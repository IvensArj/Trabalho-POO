<?php

namespace App\Http\Controllers;

use App\Models\MindGroup;
use App\Models\MindPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class MindPersonController extends Controller
{
    public function index()
    {
        $people = MindPerson::with('groups')
            ->latest()
            ->get();

        $groups = MindGroup::withCount('people')
            ->orderBy('name')
            ->get();

        return view('mindsocial.index', compact('people', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['integer', 'exists:mind_groups,id'],
            'birth_day' => ['nullable', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'integer', 'between:1,12'],
            'birth_year' => ['nullable', 'integer', 'between:1900,' . date('Y')],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'photo.image' => 'O arquivo enviado precisa ser uma imagem.',
            'photo.mimes' => 'A foto deve ser JPG, PNG ou WEBP.',
            'photo.max' => 'A foto não pode passar de 10MB.',
            'groups.*.exists' => 'Um dos grupos selecionados é inválido.',
            'birth_day.between' => 'O dia do aniversário é inválido.',
            'birth_month.between' => 'O mês do aniversário é inválido.',
            'birth_year.between' => 'O ano do aniversário é inválido.',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $this->savePhoto(
                $request->file('photo')
            );
        }

        $person = MindPerson::create([
            'name' => $validated['name'],
            'nickname' => $validated['nickname'] ?? null,
            'photo' => $photoPath,
            'notes' => $validated['notes'] ?? null,
            'birth_day' => $validated['birth_day'] ?? null,
            'birth_month' => $validated['birth_month'] ?? null,
            'birth_year' => $validated['birth_year'] ?? null,
        ]);

        $person->groups()->sync($validated['groups'] ?? []);

        return redirect()->route('mind-social.index')->with('success', 'Pessoa criada com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $person = MindPerson::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['integer', 'exists:mind_groups,id'],
            'birth_day' => ['nullable', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'integer', 'between:1,12'],
            'birth_year' => ['nullable', 'integer', 'between:1900,' . date('Y')],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'photo.image' => 'O arquivo enviado precisa ser uma imagem.',
            'photo.mimes' => 'A foto deve ser JPG, PNG ou WEBP.',
            'photo.max' => 'A foto não pode passar de 10MB.',
            'groups.*.exists' => 'Um dos grupos selecionados é inválido.',
            'birth_day.between' => 'O dia do aniversário é inválido.',
            'birth_month.between' => 'O mês do aniversário é inválido.',
            'birth_year.between' => 'O ano do aniversário é inválido.',
        ]);

        $data = [
            'name' => $validated['name'],
            'nickname' => $validated['nickname'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'birth_day' => $validated['birth_day'] ?? null,
            'birth_month' => $validated['birth_month'] ?? null,
            'birth_year' => $validated['birth_year'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            if ($person->photo) {
                Storage::disk('public')->delete($person->photo);
            }

            $data['photo'] = $this->savePhoto(
                $request->file('photo')
            );
        }

        $person->update($data);
        $person->groups()->sync($validated['groups'] ?? []);

        return redirect()->route('mind-social.index')->with('success', 'Pessoa atualizada com sucesso.');
    }

    public function destroy(string $id)
    {
        $person = MindPerson::findOrFail($id);

        if ($person->photo) {
            Storage::disk('public')->delete($person->photo);
        }

        $person->groups()->detach();
        $person->delete();

        return back()->with('success', 'Pessoa removida com sucesso.');
    }

    private function savePhoto($file): string
    {
        $filename = uniqid() . '.webp';

        $image = Image::read($file)
            ->scaleDown(width: 1200)
            ->cover(600, 600)
            ->toWebp(80);

        Storage::disk('public')->put(
            'mind-people/' . $filename,
            (string) $image
        );

        return 'mind-people/' . $filename;
    }
}