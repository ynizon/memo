<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $taskId = (int) $request->input('task_id');
        $task = Task::find($taskId);
        if ($task->user_id != Auth::user()->getAuthIdentifier() || !Auth::user()->isPremium())
        {
            abort(403, __('Unauthorized action.'));
        }

        $file = $request->file('file');
        if ($file != null) {
            $attachment = new Attachment();
            $attachment->store($taskId, $request->file('file'));
        }

        return redirect()->route('tasks.edit', [$taskId])
            ->with('success',__('Attachment created successfully.'));
    }

    /**
     * Download the specified resource.
     */
    public function show(Attachment $attachment)
    {
        if ($attachment->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $file= Storage::path('attachments/'.$attachment->user_id.'/'.$attachment->task_id.'/'.$attachment->name);

        return response()->download($file, $attachment->name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment)
    {
        if ($attachment->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        Storage::delete('attachments/'.$attachment->user_id.'/'.$attachment->task_id.'/'.$attachment->name);
        $files = Storage::allFiles('attachments/'.$attachment->user_id.'/'.$attachment->task_id.'/');
        if(count($files) == 0){
            Storage::deleteDirectory('attachments/'.$attachment->user_id.'/'.$attachment->task_id);
        }

        $files = Storage::allFiles('attachments/'.$attachment->user_id.'/');
        if(count($files) == 0){
            Storage::deleteDirectory('attachments/'.$attachment->user_id);
        }
        $attachment->delete();
        return redirect()->route('tasks.edit', [$attachment->task_id])
            ->with('success', __('Attachment deleted successfully'));
    }
}
