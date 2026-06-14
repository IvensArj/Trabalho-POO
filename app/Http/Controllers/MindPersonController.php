<?php

namespace App\Http\Controllers;

use App\Models\MindGroup;
use App\Models\MindPerson;
use Illuminate\Http\Request;

class MindPersonController extends Controller
{
    public function index()
    {
        $people = MindPerson::with('group')
            ->latest()
            ->get();

        $groups = MindGroup::orderBy('name')
            ->get();

        return view(
            'mindsocial.index',
            compact(
                'people',
                'groups'
            )
        );
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
