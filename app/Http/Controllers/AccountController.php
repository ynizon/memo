<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountAmount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class AccountController extends Controller
{
    private array $fields = [
        'name' => 'required|max:255',
        'icon' => 'required|max:25',
        'position' => 'required|max:2',
        'color' => 'required|min:7|max:7',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Auth::user()->accounts();
        $total = 0;
        $firstTransaction = Transaction::where("user_id","=",Auth::id())->orderBy('created_at', 'asc')->first();;
        foreach ($accounts as $account){
            $account->amount = $account->refreshAmount($firstTransaction);
            $account->save();
            $total = $total + $account->lastAmount()->amount;
        }

        //Charts
        $charts = ['all'=>["labels"=>[], "accounts"=>[]]];
        foreach ($accounts as $account) {
            if (count($charts['all']['labels']) == 0){
                $charts['all']['labels'] = $this->getLabels($account);
            }
            $charts['all']['accounts'][$account->id] = $this->getDatasets($account);
        }

        $charts['monthly'] = $this->getTransactionsCharts(30);
        $charts['yearly'] = $this->getTransactionsCharts(365);

        return view('accounts/index', compact('accounts', 'charts', 'total'));
    }

    public function add_amount(Request $request){
        $account_id = $request->input("account_id");
        $account = Account::where("id","=",$account_id)->first();

        if (!$account && $account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $amount  = $request->input('amount');
        $created_at  = $request->input('created_at', date("d/m/Y"));

        $accountAmount = new AccountAmount();
        $accountAmount->account_id = $account->id;
        $accountAmount->amount = (float) $amount;
        $accountAmount->created_at = formatDateUK($created_at);
        $accountAmount->calculated = false;
        $accountAmount->save();

        return redirect("/accounts");
    }

    public function remove_amount(Request $request){
        $amount_id = $request->input("amount_id");
        $accountAmount = AccountAmount::where("id","=",$amount_id)->first();

        if (!$accountAmount && $accountAmount->account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $account_id = $accountAmount->account->id;
        $accountAmount->delete();

        return redirect("/accounts/".$account_id."/edit");
    }

    private function getDatasets($account): array
    {
        $amounts = $account->amountsGraph;
        $datas = [];
        foreach ($amounts as $amount){
            $datas[] = $amount->amount;
        }
        return ["label"=> $account->name, "data"=> $datas, "color"=> $this->convertColorHexa($account->color)];
    }

    private function getLabels($account): array
    {
        $amounts = $account->amountsGraph;
        $labels = [];
        foreach ($amounts as $amount) {
            $labels[] = $amount->created_at->format('m-Y');
        }
        return $labels;
    }

    private function convertColorHexa($color): string
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

    private function getTransactionsCharts(int $maxDays): array
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

    public function add_csv(Request $request){
        $nbTransactions = 0;
        if ($request->file("linxo_csv")){
            $user = Auth::user();
            $user->linxo_at = date("Y-m-d H:i:s");
            $user->save();

            $filename = $request->file("linxo_csv");
            $fileContent = file_get_contents($filename);
            $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', 'UTF-16LE');

            $accounts = [];
            foreach (Auth::user()->accounts() as $account){
                $accounts[$account->ref] = $account->id;
            }

            $colors = ["#e10a77", "#c79710", "#c02ae5", "#3474ab", "#FF6384", "#36A2EB", "#FFCD56", "#8F55DB",
                        "#DB7093", "#FF7F50", "#00BFFF", "#7FFF00", "#FFD700", "#191970", "#DC143C",
                        "#9ACD32", "#4682B4", "#F0E68C", "#8B008B", "#FF8C00", "#20B2AA", "#FFB6C1"];
            $reader = Reader::createFromString($utf8Content);
            $reader->setDelimiter("\t");
            $reader->setHeaderOffset(0);
            $records = $reader->getRecords();
            $num = 0;
            foreach ($records as $offset => $row) {
                $ref = $row["Nom de la connexion"] . "-".$row["Nom du compte"];
                if (!isset($accounts[$ref])){
                    $account = new Account();
                    $account->user_id = Auth::user()->getAuthIdentifier();
                    $account->ref = $ref;
                    $account->color = "#e10a77";
                    if (isset($colors[$num])){
                        $account->color = $colors[$num];
                    }
                    $account->icon = "fa-bank";
                    $account->name = $ref;
                    $account->rib = "-";
                    $account->position = count($accounts);
                    $account->save();
                    $accounts[$account->ref] = $account->id;
                    $num++;
                }
            }

            DB::transaction(function () use ($records, $accounts, &$nbTransactions) {
                foreach ($records as $offset => $row) {
                    $ref = md5($row["Date"]."-".$row["Libellé"]."-".$row["Montant"]."-".$row["Notes"]);
                    $transaction = Transaction::where("ref","=",$ref)->first();
                    if ($transaction) {
                        if ($transaction->category != $row["Catégorie"]){
                            $transaction->category = $row["Catégorie"];
                            $transaction->save();
                            $nbTransactions++;
                        }
                    }else {
                        $transaction = new Transaction();
                        $transaction->account_id = $accounts[$row["Nom de la connexion"] . "-".$row["Nom du compte"]];
                        $transaction->user_id = Auth::user()->getAuthIdentifier();
                        $transaction->ref = $ref;
                        $transaction->name = $row["Libellé"];
                        $transaction->amount = $row["Montant"];
                        $transaction->check_number = $row["N° de chèque"];
                        $transaction->note = $row["Notes"];
                        $transaction->category = $row["Catégorie"];
                        $transaction->created_at = Carbon::createFromFormat('d/m/Y', $row["Date"]);
                        $transaction->save();
                        $nbTransactions++;
                    }
                }
            });

        }

        return redirect("/accounts")->with('success', $nbTransactions . __(" new transactions"));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Account::create($this->validateFields($request));

        return redirect()->route('accounts.index')
            ->with('success',__('Account created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        if ($account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        return view('accounts.edit', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        if ($account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $icons = $this->getAwesomeIcons();
        $icons['fa-dog'] = '';
        $icons['fa-notes-medical'] = '';
        ksort($icons);

        $charts = [];
        $charts['accounts'][$account->id] = $this->getDatasets($account);
        $charts['labels'] = $this->getLabels($account);

        return view('accounts/edit', compact('charts','account', 'icons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        if ($account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }

        $account->update($this->validateFields($request));
        return redirect()->route('accounts.index')
            ->with('success', __('Account updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        if ($account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        $account->delete();
        return redirect()->route('accounts.index')
            ->with('success', __('Account deleted successfully'));
    }

    private function getAwesomeIcons(): array
    {
        $icons = new \Awps\FontAwesome();
        $icons = $icons->getArray();
        $icons["fa-bank"] = "fa-bank";
        ksort($icons);
        return $icons;
    }

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['active'] = $request->input('active') ? 1 : 0;

        return $validated;
    }
}
