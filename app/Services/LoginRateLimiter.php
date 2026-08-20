<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class LoginRateLimiter
{
    /**
     * Número máximo de intentos permitidos antes de un bloqueo.
     */
    protected static $maxAttempts = 5;

    /**
     * Escala de bloqueos progresivos en segundos.
     * 1º bloqueo: 1 minuto
     * 2º bloqueo: 5 minutos
     * 3º bloqueo: 10 minutos
     * 4º bloqueo: 30 minutos
     * 5º bloqueo: 60 minutos
     * 6º o superior: 24 horas (1 día)
     */
    protected static $scales = [
        1 => 60,      // 1 minuto
        2 => 300,     // 5 minutos
        3 => 600,     // 10 minutos
        4 => 1800,    // 30 minutos
        5 => 3600,    // 60 minutos
        6 => 86400,   // 24 horas (1 día)
    ];

    /**
     * Verifica si la clave del usuario está bloqueada actualmente.
     * Retorna los segundos restantes del bloqueo, o null si no está bloqueado.
     */
    public static function blockedSeconds(string $key): ?int
    {
        $blockedUntil = Cache::get("login_blocked_until:{$key}");

        if ($blockedUntil && now()->lt($blockedUntil)) {
            return now()->diffInSeconds($blockedUntil);
        }

        // Si ya pasó el tiempo, eliminamos el bloqueo
        if ($blockedUntil) {
            Cache::forget("login_blocked_until:{$key}");
        }

        return null;
    }

    /**
     * Registra un intento de login fallido.
     * Incrementa la cuenta de fallos, y si supera el límite, aplica la escala de bloqueo.
     * Retorna los segundos de bloqueo si se ha bloqueado, o 0 si no se bloqueó.
     */
    public static function registerFailedAttempt(string $key): int
    {
        $attemptsKey = "login_attempts:{$key}";
        $attempts = Cache::get($attemptsKey, 0) + 1;
        
        // Guardamos los intentos fallidos en caché con expiración de 1 hora
        Cache::put($attemptsKey, $attempts, 3600);

        if ($attempts >= self::$maxAttempts) {
            $blockCountKey = "login_block_count:{$key}";
            $blockCount = Cache::get($blockCountKey, 0) + 1;
            
            // Mantenemos el contador de bloqueos consecutivos por 24 horas
            Cache::put($blockCountKey, $blockCount, 86400);

            // Obtener el tiempo correspondiente a la escala (máximo es el nivel 6)
            $seconds = self::$scales[$blockCount] ?? self::$scales[6];

            // Registrar el momento de desbloqueo
            Cache::put("login_blocked_until:{$key}", now()->addSeconds($seconds), $seconds);

            // Resetear el contador de intentos fallidos
            Cache::forget($attemptsKey);

            return $seconds;
        }

        return 0;
    }

    /**
     * Retorna cuántos intentos le quedan al usuario antes de ser bloqueado.
     */
    public static function attemptsRemaining(string $key): int
    {
        $attempts = Cache::get("login_attempts:{$key}", 0);
        return max(0, self::$maxAttempts - $attempts);
    }

    /**
     * Limpia completamente todos los contadores al autenticarse de forma exitosa.
     */
    public static function clear(string $key): void
    {
        Cache::forget("login_attempts:{$key}");
        Cache::forget("login_block_count:{$key}");
        Cache::forget("login_blocked_until:{$key}");
    }
}
