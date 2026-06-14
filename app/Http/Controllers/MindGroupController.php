<?php

namespace App\Http\Controllers;

use App\Models\MindGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MindGroupController extends Controller
{
    public function index()
    {
        $groups = MindGroup::latest()->get();

        return view(
            'mindsocial.groups.index',
            compact('groups')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
        ]);

        MindGroup::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . time()),
        ]);

        return redirect()->back();
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
