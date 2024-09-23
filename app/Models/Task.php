<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'information',
        'price',
        'category_id',
        'user_id',
        'created_at',
        'reminder',
        'reminder_date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function groups(): Collection
    {
        return $this->groupsRelation()->get();
    }

    public function groupsRelation(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'task_group');
    }

    public function isInGroup(int $groupId) : bool {
        foreach (self::groups() as $group) {
            if ($group->id === $groupId) {
                return true;
            }
        }
        return false;
    }

    public function getGroupIds() : array {
        $groupIds = [];
        foreach (self::groups() as $group) {
            $groupIds[] = $group->id;
        }
        return $groupIds;
    }
}
