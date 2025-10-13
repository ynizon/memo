<?php

namespace App\Http\Controllers;

use App\Managers\LoanManager;
use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    private array $fields = [
        'account_id' =>'required|max:255',
        'name' => 'required|max:255',
        'amount' => 'required|max:255',
        'rate' => 'required|max:255',
        'ref' => 'required|max:255',
        'from' => 'required|max:10',
        'to' => 'required|max:10',
        'icon' => 'required|max:25',
        'color' => 'required|min:7|max:7',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loans = Auth::user()->loans();
        $totalPaid = 0;
        $total = 0;
        foreach ($loans as $loan) {
            $totalPaid += ($loan->amount - $loan->amount_now);
            $total += $loan->amount;
        }

        $charts = LoanManager::getAllCharts($loans);
        return view('loans/index', compact('loans', 'charts', 'totalPaid', 'total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $loan = new Loan();
        $loan->color = "#3474ab";
        $loan->active = 1;
        $loan->icon = 'fa-map-signs';
        $loan->account_id = $request->input("acid");
        $icons = getAwesomeIcons();
        $charts = [];
        $charts['loans'][$loan->id] = LoanManager::getDatasets($loan);
        $charts['labels'] = LoanManager::getLabels($loan);
        $interval = [];
        $interval['from'] = date("Y-m-d", strtotime("-1 year"));
        $interval['to'] = date("Y-m-d", strtotime("+1 day"));

        return view('loans/edit', compact('loan', 'charts', 'icons', 'interval'));
    }

    public function add_loan(Request $request){
        $account_id = $request->input("account_id");
        $account = Account::where("id","=",$account_id)->first();

        if (!$account && $account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $account->needrefresh = true;
        $account->save();

        $from  = $request->input('from', date("d/m/Y"));
        $to  = $request->input('to', date("d/m/Y"));

        $loanAmount = new LoanAmount();
        $loanAmount->account_id = $account->id;
        $loanAmount->ref = $request->input('ref');
        $loanAmount->color = $request->input('color');
        $loanAmount->icon = $request->input('icon');
        $loanAmount->amount = (float) $request->input('amount');
        $loanAmount->amount_now = $loanAmount->amount ;
        $loanAmount->rate = (float) $request->input('rate');
        $loanAmount->from = formatDateUK($from);
        $loanAmount->to = formatDateUK($to);
        $loanAmount->save();

        return redirect("/loans");
    }

    public function remove_loan(Request $request){
        $amount_id = $request->input("amount_id");
        $loanAmount = LoanAmount::where("id","=",$amount_id)->first();

        if (!$loanAmount && $loanAmount->account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $account_id = $loanAmount->account->id;
        $loanAmount->delete();

        return redirect("/accounts/".$account_id."/edit");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $loan = Loan::create($this->validateFields($request));
        $loan->account->needRefresh = true;
        $loan->account->save();
        return redirect()->route('loans.index')
            ->with('success',__('Loan created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        if ($loan->account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        return view('loans.edit', compact('loan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan, Request $request)
    {
        if ($loan->account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $icons = getAwesomeIcons();
        ksort($icons);

        $charts = [];
        $charts['loans'][$loan->id] = LoanManager::getDatasets($loan);
        $charts['labels'] = LoanManager::getLabels($loan);

        $interval = [];
        $interval['from'] = $request->input("from") != '' ? $request->input("from") :
            date("Y-m-d", strtotime("-1 year"));
        $interval['to'] = $request->input("to") != '' ? $request->input("to") :
            date("Y-m-d", strtotime("+1 day"));;

        return view('loans/edit', compact('charts','loan', 'icons', 'interval'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        if ($loan->account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }

        $loan->update($this->validateFields($request));
        $loan->account->needRefresh = true;
        $loan->account->save();
        return redirect()->route('loans.index')
            ->with('success', __('Loan updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        if ($loan->account->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        $loan->delete();
        return redirect()->route('loans.index')
            ->with('success', __('Loan deleted successfully'));
    }

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['active'] = $request->input('active') ? 1 : 0;

        return $validated;
    }
}
