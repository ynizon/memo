<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
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
        $tasks = Task::where("user_id","=",Auth::user()->getAuthIdentifier())->orderBy('created_at','desc')->get();
        $categories = Category::getCategories();
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
