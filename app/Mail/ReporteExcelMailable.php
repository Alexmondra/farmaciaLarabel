<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Ventas\VentasHistorialExport;
use Illuminate\Support\Facades\Storage;

class ReporteExcelMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $filters;
    public $fileName;

    public $timeout = 700;

    public function __construct($filters, $fileName)
    {
        $this->filters = $filters;
        $this->fileName = $fileName;
    }

    public function build()
    {
        $tempPath = 'temp_reports/' . $this->fileName;
        $disk = 'local';

        try {
            // 1. Asegurar que la carpeta exista
            if (!Storage::disk($disk)->exists('temp_reports')) {
                Storage::disk($disk)->makeDirectory('temp_reports');
            }

            // 2. Intentar guardar y verificar el retorno
            // store() devuelve true si se creó con éxito
            $creado = Excel::store(new VentasHistorialExport($this->filters), $tempPath, $disk);

            if (!$creado) {
                throw new \Exception("Laravel-Excel no pudo guardar el archivo en el disco.");
            }

            $fullPath = Storage::disk($disk)->path($tempPath);

            // 3. Verificación física antes de adjuntar
            if (!file_exists($fullPath)) {
                throw new \Exception("Archivo no encontrado físicamente en: " . $fullPath);
            }

            return $this->subject('Reporte de Ventas Solicitado')
                ->view('emails.reporte_excel')
                ->attach($fullPath, [
                    'as' => $this->fileName,
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
        } catch (\Exception $e) {
            // ESTO ES LO MÁS IMPORTANTE: Verás el error REAL en el log
            \Log::error("Fallo crítico en Mailable: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
