<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['codigo'=>'SUC01','nombre'=>'Sucursal Central','direccion'=>'Av. Principal 123','telefono'=>'(01) 444-1111'],
            ['codigo'=>'SUC02','nombre'=>'Sucursal Norte','direccion'=>'Jr. Las Flores 456','telefono'=>'(01) 444-2222'],
            ['codigo'=>'SUC03','nombre'=>'Sucursal Sur','direccion'=>'Calle Sol 789','telefono'=>'(01) 444-3333'],
        ];
        foreach ($data as $d) {
            Sucursal::firstOrCreate(['codigo'=>$d['codigo']], $d);
        }
    }
}
