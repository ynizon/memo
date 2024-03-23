<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    private array $fields = [
        'name' => 'required|max:255',
        'icon' => 'required|max:25',
        'color' => 'required|min:6|max:6',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return view('categories/index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = new Category();
        return view('categories/edit', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['forall'] = 0;
        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success','Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('categories/edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        if ($category->forall == 0){
            $category->update($request->all());
            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully.');
        } else {
            return redirect()->route('categories.index')
                ->with('error', 'Category not updated successfully.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->user_id == Auth::user()->getAuthIdentifier() && $category->forall == 0){
            $category->delete();
            return redirect()->route('categories.index')
                ->with('success', 'Category deleted successfully');
        } else {
            $category->delete();
            return redirect()->route('categories.index')
                ->with('error', 'Category not deleted successfully');
        }
    }
}
