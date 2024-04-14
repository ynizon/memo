<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'task_id',
        'user_id',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(int $taskId, UploadedFile $file): void
    {
        $this->user_id = Auth::user()->id;
        $this->task_id = $taskId;
        $this->name = $file->getClientOriginalName();
        Storage::disk('local')->put('attachments/'.Auth::user()->id.'/'.$taskId.
            '/'.$file->getClientOriginalName(), file_get_contents($file));
        $this->save();
    }
}
