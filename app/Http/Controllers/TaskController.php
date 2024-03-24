<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $tasks = Auth::user()->tasks();
        $categories = Auth::user()->categories();
        return view('tasks/index', compact('tasks','categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $task = new Task();
        $task->month = 12;
        $categories = Auth::user()->categories();
        return view('tasks/edit', compact('task', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Task::create($this->validateFields($request));

        return redirect()->route('tasks.index')
            ->with('success','Task created successfully.');
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
    public function edit(Task $task)
    {
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        $categories = Auth::user()->categories();
        return view('tasks/edit', compact('task','categories'));
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
        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        $task->delete();
        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully');
    }

    public function archive(Task $task)
    {
        if ($task->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        $task->archive = !$task->archive;
        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully');
    }

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['price'] = (float) $request->input('price');
        $validated['reminder_date'] = formatDateUK($request->input('reminder_date'));
        $validated['created_at'] = $request->input('created_at');
        $validated['reminder'] = $request->input('reminder') ? 1 : 0;

        return $validated;
    }
}
