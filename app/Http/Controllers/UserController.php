<?php

namespace App\Http\Controllers;

use App\Managers\UserManager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->admin || Auth::user()->email == env("ADMIN_EMAIL"))
        {
            $users = User::all();
            return view('users/index', compact('users'));
        } else{
            abort(403, __('Unauthorized action.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::user()->admin || Auth::user()->email == env("ADMIN_EMAIL")) {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', __('User deleted successfully'));
        } else {
            abort(403, __('Unauthorized action.'));
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        $tasks = Auth::user()->tasks();;
        $categories = Auth::user()->categories();
        $limitDate = date("Y-m-d", strtotime("-12 month"));
        $previousDate = date("Y-m-d", strtotime("-24 month"));
        $groups = UserManager::getGroups(Auth::user()->groups(), $limitDate, $previousDate);
        $expenses = UserManager::getExpenses($tasks, $limitDate, $previousDate, $groups);
        $categoryId = 0;
        $groupId = 0;

        return view('dashboard', compact('tasks','categories', 'expenses', 'categoryId',
            'groups', 'groupId'));
    }

    public function togglePremium(int $userId){
        if (Auth::user()->email != env("ADMIN_EMAIL") && Auth::user()->isAdmin()){
            abort(403, 'Unauthorized action.');
        }
        $user = User::find($userId);
        $user->premium = !$user->premium;
        $user->save();

        return redirect()->route('users.index');
    }

    public function toggleAdmin(int $userId){
        if (Auth::user()->email != env("ADMIN_EMAIL") && Auth::user()->isAdmin()){
            abort(403, 'Unauthorized action.');
        }
        $user = User::find($userId);
        $user->admin = !$user->admin;
        $user->save();

        return redirect()->route('users.index');
    }

    public function setBankDate($field, Request $request){
        $value = $request->input("value");
        $field == "start" ? $field = "bank_start_at" : $field = 'bank_end_at';
        $user = Auth::user();
        $user->$field = $value;
        $user->save();

        return response()->json([
            'status' => 'success',
            'field_updated' => $field
        ], 200);
    }
}
