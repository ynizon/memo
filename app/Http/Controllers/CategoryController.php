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
        'color' => 'required|min:7|max:7',
        'month' => 'required',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Auth::user()->getAllCategories();
        return view('categories/index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = new Category();
        $category->icon = 'fa-list';
        $icons = $this->getAwesomeIcons();
        return view('categories/edit', compact('category', 'icons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Category::create($this->validateFields($request));

        return redirect()->route('categories.index')
            ->with('success',__('Category created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        if ($category->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        return view('categories.edit', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        if ($category->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $icons = $this->getAwesomeIcons();
        $icons['fa-dog'] = '';
        $icons['fa-notes-medical'] = '';
        ksort($icons);
        return view('categories/edit', compact('category', 'icons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        if ($category->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }

        $category->update($this->validateFields($request));
        return redirect()->route('categories.index')
            ->with('success', __('Category updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }
        $category->delete();
        return redirect()->route('categories.index')
            ->with('success', __('Category deleted successfully'));
    }

    private function getAwesomeIcons(): array
    {
        $icons = new \Awps\FontAwesome();
        $icons = $icons->getArray();
        ksort($icons);
        return $icons;
    }

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['archive'] = $request->input('archive') ? 1 : 0;

        return $validated;
    }
}
