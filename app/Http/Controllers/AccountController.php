<?php

namespace App\Http\Controllers;

use App\Models\Account;
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
        'color' => 'required|min:7|max:7',
        'month' => 'required',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Auth::user()->accounts();
        return view('accounts/index', compact('accounts'));
    }

    public function add_csv(Request $request){
        $nbTransactions = 0;
        if ($request->file("linxo_csv")){
            $filename = $request->file("linxo_csv");
            $fileContent = file_get_contents($filename);
            $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', 'UTF-16LE');

            $accounts = [];
            foreach (Auth::user()->accounts() as $account){
                $accounts[$account->ref] = $account->id;
            }

            $colors = ["#e10a77", "#c79710", "#c02ae5", "#3474ab"];
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
                    $account->save();
                    $accounts[$account->ref] = $account->id;
                    $num++;
                }
            }

            DB::transaction(function () use ($records, $accounts, &$nbTransactions) {
                foreach ($records as $offset => $row) {
                    $ref = md5($row["Date"]."-".$row["Libellé"]."-".$row["Montant"]."-".$row["Notes"]);
                    $transaction = Transaction::where("ref","=",$ref)->first();
                    if (!$transaction){
                        $transaction = new Transaction();
                        $transaction->account_id = $accounts[$row["Nom de la connexion"] . "-".$row["Nom du compte"]];
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
        return view('accounts/edit', compact('account', 'icons'));
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
            ->with('success', __('account updated successfully.'));
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
