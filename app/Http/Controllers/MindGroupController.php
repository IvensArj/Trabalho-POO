<?php

namespace App\Http\Controllers;

use App\Models\MindGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MindGroupController extends Controller
{
    public function index()
    {
        return redirect()->route('mind-social.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => [
                'required',
                'in:users,heart,gamepad-2,book-open,briefcase,music,camera,home,star'
            ],
        ]);

        MindGroup::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'] . '-' . Str::random(6)),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'],
        ]);

        return back()->with('success', 'Grupo criado com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $group = MindGroup::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => [
                'required',
                'in:users,heart,gamepad-2,book-open,briefcase,music,camera,home,star'
            ],
        ]);

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return back();
    }

    public function destroy(string $id)
    {
        $group = MindGroup::findOrFail($id);
        $group->delete();

        return back()->with('success', 'Grupo removido com sucesso.');
    }
}