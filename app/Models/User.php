<?php

namespace App\Models;

use App\Notifications\ReminderNotif;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Pusher\PushNotifications\PushNotifications;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'token',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tasks(): Collection
    {
        return $this->hasMany(Task::class)->orderBy('created_at','desc')->get();
    }

    public function groups(): Collection
    {
        return $this->belongsToMany(Group::class, 'user_group')->get();
    }
    public function reminders(): Collection
    {
        return $this->hasMany(Task::class)
            ->where("reminder","=",true)
            ->where("reminder_date","<=",date("Y-m-d"))
            ->orderBy('created_at','desc')->get();
    }

    public function categories(): Collection
    {
        return $this->hasMany(Category::class)->orderBy('name')->get();
    }

    public function getReminderTasks(){
        $reminders = [];
        foreach (Auth::user()->unreadNotifications as $notification){
            $reminders[$notification->id] = Task::find($notification->data['task_id']);
        }

        return $reminders;
    }

    public function isPremium(){
        return $this->premium;
    }

    public function isAdmin(){
        return $this->admin;
    }

    public function sendReminders() {
        $notifications = [];
        $notificationsUnread = $this->getReminderTasks();
        //Avoid duplicate notification
        foreach ($notificationsUnread as $notification) {
            $notifications[] = $notification->id;
        }

        $notifSend = false;
        $reminders = $this->reminders();
        foreach ($reminders as $reminder){
            if (!in_array($reminder->id, $notifications)){
                Notification::send($this, new ReminderNotif($reminder));

                try {
                    if (!$notifSend) {
                        $beamsClient = new PushNotifications(array(
                            "instanceId" => env('PUSHER_BEAM_INSTANCE_ID'),
                            "secretKey" => env('PUSHER_BEAM_SECRET_KEY'),
                        ));

                        $publishResponse = $beamsClient->publishToInterests(
                            array("App.User." . $reminder->user_id),
                            array("web" => array("notification" => [
                                "title" => __($reminder->category->name) . " > " . $reminder->name,
                                "body" => __("Reminder") . ' ' . $reminder->description,
                                "deep_link" => config("app.url") . "/dashboard",
                            ])
                            ));
                        $notifSend = true;
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function getAllCategories() {
        return Category::where('user_id','=',Auth::user()->getAuthIdentifier())
            ->orderBy('name')->get();
    }

    public static function getCategories() {
        return Category::where('user_id','=',Auth::user()->getAuthIdentifier())
            ->where("archive","=",false)
            ->orderBy('name')->get();
    }

    public function getGroupIds() : array {
        $myGroupIds = [];
        foreach (self::groups() as $group){
            $myGroupIds[] = $group->id;
        }
        return $myGroupIds;
    }
    public static function getReminders() {
        return Task::where('user_id','=',Auth::user()->getAuthIdentifier())
            ->where("reminder","=","1")
            ->where("reminder_date",">=",date("Y-m-d"))
            ->orderBy('created_at','desc')->get();
    }

    public function addDefaultCategories() : void
    {
        $category = new Category();
        $category->name = 'Health';
        $category->icon= 'fa-notes-medical';
        $category->color= '#e10a77';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();

        $category = new Category();
        $category->name = 'Car';
        $category->icon= 'fa-car';
        $category->color= '#c79710';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();

        $category = new Category();
        $category->name = 'Other';
        $category->icon= 'fa-list';
        $category->color= '#c02ae5';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();

        $category = new Category();
        $category->name = 'Animal';
        $category->icon= 'fa-dog';
        $category->color= '#3474ab';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();
    }
}
