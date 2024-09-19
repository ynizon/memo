<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\Request;
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
        $groupId = (int) $request->input("group_id");

        if ($groupId != 0) {
            $tasks = [];

            foreach (Auth::user()->groups()->get() as $group) {
                foreach ($group->tasks()->get() as $task) {
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

        $taskDates = [];
        $taskDatasets = [];
        foreach ($tasks as $task){
            $date = substr($task->created_at,0,7);
            if (!in_array($date, $taskDates) && $task->price > 0) {
                $taskDates[] = $date;
            }
        }

        sort($taskDates);

        $taskCategories = [];
        foreach ($tasks as $task) {
            if ($task->price > 0){
                if (!isset($taskCategories[$task->category_id])){
                    $taskCategories[$task->category_id] = [];
                    foreach ($taskDates as $date){
                        $taskCategories[$task->category_id][$date] = 0;
                    }
                }
                $date = substr($task->created_at,0,7);
                $taskCategories[$task->category_id][$date] = $taskCategories[$task->category_id][$date] + $task->price;
            }
        }

        //Reorder dataset
        $taskCategoriesTmp = $taskCategories;
        $taskCategories = [];
        foreach ($categories as $category) {
            foreach ($taskCategoriesTmp as $taskCategoryId => $taskCategory) {
                if ($taskCategoryId == $category->id) {
                    $taskCategories[$taskCategoryId] = $taskCategory;
                }
            }
        }

        foreach ($taskCategories as $categoryIdTmp => $dates){
            $datas = [];
            foreach ($dates as $date => $total){
                $datas[] = $total;
            }
            $category = Category::find($categoryIdTmp);
            $taskDatasets[] = [
                "label" => __($category->name),
                "tension" => "0.4",
                "borderWidth"=> "0",
                "borderSkipped" => "false",
                "backgroundColor" => $category->color,
                "data" => $datas,
                "maxBarThickness" => "6"
            ];
        }

        $charts = [
            "labels" => $taskDates,
            "datasets" => $taskDatasets
        ];

        return view('tasks/index', compact('tasks','categories', "categoryId", "charts",
            "groupId"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $task = new Task();
        $categories = Auth::user()->categories();
        $groups = Auth::user()->groups()->get();
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

        $groups = [];
        $groupsTmp = $request->input("groups");
        $userGroupIds = Auth::user()->getGroupIds();

        if ($groupsTmp != null) {
            foreach ($groupsTmp as $groupId) {
                if (in_array($groupId, $userGroupIds)) {
                    $groups[] = $groupId;
                }
            }
            $task->groups()->sync($groups);
        }

        return redirect()->route('tasks.index')
            ->with('success',__('Task created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $categories = Category::getCategories();
        return view('tasks/edit', compact('task','categories'));
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
        $groups = Auth::user()->groups()->get();
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

        $groups = [];
        $groupsTmp = $request->input("groups");
        $userGroupIds = Auth::user()->getGroupIds();

        if ($groupsTmp != null) {
            foreach ($groupsTmp as $groupId) {
                if (in_array($groupId, $userGroupIds)) {
                    $groups[] = $groupId;
                }
            }
            $task->groups()->sync($groups);
        }

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
