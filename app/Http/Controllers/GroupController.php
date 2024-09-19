<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    private array $fields = [
        'name' => 'required|max:255',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $groups = [];
        $groupIds = [];
        foreach (Auth::user()->groups()->get() as $group){
            $groupIds[] = $group->id;
            $groups[] = $group;
        }
        foreach (Group::where("user_id", "=", Auth::id())->get() as $group){
            if (!in_array($group->id, $groupIds)){
                $groups[] = $group;
            }
        }

        return view('groups/index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $group = new Group();
        $group->name = __("Family");

        return view('groups/edit', compact('group'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $group = Group::create($this->validateFields($request));
        $group->users()->attach(Auth::id());

        return redirect()->route('groups.index')
            ->with('success',__('Group created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        return view('groups/edit', compact('group'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group, Request $request)
    {
        if ($group->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }

        return view('groups/edit', compact('group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        if ($group->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, 'Unauthorized action.');
        }

        $group->update($this->validateFields($request));
        return redirect()->route('groups.index')
            ->with('success', __('Group updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        if ($group->user_id != Auth::user()->getAuthIdentifier()){
            abort(403, __('Unauthorized action.'));
        }
        $group->delete();
        return redirect()->route('groups.index')
            ->with('success', __('Group deleted successfully'));
    }

    /**
     * Add to this group
     */
    public function addto(Request $request, int $groupId)
    {
        $email = $request->input('email');
        $user = User::where("email","=", $email)->first();

        if (!$user) {
            return redirect()->route('groups.index')
                ->with('error', __('User can not be found'));
        }
        $groupIds = [];
        foreach ($user->groups()->get() as $group){
            $groupIds[] = $group->id;
        }

        if (!in_array($groupId, $groupIds)){
            $group = Group::where("id","=",$groupId)->first();
            $group->users()->attach($user->id);
        }

        return redirect()->route('groups.index')
            ->with('success', __('Group successfully joined'));
    }

    private function validateFields(Request $request) : array
    {
        $validated = $request->validate($this->fields);
        $validated['user_id'] = Auth::user()->getAuthIdentifier();
        $validated['name'] = $request->input('name');
        $validated['password'] = uniqid();

        return $validated;
    }
}
