<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventario\Lote;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DetectarLotesVencidos extends Command
{
    protected $signature = 'inventario:detectar-vencidos';

    protected $description = 'Detecta lotes de medicamentos que ya han vencido y tienen stock actual, registrando la alerta en los logs del sistema.';

    public function handle(): int
    {
        $hoy = Carbon::today();
        
        $lotesVencidos = Lote::with(['medicamento', 'sucursal'])
            ->where('stock_actual', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->get();

        $totalLotes = $lotesVencidos->count();
        $totalUnidades = $lotesVencidos->sum('stock_actual');

        if ($totalLotes === 0) {
            $this->info('No se detectaron lotes vencidos con stock en el sistema.');
            return self::SUCCESS;
        }

        $this->warn("¡Alerta! Se han detectado {$totalLotes} lote(s) vencido(s) que aún tienen stock físico ({$totalUnidades} unidades en total).");

        // Registrar en logs de Laravel para control administrativo
        Log::warning("Auditoría de Inventario: Se detectaron {$totalLotes} lotes vencidos con stock. Total de unidades vencidas: {$totalUnidades}.");

        foreach ($lotesVencidos as $lote) {
            $msg = "Lote '{$lote->codigo_lote}' del medicamento '{$lote->medicamento->nombre}' en la sucursal '{$lote->sucursal->nombre}' venció el {$lote->fecha_vencimiento->format('d/m/Y')} y tiene {$lote->stock_actual} unidades.";
            $this->line("- " . $msg);
            Log::warning($msg);
        }

        return self::SUCCESS;
    }
}
