<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
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
        return view('transactions/index', compact('accounts'));
    }

}
