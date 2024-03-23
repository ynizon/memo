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
        $tasks = Task::where("user_id","=",Auth::user()->getAuthIdentifier())->orderBy('created_at','desc')->get();
        $categories = Category::getCategories();
        return view('tasks/index', compact('tasks','categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $task = new Task();
        $categories = Category::getCategories();
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
        $categories = Category::getCategories();
        return view('tasks/edit', compact('task','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->user_id == Auth::user()->getAuthIdentifier()){
            $task->update($this->validateFields($request));
            return redirect()->route('tasks.index')
                ->with('success', 'Task updated successfully.');
        } else {
            return redirect()->route('tasks.index')
                ->with('error', 'Task not updated successfully.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->user_id == Auth::user()->getAuthIdentifier()){
            $task->delete();
            return redirect()->route('tasks.index')
                ->with('success', 'Task deleted successfully');
        } else {
            $task->delete();
            return redirect()->route('tasks.index')
                ->with('error', 'Task not deleted successfully');
        }
    }

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['price'] = (float) $request->input('price');

        return $validated;
    }
}
