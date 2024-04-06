<?php

use App\Models\User;
use Illuminate\Support\Facades\Schedule;


//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $users = User::all();
    foreach ($users as $user){
        $user->sendReminders();
    }
})->daily();

Schedule::command('queue:work --stop-when-empty')
        ->daily()
        ->withoutOverlapping();
