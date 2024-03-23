<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'name',
        'icon',
        'color',
        'user_id',
        'forall'
    ];

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    public static function getCategories(){
        return Category::where('forall',"=",1)->orWhere('user_id','=',Auth::user()->getAuthIdentifier())
            ->orderBy('name')->get();
    }
}
