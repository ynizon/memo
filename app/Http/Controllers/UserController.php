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
                $transactions[$task->category_id] = ['price' => 0, 'category' =>$task->category, 'latest'=>$task->created_at];
            }
            $transactions[$task->category_id]['price'] = $transactions[$task->category_id]['price'] + $task->price;
        }
        return view('dashboard', compact('tasks','categories', 'transactions'));
    }
}
