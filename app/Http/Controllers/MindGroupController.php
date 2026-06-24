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
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:mind_groups,name'],
            'description' => ['nullable', 'string'],
            'icon' => ['required', 'in:users,heart,gamepad-2,book-open,briefcase,music,camera,home,star'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'group')->withInput();
        }

        $validated = $validator->validated();

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
        $group = MindGroup::where('user_id', auth()->id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('mind_groups', 'name')->ignore($group->id)],
            'description' => ['nullable', 'string'],
            'icon' => ['required', 'in:users,heart,gamepad-2,book-open,briefcase,music,camera,home,star'],
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