<?php

namespace Database\Seeders;

use App\Models\Inventario\Medicamento;
use App\Models\Tienda\TiendaProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TiendaFarmaTestProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get all active mappings where price is valid
        $mappings = DB::table('medicamento_sucursal')
            ->where('activo', 1)
            ->where('precio_venta', '>', 0)
            ->inRandomOrder()
            ->get();

        // 2. We want to publish around 300 unique medicines.
        $selectedMappings = [];
        $usedMedicamentoIds = [];

        // Exclude medicines that are already in tienda_productos to prevent duplicates
        $existingMedicamentoIds = TiendaProducto::pluck('medicamento_id')->all();
        foreach ($existingMedicamentoIds as $id) {
            $usedMedicamentoIds[$id] = true;
        }

        foreach ($mappings as $mapping) {
            if (count($selectedMappings) >= 300) {
                break;
            }
            if (!isset($usedMedicamentoIds[$mapping->medicamento_id])) {
                $selectedMappings[] = $mapping;
                $usedMedicamentoIds[$mapping->medicamento_id] = true;
            }
        }

        // Beautiful, professional Unsplash pharmacy & medicine stock photos
        $images = [
            'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1512069772995-ec65ed45afd6?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1550572017-edd951b55104?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1607619275048-24722480f875?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1628771065518-0d82f1938462?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1587854692152-cbe660dbbab9?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1526256262350-7da7584cf5eb?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1631549916768-4119b255f926?w=500&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1547851965-4f8e36efe63d?w=500&auto=format&fit=crop&q=60',
        ];

        $insertedCount = 0;
        foreach ($selectedMappings as $mapping) {
            $medicamento = Medicamento::find($mapping->medicamento_id);
            if (!$medicamento) {
                continue;
            }

            // Assign a random high-quality image path if the medicine does not have one
            if (empty($medicamento->imagen_path)) {
                $medicamento->update([
                    'imagen_path' => $images[array_rand($images)]
                ]);
            }

            // Generate a unique slug
            $baseSlug = Str::slug($medicamento->nombre) ?: 'producto';
            $slug = $baseSlug;
            $i = 2;
            while (TiendaProducto::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            // Use manual stock mode to ensure the seeded products have available stock
            TiendaProducto::create([
                'medicamento_id' => $mapping->medicamento_id,
                'sucursal_id' => $mapping->sucursal_id,
                'slug' => $slug,
                'nombre_web' => $medicamento->nombre,
                'descripcion_web' => $medicamento->descripcion ?: ($medicamento->nombre . ' - Medicamento e insumo farmacéutico de alta calidad. Manténgase fuera del alcance de los niños. Consulte a su médico para dosis recomendadas.'),
                'precio_web' => $mapping->precio_venta,
                'stock_modo' => TiendaProducto::STOCK_MANUAL,
                'stock_web' => rand(15, 80),
                'visible' => true,
                'destacado' => (rand(1, 10) === 1), // 10% are featured
            ]);

            $insertedCount++;
        }

        $this->command->info("Se han publicado {$insertedCount} medicamentos en la tienda virtual exitosamente.");
    }
}
