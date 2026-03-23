<?php

namespace App\Http\Controllers;

use App\Managers\AccountManager;
use App\Managers\LoanManager;
use App\Models\Account;
use App\Models\AccountAmount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        /** @var Account $account */
        $accounts = Auth::user()->accounts();
        $loans = Auth::user()->loans();
        $total = 0;
        $firstTransaction = Transaction::where("user_id","=",Auth::id())->orderBy('created_at', 'asc')->first();;
        foreach ($accounts as $account){
            if ($account->needrefresh){
                $account->amount = $account->refreshAmount($firstTransaction);
                $account->needrefresh = false;
                $account->save();
            }
            if ($account->active){
                $total = $total + $account->lastAmount()->amount;
            }
        }

        //Add missing months amounts (if no transactions during some months)
        $lastDate = null;
        foreach ($accounts as $account){
            if ($lastDate == null or $account->updated_at >= $lastDate){
                $lastDate = $account->updated_at;
            }
        }

        if ($lastDate != null){
            foreach ($accounts as $account){
                $lastAmounts = AccountAmount::where("account_id","=",$account->id)
                    ->where("calculated","=",false)
                    ->orderBy("created_at","desc")->get();

                if (substr($account->updated_at,0,10) != date("Y-m-d")){
                    $account->refreshAmountMonths($firstTransaction, $lastAmounts, $lastDate);
                    $account->updated_at = date("Y-m-d");
                    $account->save();
                }
            }
        }

        $totalPaid = 0;
        $totalToPaid = 0;
        foreach ($loans as $loan) {
            $totalPaid += ($loan->amount - $loan->amount_now);
            $totalToPaid += $loan->amount;
        }

        $charts = AccountManager::getAllCharts($accounts);
        $loanCharts = LoanManager::getAllCharts($loans);

        //Remove data for UI
        $labelsToRemove = [];
        foreach ($charts["all"]["labels"] as $labelNum => $label)
        {
            $labelToCheck = substr($label, -4) . "-" . substr($label, 0,2).'-01';
            if (Auth::user()->bank_start_at != null && $labelToCheck < Auth::user()->bank_start_at)
            {
                $labelsToRemove[] = $labelNum;
            } else {
                if (Auth::user()->bank_end_at != null && $labelToCheck > Auth::user()->bank_end_at) {
                    $labelsToRemove[] = $labelNum;
                }
            }
        }

        foreach ($labelsToRemove as $labelToRemove)
        {
            unset($charts["all"]["labels"][$labelToRemove]);
            foreach ($charts["all"]["accounts"] as $accountId => $account)
            {
                unset($charts["all"]["accounts"][$accountId]["data"][$labelToRemove]);
            }
        }
        $charts["all"]["labels"] = array_values($charts["all"]["labels"]);
        foreach ($charts["all"]["accounts"] as $accountId => $account)
        {
            $charts["all"]["accounts"][$accountId]["data"] = array_values($charts["all"]["accounts"][$accountId]["data"]);
        }

        return view('accounts/index', compact('accounts', 'charts','loanCharts',
                  'total', 'totalPaid', 'totalToPaid'));
    }

    public function add_amount(Request $request){
        $account_id = $request->input("account_id");
        $account = Account::where("id","=",$account_id)->first();

        if (!$account && $account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $account->needrefresh = true;
        $account->save();

        $amount  = $request->input('amount');
        $created_at  = $request->input('created_at', date("d/m/Y"));

        $accountAmount = new AccountAmount();
        $accountAmount->account_id = $account->id;
        $accountAmount->amount = (float) $amount;
        $accountAmount->created_at = formatDateUK($created_at). ' ' .date("H:i:s");
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

    public function add_csv(Request $request){
        $nbTransactions = 0;
        if ($request->file("linxo_csv")){
            try {
                $nbTransactions = AccountManager::importCsvFile($request->file("linxo_csv"));
            }catch(\Exception $e){
                return redirect("/accounts")->with('error', __("File error"));
            }
        }

        return redirect("/accounts")->with('success', $nbTransactions . " " .__("new transactions"));
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
    public function edit(Account $account, Request $request)
    {
        if ($account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $icons = getAwesomeIcons();
        ksort($icons);

        $charts = [];
        $charts['accounts'][$account->id] = AccountManager::getDatasets($account);
        $charts['labels'] = AccountManager::getLabels($account);

        $interval = [];
        $interval['from'] = $request->input("from") != '' ? $request->input("from") :
            date("Y-m-d", strtotime("-1 year"));
        $interval['to'] = $request->input("to") != '' ? $request->input("to") :
            date("Y-m-d", strtotime("+1 day"));

        return view('accounts/edit', compact('charts','account', 'icons', 'interval'));
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

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['active'] = $request->input('active') ? 1 : 0;

        return $validated;
    }
}
