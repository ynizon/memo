<?php

namespace App\Managers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class AccountManager
{
    public static function getAllCharts($accounts): array
    {
        $charts = ['all'=>["labels"=>[], "accounts"=>[]]];
        foreach ($accounts as $account) {
            if (count($charts['all']['labels']) == 0){
                $charts['all']['labels'] = self::getLabels($account);
            }
            $charts['all']['accounts'][$account->id] = self::getDatasets($account);
        }

        $charts['monthly'] = self::getTransactionsCharts(30);
        $charts['yearly'] = self::getTransactionsCharts(365);
        return $charts;
    }

    public static function getDatasets($account): array
    {
        $amounts = $account->amountsGraph;
        $datas = [];
        foreach ($amounts as $amount){
            $datas[] = $amount->amount;
        }
        $datas[] = $account->amount;
        return [
            "hidden" => $account->active ? "false" : "true",
            "label"=> $account->name,
            "data"=> $datas,
            "color"=> self::convertColorHexa($account->color)
        ];
    }

    public static function getLabels($account): array
    {
        $amounts = $account->amountsGraph;
        $labels = [];
        foreach ($amounts as $amount) {
            $labels[] = $amount->created_at->format('m-Y');
        }
        $labels[] = __("Now");
        return $labels;
    }

    private static function convertColorHexa($color): string
    {
        // Remove the '#' if it's present
        $hex = ltrim($color, '#');

        // Make sure it's a valid 6-character hex code
        if (strlen($hex) !== 6) {
            // You might want to handle this error more gracefully
            return 'Invalid hex color';
        }

        // Convert hex to decimal
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgb($r, $g, $b, 0.5)";
    }

    private static function getTransactionsCharts(int $maxDays): array
    {
        $transactions = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('accounts.active', 1)
            ->where("transactions.user_id","=",Auth::user()->getAuthIdentifier())
            ->where("transactions.created_at",">=",Carbon::now()->subDays($maxDays)->toDateTimeString())
            ->selectRaw("SUM(transactions.amount) as sum_amount, category")
            ->groupBy("category")
            ->get();

        $sum = 0;
        foreach ($transactions as $transaction) {
            $sum = $sum + $transaction->sum_amount;
        }
        if ($sum == 0){
            $sum = 1;
        }

        $labels = [];
        $datasets = [];
        foreach ($transactions as $transaction) {
            $percent = round($transaction->sum_amount * 100 / $sum,  0, PHP_ROUND_HALF_UP);
            if ($percent >= 1) {
                $labels[] = $transaction->category . " (".$transaction->sum_amount." €)";
                $datasets[] = $percent;
            }
        }
        return ['datasets' => $datasets, 'labels' => $labels];
    }

    /**
     * @throws \League\Csv\InvalidArgument
     * @throws \League\Csv\Exception
     */
    public static function importCsvFile(string $filename): void
    {
        $user = Auth::user();
        $user->linxo_at = date("Y-m-d H:i:s");
        $user->save();

        $fileContent = file_get_contents($filename);
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', 'UTF-16LE');

        $accounts = [];
        foreach (Auth::user()->accounts() as $account){
            $accounts[$account->ref] = $account;
        }

        $colors = getHexaColors();
        $reader = Reader::createFromString($utf8Content);
        $reader->setDelimiter("\t");
        $reader->setHeaderOffset(0);
        $validFile = true;
        try {
            $headerRow = $reader->getHeader();
            $fields = ["Nom de la connexion", "Nom du compte", "Libellé","Date",
                "Montant", "N° de chèque", "Notes", "Catégorie"];
            $missingFields = [];
            foreach ($fields as $expectedField) {
                if (!in_array($expectedField, $headerRow)) {
                    $missingFields[] = $expectedField;
                }
            }
            if (!empty($missingFields)) {
                $validFile = false;
            }
        }catch(\Exception $e){
            throw($e);
        }

        if (!$validFile) {
            throw new \Exception();
        } else {
            $records = $reader->getRecords();
            $num = 0;
            foreach ($records as $offset => $row) {
                $ref = $row["Nom de la connexion"] . "-" . $row["Nom du compte"];
                if (!isset($accounts[$ref])) {
                    $account = new Account();
                    $account->user_id = Auth::user()->getAuthIdentifier();
                    $account->ref = $ref;
                    $account->color = "#e10a77";
                    if (isset($colors[$num])) {
                        $account->color = $colors[$num];
                    }
                    $account->icon = "fa-bank";
                    $account->name = $ref;
                    $account->rib = "-";
                    $account->position = count($accounts);
                    $account->needrefresh = true;
                    $account->save();
                    $accounts[$account->ref] = $account;
                    $num++;
                }
            }

            DB::transaction(function () use ($records, $accounts, &$nbTransactions) {
                foreach ($records as $offset => $row) {
                    $ref = md5($row["Date"] . "-" . $row["Libellé"] . "-" . $row["Montant"]
                        . "-" . $row["Notes"]. "-" . $row["Notes"]);
                    $transaction = Transaction::where("ref", "=", $ref)->first();
                    if ($transaction) {
                        if ($transaction->category != $row["Catégorie"]) {
                            $transaction->category = $row["Catégorie"];
                            $transaction->save();
                            $nbTransactions++;
                        }
                    } else {
                        $transaction = new Transaction();
                        $transaction->account_id = $accounts[$row["Nom de la connexion"] . "-" . $row["Nom du compte"]]->id;
                        $transaction->user_id = Auth::user()->getAuthIdentifier();
                        $transaction->ref = $ref;
                        $transaction->name = $row["Libellé"];
                        $transaction->amount = str_replace(",",".",$row["Montant"]);
                        $transaction->check_number = $row["N° de chèque"];
                        $transaction->note = $row["Notes"];
                        $transaction->category = $row["Catégorie"];
                        $transaction->created_at = Carbon::createFromFormat('d/m/Y', $row["Date"]);
                        $transaction->save();
                        $nbTransactions++;

                        $account = $accounts[$row["Nom de la connexion"] . "-" . $row["Nom du compte"]];
                        $account->needrefresh = true;
                        $account->save();
                    }
                }
            });

            //@TODO
//                            DB::table('account_amounts')->insert([
//                                'amount' => 1328.6,
//                                'account_id' => 1,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 668,
//                                'account_id' => 2,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 67.07,
//                                'account_id' => 3,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 2061.4,
//                                'account_id' => 4,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 1922.96,
//                                'account_id' => 5,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 11853.31,
//                                'account_id' => 6,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 2477.69,
//                                'account_id' => 7,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 25.84,
//                                'account_id' => 8,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 388.38,
//                                'account_id' => 9,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);
//
//                            DB::table('account_amounts')->insert([
//                                'amount' => 78.85,
//                                'account_id' => 10,
//                                'calculated'=>0,
//                                'created_at' => '2025-09-25 00:00:00',
//                            ]);

//                DB::table('accounts')
//                                ->whereIn('account_id', [4, 7,8,10])
//                                ->update(['active' => 0]);
        }
    }
}
