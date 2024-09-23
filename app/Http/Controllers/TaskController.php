<?php

namespace App\Http\Controllers;

use App\Managers\TaskManager;
use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    private array $fields = [
        'name' => 'required|max:255',
        'category_id' => 'required',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Auth::user()->categories();
        $categoryId = (int) $request->input("category_id");

        $groups = Auth::user()->groups();
        $groupId = (int) $request->input("group_id");
        if ($groupId != 0) {
            $tasks = [];

            foreach ($groups as $group) {
                foreach ($group->tasks() as $task) {
                    if ($groupId == $group->id) {
                        $tasks[] = $task;
                    }
                }
            }
            usort($tasks, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        } else {
            $tasks = Auth::user()->tasks();
        }

        $charts = TaskManager::getChart($tasks, $categories);

        return view('tasks/index', compact('tasks','categories', "categoryId", "charts",
            "groups", "groupId"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $task = new Task();
        $categories = Auth::user()->categories();
        $groups = Auth::user()->groups();
        return view('tasks/edit', compact('task', 'categories', 'groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $task = Task::create($this->validateFields($request));

        $file = $request->file('file');
        if ($file != null && Auth::user()->isPremium()){
            $attachment = new Attachment();
            $attachment->store($task->id, $file);
        }

        TaskManager::syncGroup(Auth::user(), $request->input("groups"), $task, true);
        return redirect()->route('tasks.index')
            ->with('success',__('Task created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task, Request $request)
    {
        $authorize = false;
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            foreach ($task->groups() as $group) {
                foreach ($group->users() as $user) {
                    if ($user->id == Auth::user()->getAuthIdentifier()){
                        $authorize = true;
                    }
                }
            }
        } else {
            $authorize = true;
        }

        if (!$authorize) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->input("notif") != '') {
            $notif = Auth::user()->notifications()->where('id', $request->input("notif"))->first();
            $notif->read_at = now();
            $notif->save();
        }

        $categories = Auth::user()->categories();
        $groups = Auth::user()->groups();
        return view('tasks/show', compact('task','categories', 'groups'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task, Request $request)
    {
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        if ($request->input("notif") != '') {
            $notif = Auth::user()->notifications()->where('id', $request->input("notif"))->first();
            $notif->read_at = now();
            $notif->save();
        }
        $categories = Auth::user()->categories();
        $groups = Auth::user()->groups();
        return view('tasks/edit', compact('task','categories', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }

        $task->update($this->validateFields($request));
        TaskManager::syncGroup(Auth::user(), $request->input("groups"), $task, false);

        return redirect()->route('tasks.index')
            ->with('success', __('Task updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }

        $notifs = DatabaseNotification::where('data->task_id', $task->id)->get();
        foreach ($notifs as $notif) {
            $notif->delete();
        }
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', __('Task deleted successfully'));
    }

    public function archive(Task $task)
    {
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        $task->archive = !$task->archive;
        return redirect()->route('tasks.index')
            ->with('success', __('Task updated successfully'));
    }

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['price'] = (float) $request->input('price');
        $validated['reminder_date'] = formatDateUK($request->input('reminder_date'));
        $validated['created_at'] = $request->input('created_at');
        $validated['information'] = $request->input('information') ? $request->input('information') : '';
        $validated['reminder'] = $request->input('reminder') ? 1 : 0;

        return $validated;
    }
}
