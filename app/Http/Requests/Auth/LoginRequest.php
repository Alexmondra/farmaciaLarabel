<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\LoginRateLimiter;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $key = $this->throttleKey();
            $blockedSeconds = LoginRateLimiter::registerFailedAttempt($key);

            if ($blockedSeconds) {
                event(new Lockout($this));
                $minutes = ceil($blockedSeconds / 60);
                $timeText = $minutes === 1 ? '1 minuto' : ($minutes >= 60 ? ceil($minutes / 60) . ' horas' : "{$minutes} minutos");
                if ($blockedSeconds >= 86400) {
                    $timeText = '24 horas';
                }
                throw ValidationException::withMessages([
                    'email' => "Demasiados intentos fallidos. Acceso bloqueado por {$timeText} por motivos de seguridad.",
                ]);
            }

            $remaining = LoginRateLimiter::attemptsRemaining($key);
            throw ValidationException::withMessages([
                'email' => "Las credenciales de acceso son incorrectas. Te quedan {$remaining} intentos antes de ser bloqueado.",
            ]);
        }

        LoginRateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $key = $this->throttleKey();
        $seconds = LoginRateLimiter::blockedSeconds($key);

        if (!$seconds) {
            return;
        }

        event(new Lockout($this));

        $minutes = ceil($seconds / 60);
        $timeText = $minutes === 1 ? '1 minuto' : ($minutes >= 60 ? ceil($minutes / 60) . ' horas' : "{$minutes} minutos");
        if ($seconds >= 86400) {
            $timeText = '24 horas';
        }

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos fallidos. Acceso bloqueado. Intenta de nuevo en {$timeText}.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
