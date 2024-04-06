<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::id()){
        return redirect('dashboard');
    } else {
        $pictures = array_filter(glob(public_path('screenshots').'/*'), 'is_file');
        return view('welcome', compact('pictures'));
    }
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/categories', CategoryController::class);
    Route::resource('/tasks', TaskController::class);
    Route::resource('/users', UserController::class);
});

Route::get('/dashboard',  [UserController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('cron', function () {
    //Same code in routes/console.php scheduler
    $users = User::all();
    foreach ($users as $user){
        $user->sendReminders();
    }

    return Response()->json(["success"=>"ok"]);
});

require __DIR__.'/auth.php';
