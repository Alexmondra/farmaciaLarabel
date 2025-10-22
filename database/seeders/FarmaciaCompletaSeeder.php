<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;

class FarmaciaCompletaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Iniciando seeder completo para farmacia real...');

        // Obtener usuario admin
        $user = User::first();
        if (!$user) {
            $this->command->error('❌ No se encontró usuario. Ejecuta primero el seeder de usuarios.');
            return;
        }

        // Obtener sucursales
        $sucursales = Sucursal::all();
        if ($sucursales->count() == 0) {
            $this->command->error('❌ No se encontraron sucursales. Ejecuta primero el seeder de sucursales.');
            return;
        }

        // Obtener categorías
        $categorias = Categoria::all();
        if ($categorias->count() == 0) {
            $this->command->error('❌ No se encontraron categorías. Ejecuta primero el seeder de categorías.');
            return;
        }

        // Medicamentos reales para farmacia
        $medicamentosData = [
            // ANALGÉSICOS
            [
                'codigo' => 'PAR500',
                'nombre' => 'Paracetamol 500mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '500mg',
                'presentacion' => 'Caja x 20 tabletas',
                'laboratorio' => 'Genfar S.A.',
                'registro_sanitario' => 'RS-2024-001234',
                'codigo_barra' => '7701234567890',
                'descripcion' => 'Analgésico y antipirético. Alivia el dolor y reduce la fiebre.',
                'categoria_nombre' => 'Analgésicos',
                'precio_compra' => 2.50,
                'precio_venta' => 4.50,
                'stock_minimo' => 50
            ],
            [
                'codigo' => 'IBU400',
                'nombre' => 'Ibuprofeno 400mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '400mg',
                'presentacion' => 'Caja x 20 tabletas',
                'laboratorio' => 'Bayer S.A.',
                'registro_sanitario' => 'RS-2024-001235',
                'codigo_barra' => '7701234567891',
                'descripcion' => 'Antiinflamatorio no esteroideo. Reduce dolor, inflamación y fiebre.',
                'categoria_nombre' => 'Antiinflamatorios',
                'precio_compra' => 3.20,
                'precio_venta' => 5.80,
                'stock_minimo' => 30
            ],
            [
                'codigo' => 'ASP100',
                'nombre' => 'Ácido Acetilsalicílico 100mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '100mg',
                'presentacion' => 'Caja x 30 tabletas',
                'laboratorio' => 'Bayer S.A.',
                'registro_sanitario' => 'RS-2024-001236',
                'codigo_barra' => '7701234567892',
                'descripcion' => 'Analgésico, antipirético y antiagregante plaquetario.',
                'categoria_nombre' => 'Analgésicos',
                'precio_compra' => 1.80,
                'precio_venta' => 3.20,
                'stock_minimo' => 40
            ],

            // ANTIBIÓTICOS
            [
                'codigo' => 'AMO500',
                'nombre' => 'Amoxicilina 500mg',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => '500mg',
                'presentacion' => 'Caja x 21 cápsulas',
                'laboratorio' => 'Genfar S.A.',
                'registro_sanitario' => 'RS-2024-001237',
                'codigo_barra' => '7701234567893',
                'descripcion' => 'Antibiótico de amplio espectro. Tratamiento de infecciones bacterianas.',
                'categoria_nombre' => 'Antibióticos',
                'precio_compra' => 8.50,
                'precio_venta' => 15.00,
                'stock_minimo' => 20
            ],
            [
                'codigo' => 'AZI500',
                'nombre' => 'Azitromicina 500mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '500mg',
                'presentacion' => 'Caja x 3 tabletas',
                'laboratorio' => 'Pfizer S.A.',
                'registro_sanitario' => 'RS-2024-001238',
                'codigo_barra' => '7701234567894',
                'descripcion' => 'Antibiótico macrólido. Tratamiento de infecciones respiratorias.',
                'categoria_nombre' => 'Antibióticos',
                'precio_compra' => 12.00,
                'precio_venta' => 22.00,
                'stock_minimo' => 15
            ],

            // ANTIHISTAMÍNICOS
            [
                'codigo' => 'LOR10',
                'nombre' => 'Loratadina 10mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '10mg',
                'presentacion' => 'Caja x 10 tabletas',
                'laboratorio' => 'Schering-Plough S.A.',
                'registro_sanitario' => 'RS-2024-001239',
                'codigo_barra' => '7701234567895',
                'descripcion' => 'Antihistamínico de segunda generación. Tratamiento de alergias.',
                'categoria_nombre' => 'Antihistamínicos',
                'precio_compra' => 4.20,
                'precio_venta' => 7.50,
                'stock_minimo' => 25
            ],

            // CARDIOVASCULARES
            [
                'codigo' => 'LOS50',
                'nombre' => 'Losartán 50mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '50mg',
                'presentacion' => 'Caja x 30 tabletas',
                'laboratorio' => 'Merck S.A.',
                'registro_sanitario' => 'RS-2024-001240',
                'codigo_barra' => '7701234567896',
                'descripcion' => 'Antihipertensivo bloqueador del receptor de angiotensina II.',
                'categoria_nombre' => 'Cardiovasculares',
                'precio_compra' => 15.50,
                'precio_venta' => 28.00,
                'stock_minimo' => 20
            ],

            // DIGESTIVOS
            [
                'codigo' => 'OME20',
                'nombre' => 'Omeprazol 20mg',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => '20mg',
                'presentacion' => 'Caja x 14 cápsulas',
                'laboratorio' => 'AstraZeneca S.A.',
                'registro_sanitario' => 'RS-2024-001241',
                'codigo_barra' => '7701234567897',
                'descripcion' => 'Inhibidor de la bomba de protones. Tratamiento de úlceras gástricas.',
                'categoria_nombre' => 'Digestivos',
                'precio_compra' => 6.80,
                'precio_venta' => 12.50,
                'stock_minimo' => 30
            ],

            // RESPIRATORIOS
            [
                'codigo' => 'SAL100',
                'nombre' => 'Salbutamol 100mcg',
                'forma_farmaceutica' => 'Inhalador',
                'concentracion' => '100mcg',
                'presentacion' => 'Inhalador x 200 dosis',
                'laboratorio' => 'GlaxoSmithKline S.A.',
                'registro_sanitario' => 'RS-2024-001242',
                'codigo_barra' => '7701234567898',
                'descripcion' => 'Broncodilatador beta-2 agonista. Tratamiento del asma.',
                'categoria_nombre' => 'Respiratorios',
                'precio_compra' => 18.50,
                'precio_venta' => 35.00,
                'stock_minimo' => 10
            ],

            // VITAMINAS
            [
                'codigo' => 'VITC1000',
                'nombre' => 'Vitamina C 1000mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '1000mg',
                'presentacion' => 'Frasco x 100 tabletas',
                'laboratorio' => 'Bayer S.A.',
                'registro_sanitario' => 'RS-2024-001243',
                'codigo_barra' => '7701234567899',
                'descripcion' => 'Suplemento de vitamina C. Fortalece el sistema inmunológico.',
                'categoria_nombre' => 'Vitaminas y Suplementos',
                'precio_compra' => 8.00,
                'precio_venta' => 15.00,
                'stock_minimo' => 20
            ],
            [
                'codigo' => 'VITD1000',
                'nombre' => 'Vitamina D3 1000 UI',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => '1000 UI',
                'presentacion' => 'Frasco x 60 cápsulas',
                'laboratorio' => 'Nature Made S.A.',
                'registro_sanitario' => 'RS-2024-001244',
                'codigo_barra' => '7701234567900',
                'descripcion' => 'Suplemento de vitamina D3. Salud ósea y sistema inmunológico.',
                'categoria_nombre' => 'Vitaminas y Suplementos',
                'precio_compra' => 12.50,
                'precio_venta' => 22.00,
                'stock_minimo' => 15
            ],

            // CUIDADO PERSONAL
            [
                'codigo' => 'ALC70',
                'nombre' => 'Alcohol 70%',
                'forma_farmaceutica' => 'Solución',
                'concentracion' => '70%',
                'presentacion' => 'Frasco x 500ml',
                'laboratorio' => 'Genfar S.A.',
                'registro_sanitario' => 'RS-2024-001245',
                'codigo_barra' => '7701234567901',
                'descripcion' => 'Antiséptico tópico. Desinfección de heridas y superficies.',
                'categoria_nombre' => 'Cuidado Personal',
                'precio_compra' => 3.50,
                'precio_venta' => 6.50,
                'stock_minimo' => 30
            ],
            [
                'codigo' => 'GAS10X10',
                'nombre' => 'Gasas Estériles 10x10cm',
                'forma_farmaceutica' => 'Gasas',
                'concentracion' => '10x10cm',
                'presentacion' => 'Paquete x 10 unidades',
                'laboratorio' => 'Johnson & Johnson S.A.',
                'registro_sanitario' => 'RS-2024-001246',
                'codigo_barra' => '7701234567902',
                'descripcion' => 'Gasas estériles para curación de heridas.',
                'categoria_nombre' => 'Cuidado Personal',
                'precio_compra' => 2.80,
                'precio_venta' => 5.20,
                'stock_minimo' => 50
            ]
        ];

        $this->command->info('💊 Creando medicamentos...');
        
        foreach ($medicamentosData as $medData) {
            // Buscar categoría
            $categoria = $categorias->where('nombre', $medData['categoria_nombre'])->first();
            
            // Crear o actualizar medicamento
            $medicamento = Medicamento::updateOrCreate(
                ['codigo' => $medData['codigo']],
                [
                    'nombre' => $medData['nombre'],
                    'forma_farmaceutica' => $medData['forma_farmaceutica'],
                    'concentracion' => $medData['concentracion'],
                    'presentacion' => $medData['presentacion'],
                    'laboratorio' => $medData['laboratorio'],
                    'registro_sanitario' => $medData['registro_sanitario'],
                    'codigo_barra' => $medData['codigo_barra'],
                    'descripcion' => $medData['descripcion'],
                    'categoria_id' => $categoria->id,
                    'user_id' => $user->id,
                    'activo' => true
                ]
            );

            // Asignar a todas las sucursales
            $sucursalIds = $sucursales->pluck('id')->toArray();
            $medicamento->sucursales()->sync($sucursalIds);
            
            // Actualizar datos de la tabla pivot
            foreach ($sucursales as $sucursal) {
                $medicamento->sucursales()->updateExistingPivot($sucursal->id, [
                    'precio_compra' => $medData['precio_compra'],
                    'precio_venta' => $medData['precio_venta'],
                    'stock_actual' => 0, // Se calculará desde lotes
                    'stock_minimo' => $medData['stock_minimo'],
                    'ubicacion' => 'Estante ' . chr(65 + rand(0, 5)) . '-' . rand(1, 10),
                    'updated_by' => $user->id
                ]);
            }
        }

        $this->command->info('📦 Creando lotes con fechas de vencimiento...');
        
        // Crear lotes para cada medicamento en cada sucursal
        $medicamentos = Medicamento::all();
        
        foreach ($medicamentos as $medicamento) {
            foreach ($sucursales as $sucursal) {
                // Crear 2-4 lotes por medicamento por sucursal
                $numLotes = rand(2, 4);
                
                for ($i = 1; $i <= $numLotes; $i++) {
                    $fechaVencimiento = Carbon::now()->addMonths(rand(6, 36));
                    $cantidadInicial = rand(50, 200);
                    $cantidadActual = rand(10, $cantidadInicial);
                    
                    $lote = Lote::create([
                        'medicamento_id' => $medicamento->id,
                        'sucursal_id' => $sucursal->id,
                        'codigo_lote' => 'LOT' . $medicamento->id . $sucursal->id . $i . date('Y'),
                        'fecha_vencimiento' => $fechaVencimiento,
                        'cantidad_inicial' => $cantidadInicial,
                        'cantidad_actual' => $cantidadActual,
                        'estado' => $fechaVencimiento->isFuture() ? 'vigente' : 'vencido'
                    ]);

                    // Crear movimientos de inventario
                    $this->crearMovimientosInventario($medicamento, $sucursal, $lote, $user);
                }
            }
        }

        $this->command->info('📊 Actualizando stock desde lotes...');
        
        // Actualizar stock_actual en medicamento_sucursal desde lotes
        foreach ($medicamentos as $medicamento) {
            foreach ($sucursales as $sucursal) {
                $stockTotal = $medicamento->lotes()
                    ->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'vigente')
                    ->sum('cantidad_actual');
                
                $medicamento->sucursales()
                    ->where('sucursal_id', $sucursal->id)
                    ->update(['stock_actual' => $stockTotal]);
            }
        }

        $this->command->info('✅ Seeder completo finalizado!');
        $this->command->info('📈 Resumen:');
        $this->command->info('- Medicamentos: ' . Medicamento::count());
        $this->command->info('- Lotes: ' . Lote::count());
        $this->command->info('- Movimientos: ' . MovimientoInventario::count());
    }

    private function crearMovimientosInventario($medicamento, $sucursal, $lote, $user)
    {
        // Movimiento de entrada inicial
        MovimientoInventario::create([
            'tipo' => 'entrada',
            'medicamento_id' => $medicamento->id,
            'sucursal_id' => $sucursal->id,
            'lote_id' => $lote->id,
            'cantidad' => $lote->cantidad_inicial,
            'motivo' => 'Compra inicial de inventario',
            'referencia' => 'FACT-' . rand(1000, 9999),
            'user_id' => $user->id,
            'stock_final' => $lote->cantidad_inicial
        ]);

        // Algunos movimientos de salida (ventas simuladas)
        if (rand(1, 3) == 1) {
            $cantidadVenta = rand(5, 20);
            MovimientoInventario::create([
                'tipo' => 'salida',
                'medicamento_id' => $medicamento->id,
                'sucursal_id' => $sucursal->id,
                'lote_id' => $lote->id,
                'cantidad' => $cantidadVenta,
                'motivo' => 'Venta al público',
                'referencia' => 'VENT-' . rand(1000, 9999),
                'user_id' => $user->id,
                'stock_final' => $lote->cantidad_actual
            ]);
        }

        // Algunos ajustes de inventario
        if (rand(1, 4) == 1) {
            $cantidadAjuste = rand(-5, 5);
            MovimientoInventario::create([
                'tipo' => 'ajuste',
                'medicamento_id' => $medicamento->id,
                'sucursal_id' => $sucursal->id,
                'lote_id' => $lote->id,
                'cantidad' => $cantidadAjuste,
                'motivo' => 'Ajuste de inventario por auditoría',
                'referencia' => 'AJUST-' . rand(1000, 9999),
                'user_id' => $user->id,
                'stock_final' => $lote->cantidad_actual
            ]);
        }
    }
}