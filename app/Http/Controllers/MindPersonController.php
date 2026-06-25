<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMindPersonPhoto;
use App\Models\MindGroup;
use App\Models\MindPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image;

class MindPersonController extends Controller
{
    public function index()
    {
        $people = MindPerson::where('user_id', auth()->id())
            ->with('groups')
            ->latest()
            ->get();

        $groups = MindGroup::where('user_id', auth()->id())
            ->withCount('people')
            ->orderBy('name')
            ->get();

        return view('mindsocial.index', compact('people', 'groups'));
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'nickname'    => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
            'photo'       => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'groups'      => ['nullable', 'array'],
            'groups.*'    => ['integer', Rule::exists('mind_groups', 'id')->where('user_id', $userId)],
            'birth_day'   => ['nullable', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'integer', 'between:1,12'],
            'birth_year'  => ['nullable', 'integer', 'between:1900,' . date('Y')],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'photo.image'   => 'O arquivo enviado precisa ser uma imagem.',
            'photo.mimes'   => 'A foto deve ser JPG, PNG ou WEBP.',
            'photo.max'     => 'A foto não pode passar de 10MB.',
            'groups.*.exists' => 'Um dos grupos selecionados é inválido.',
            'birth_day.between'   => 'O dia do aniversário é inválido.',
            'birth_month.between' => 'O mês do aniversário é inválido.',
            'birth_year.between'  => 'O ano do aniversário é inválido.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'person')->withInput();
        }

        $validated = $validator->validated();

        $person = DB::transaction(function () use ($validated, $userId) {
            $person = MindPerson::create([
                'name'        => $validated['name'],
                'nickname'    => $validated['nickname'] ?? null,
                'photo'       => null,
                'notes'       => $validated['notes'] ?? null,
                'birth_day'   => $validated['birth_day'] ?? null,
                'birth_month' => $validated['birth_month'] ?? null,
                'birth_year'  => $validated['birth_year'] ?? null,
            ]);

            if (! empty($validated['groups'])) {
                $person->groups()->sync($validated['groups']);
            }

            return $person;
        });

        // Se houver foto, salva diretamente no storage (sem job)
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            \Log::info('Upload iniciado', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);

            try {
                $extension = $file->extension() ?: 'jpg';
                $filename = 'mind-people/' . uniqid('p_', true) . '.' . $extension;

                $path = Storage::disk('public')->putFileAs('mind-people', $file, basename($filename));

                if (!$path) {
                    throw new \Exception('Não foi possível salvar a imagem.');
                }

                $person->update(['photo' => $path]);

                \Log::info('Imagem salva com sucesso', ['path' => $path]);

            } catch (\Throwable $e) {
                \Log::error('Erro ao salvar a imagem', ['error' => $e->getMessage()]);
                return back()->withErrors(['photo' => 'Erro ao salvar a foto.'])->withInput();
            }
        }

        return redirect()->route('mind-social.index')->with('success', 'Pessoa criada com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $userId = auth()->id();

        // with('groups') evita nova query quando chamarmos $person->groups()->sync().
        $person = MindPerson::where('user_id', $userId)
            ->with('groups')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'nickname'    => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
            'photo'       => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'groups'      => ['nullable', 'array'],
            'groups.*'    => ['integer', Rule::exists('mind_groups', 'id')->where('user_id', $userId)],
            'birth_day'   => ['nullable', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'integer', 'between:1,12'],
            'birth_year'  => ['nullable', 'integer', 'between:1900,' . date('Y')],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'photo.image'   => 'O arquivo enviado precisa ser uma imagem.',
            'photo.mimes'   => 'A foto deve ser JPG, PNG ou WEBP.',
            'photo.max'     => 'A foto não pode passar de 10MB.',
            'groups.*.exists' => 'Um dos grupos selecionados é inválido.',
            'birth_day.between'   => 'O dia do aniversário é inválido.',
            'birth_month.between' => 'O mês do aniversário é inválido.',
            'birth_year.between'  => 'O ano do aniversário é inválido.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'person')->withInput();
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($person, $validated) {
            $data = [
                'name'        => $validated['name'],
                'nickname'    => $validated['nickname'] ?? null,
                'notes'       => $validated['notes'] ?? null,
                'birth_day'   => $validated['birth_day'] ?? null,
                'birth_month' => $validated['birth_month'] ?? null,
                'birth_year'  => $validated['birth_year'] ?? null,
            ];

            $person->update($data);
            $person->groups()->sync($validated['groups'] ?? []);
        });

        // Substituição de foto também vai para o job em background.
        if ($request->hasFile('photo')) {
            if ($person->photo) {
                Storage::disk('public')->delete($person->photo);
            }

            $tempPath = $request->file('photo')->storeAs(
                'mind-people-tmp',
                uniqid('upload_', true) . '.' . $request->file('photo')->extension(),
                'public'
            );

            // Zera a foto atual — o job vai sobrescrever quando terminar.
            $person->update(['photo' => null]);
            ProcessMindPersonPhoto::dispatch($person->id, $tempPath);
        }

        return redirect()->route('mind-social.index')->with('success', 'Pessoa atualizada com sucesso.');
    }

    public function destroy(string $id)
    {
        $person = MindPerson::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($person) {
            if ($person->photo) {
                Storage::disk('public')->delete($person->photo);
            }

            $person->groups()->detach();
            $person->delete();
        });

        return back()->with('success', 'Pessoa removida com sucesso.');
    }

    public function fisheye()
    {
        $people = MindPerson::where('user_id', auth()->id())
            ->with('groups')
            ->orderBy('name')
            ->get();

        return view('mindsocial.fisheye', compact('people'));
    }
}