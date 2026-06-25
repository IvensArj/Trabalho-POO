<?php

namespace App\Http\Controllers;

use App\Models\MindGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MindGroupController extends Controller
{
    public function index()
    {
        return redirect()->route('mind-social.index');
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

        $validator = Validator::make($request->all(), [
            // Unicidade agora é por usuário — evita colisão entre contas diferentes
            // e elimina a race condition do duplo-clique (índice composto no schema).
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('mind_groups', 'name')->where('user_id', $userId),
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['required', 'in:users,heart,gamepad-2,book-open,briefcase,music,camera,home,star'],
        ], [
            'name.unique' => 'Você já tem um grupo com este nome.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'group')->withInput();
        }

        $validated = $validator->validated();

        $group = MindGroup::create([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']) . '-' . Str::lower(Str::random(8)),
            'description' => $validated['description'] ?? null,
            'icon'        => $validated['icon'],
        ]);

        return back()->with('success', 'Grupo criado com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $userId = auth()->id();

        $group = MindGroup::where('user_id', $userId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('mind_groups', 'name')
                    ->where('user_id', $userId)
                    ->ignore($group->id),
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['required', 'in:users,heart,gamepad-2,book-open,briefcase,music,camera,home,star'],
        ], [
            'name.unique' => 'Você já tem um grupo com este nome.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'group')->withInput();
        }

        $group->update($validator->validated());

        return back()->with('success', 'Grupo atualizado com sucesso.');
    }

    public function destroy(string $id)
    {
        $group = MindGroup::where('user_id', auth()->id())->findOrFail($id);
        $group->delete();

        return back()->with('success', 'Grupo removido com sucesso.');
    }
}