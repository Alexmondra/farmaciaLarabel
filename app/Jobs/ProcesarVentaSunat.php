<?php

namespace App\Jobs;

use App\Models\Ventas\Venta;
use App\Services\Sunat\SunatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcesarVentaSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $venta;

    // Si falla la conexión a SUNAT, reintenta 5 veces automáticamente
    public $tries = 5;
    // Espera 60 segundos entre reintentos
    public $backoff = 60;

    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    public function handle(SunatService $sunatService)
    {
        $exito = $sunatService->transmitirAComprobante($this->venta);

        if (!$exito && $this->venta->estado === 'PENDIENTE') {
            throw new \Exception("Error temporal de conexión con SUNAT para Venta ID {$this->venta->id}: " . $this->venta->mensaje_sunat);
        }
    }
}
