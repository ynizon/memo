<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'user_id',
    ];

    public $timestamps = false;

    public function users(): Collection
    {
        return $this->usersRelation()->get();
    }

    public function usersRelation(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group');
    }

    public function tasks(): Collection
    {
        return $this->tasksRelation()->get();
    }

    public function tasksRelation(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_group');
    }
}
