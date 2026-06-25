<?php

namespace App\Jobs;

use App\Models\MindPerson;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Processa a foto de uma pessoa em background.
 *
 * Esse job existe para tirar do request HTTP o pipeline CPU-bound de
 * `scaleDown(1200) -> cover(600,600) -> toWebp(80)`, que para uma foto
 * de 8–10 MB pode levar 3–8 segundos e travar o navegador do usuário.
 *
 * Fluxo:
 *   1. Controller salva o upload bruto em `mind-people-tmp/`.
 *   2. Controller cria a pessoa com `photo = null` e despacha este job.
 *   3. O request HTTP retorna em ~200 ms.
 *   4. O worker processa a imagem, salva em `mind-people/` e atualiza a pessoa.
 */
class ProcessMindPersonPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Limite de tentativas antes de descartar o job. */
    public int $tries = 3;

    /** Timeout em segundos (proteção contra travamento). */
    public int $timeout = 120;

    public function __construct(
        public int $personId,
        public string $tempPath,
    ) {}

    public function handle(): void
    {
        $disk = Storage::disk('public');

        // Força o log no console do worker para vermos
        echo "Job iniciado para person_id: {$this->personId}\n";

        if (! $disk->exists($this->tempPath)) {
            echo "Arquivo temporário não encontrado\n";
            Log::warning('ProcessMindPersonPhoto: arquivo temporário não encontrado', [
                'person_id' => $this->personId,
                'temp_path' => $this->tempPath,
            ]);
            return;
        }

        $absolutePath = $disk->path($this->tempPath);
        echo "Arquivo encontrado: $absolutePath (tamanho: " . filesize($absolutePath) . " bytes)\n";

        try {
            echo "Lendo imagem...\n";
            $image = Image::read($absolutePath);
            echo "Imagem lida com sucesso\n";

            echo "Redimensionando...\n";
            $image->scaleDown(1200)->cover(600, 600);
            echo "Redimensionado OK\n";

            echo "Codificando para JPEG...\n";
            $encoded = $image->encode('jpg', 85);
            echo "Codificado OK, tamanho: " . strlen($encoded) . " bytes\n";

            if (! $disk->exists('mind-people')) {
                echo "Criando diretório mind-people...\n";
                $disk->makeDirectory('mind-people');
            }

            $filename = 'mind-people/' . uniqid('p_', true) . '.jpg';
            echo "Salvando arquivo: $filename\n";
            $disk->put($filename, $encoded);
            echo "Arquivo salvo com sucesso\n";

            echo "Atualizando banco...\n";
            $updated = MindPerson::where('id', $this->personId)->update(['photo' => $filename]);
            echo "Banco atualizado, linhas afetadas: $updated\n";

            echo "Deletando temporário...\n";
            $disk->delete($this->tempPath);
            echo "Job concluído com sucesso\n";

        } catch (\Throwable $e) {
            echo "ERRO: " . $e->getMessage() . "\n";
            echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";

            Log::error('ProcessMindPersonPhoto falhou', [
                'person_id' => $this->personId,
                'temp_path' => $this->tempPath,
                'error'     => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Chamado quando o job falha definitivamente após todas as tentativas.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessMindPersonPhoto descartado após retries', [
            'person_id' => $this->personId,
            'error'     => $exception->getMessage(),
        ]);
    }
}