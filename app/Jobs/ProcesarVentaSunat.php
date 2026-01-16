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

    // Si falla la conexión a SUNAT, reintenta 3 veces automáticamente
    public $tries = 3;
    // Espera 60 segundos entre reintentos
    public $backoff = 60;

    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    public function handle(SunatService $sunatService)
    {
        // Ejecuta tu lógica actual de envío que está en SunatService
        $sunatService->transmitirAComprobante($this->venta);
    }
}
