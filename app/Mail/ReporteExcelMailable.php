<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ReporteExcelMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $filters;
    public $fileName;
    public $exportClass;

    public $timeout = 700;

    public function __construct($filters, $fileName, $exportClass = null)
    {
        $this->filters = $filters;
        $this->fileName = $fileName;
        $this->exportClass = $exportClass ?? \App\Exports\Ventas\VentasHistorialExport::class;
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

            // 2. Crear la instancia del export class dinámicamente
            $exportInstance = new $this->exportClass($this->filters);

            $creado = Excel::store($exportInstance, $tempPath, $disk);

            if (!$creado) {
                throw new \Exception("Laravel-Excel no pudo guardar el archivo en el disco.");
            }

            $fullPath = Storage::disk($disk)->path($tempPath);

            // 3. Verificación física antes de adjuntar
            if (!file_exists($fullPath)) {
                throw new \Exception("Archivo no encontrado físicamente en: " . $fullPath);
            }

            $subject = str_contains($this->fileName, 'anuladas')
                ? 'Reporte de Ventas Anuladas Solicitado'
                : 'Reporte de Ventas Solicitado';

            return $this->subject($subject)
                ->view('emails.reporte_excel')
                ->attach($fullPath, [
                    'as' => $this->fileName,
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
        } catch (\Exception $e) {
            \Log::error("Fallo crítico en Mailable: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
