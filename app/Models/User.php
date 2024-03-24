<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

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

    public function tasks(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->hasMany(Task::class)->orderBy('created_at','desc')->get();
    }

    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->hasMany(Category::class)->orderBy('name')->get();
    }

    public static function getAllCategories(){
        return Category::where('user_id','=',Auth::user()->getAuthIdentifier())
            ->orderBy('name')->get();
    }

    public static function getCategories(){
        return Category::where('user_id','=',Auth::user()->getAuthIdentifier())
            ->where("archive","=",false)
            ->orderBy('name')->get();
    }

    public static function getReminders(){
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
        $category->color= 'e10a77';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();

        $category = new Category();
        $category->name = 'Car';
        $category->icon= 'fa-car';
        $category->color= 'c79710';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();

        $category = new Category();
        $category->name = 'Other';
        $category->icon= 'fa-list';
        $category->color= 'c02ae5';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();

        $category = new Category();
        $category->name = 'Animal';
        $category->icon= 'fa-dog';
        $category->color= '3474ab';
        $category->user_id= $this->id;
        $category->month = 12;
        $category->archive = false;
        $category->save();
    }
}
