<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('laravel-examples.users-management', compact('users'));
    }

    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        $tasks = Auth::user()->tasks();;
        $categories = Auth::user()->categories();
        $transactions = [];
        foreach ($tasks as $task){
            if (!isset($transactions[$task->category_id])){
                $transactions[$task->category_id] = ["last" =>0, "now" =>0, "total"=>0,
                    'category' =>$task->category, 'latest'=>$task->created_at];
            }

            $limitDate = date("Y-m-d", strtotime("-12 month"));
            $previousDate = date("Y-m-d", strtotime("-24 month"));

            if ($task->created_at >= $limitDate) {
                $transactions[$task->category_id]['now'] = $transactions[$task->category_id]['now'] + $task->price;
            }
            if ($task->created_at >= $previousDate && $task->created_at < $limitDate) {
                $transactions[$task->category_id]['last'] = $transactions[$task->category_id]['last'] + $task->price;
            }
            $transactions[$task->category_id]['total'] = $transactions[$task->category_id]['total'] + $task->price;
        }
        $categoryId = 0;
        return view('dashboard', compact('tasks','categories', 'transactions', 'categoryId'));
    }
}
