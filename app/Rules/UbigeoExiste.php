<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UbigeoExiste implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        if (!preg_match('/^\d{6}$/', $value)) {
            $fail('El código de Ubigeo ingresado para :attribute debe ser de 6 dígitos.');
            return;
        }

        // 2. Consulta a la tabla 'ubigeos'
        $existe = DB::table('ubigeos')->where('codigo', $value)->exists();

        if (!$existe) {
            $fail('El código de Ubigeo ingresado para :attribute no es un Ubigeo oficial válido (No encontrado en la base de datos).');
        }
    }
}
