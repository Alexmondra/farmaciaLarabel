<?php

namespace App\Jobs;

use App\Models\Ventas\NotaCredito;
use App\Models\Ventas\Venta;
use App\Services\Sunat\SunatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcesarAnulacionSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nota;
    protected $venta;

    public $tries = 5;
    public $backoff = 60;

    public function __construct(NotaCredito $nota, Venta $venta)
    {
        $this->nota = $nota;
        $this->venta = $venta;
    }

    public function handle(SunatService $sunatService)
    {
        Log::channel('worker')->info("Enviando NC a SUNAT: {$this->nota->serie}-{$this->nota->numero}");

        $exito = $sunatService->transmitirNotaCredito($this->nota, $this->venta);

        if (!$exito) {
            throw new \Exception("Fallo envío de Nota de Crédito ID: " . $this->nota->id);
        }

        Log::channel('worker')->info("NC Aceptada por SUNAT: {$this->nota->serie}-{$this->nota->numero}");
    }
}
