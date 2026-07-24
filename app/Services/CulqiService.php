<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CulqiService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.culqi.com/v2';

    public function __construct()
    {
        $this->secretKey = config('services.culqi.secret_key');
    }

    public function crearCargo(float $monto, string $moneda, string $email, string $sourceId): array
    {
        $montoCentavos = (int) round($monto * 100);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ])
                ->withoutVerifying()
                ->retry(1, 200)
                ->post($this->baseUrl . '/charges', [
                    'amount' => $montoCentavos,
                    'currency_code' => $moneda,
                    'email' => $email,
                    'source_id' => $sourceId,
                    'capture' => true,
                ]);

            return $this->parseResponse($response, 'crearCargo');

        } catch (\Exception $e) {
            Log::error('Culqi crearCargo exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'No se pudo conectar con el servicio de pagos.'];
        }
    }

    public function crearOrden(float $monto, string $moneda, string $email, string $orderNumber, string $fullName, ?string $phone = null): array
    {
        $montoCentavos = (int) round($monto * 100);

        if ($montoCentavos < 600) {
            return ['success' => false, 'error' => 'El monto minimo para pago online es S/ 6.00.'];
        }

        $parts = array_map('trim', explode(' ', trim($fullName), 2));
        $firstName = !empty($parts[0]) ? mb_substr($parts[0], 0, 50) : '';
        $lastName = !empty($parts[1]) ? mb_substr($parts[1], 0, 50) : '';

        if (empty($firstName) || mb_strlen($firstName) < 2) {
            $firstName = 'Cliente';
        }
        if (empty($lastName)) {
            $lastName = $firstName;
        }
        if (mb_strlen($lastName) < 2) {
            $lastName = $firstName;
        }

        $phoneNumber = $phone;
        if (empty($phoneNumber) || strlen(trim($phoneNumber)) < 6) {
            $phoneNumber = '999999999';
        }

        $clientDetails = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $phoneNumber,
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ])
                ->withoutVerifying()
                ->retry(1, 200)
                ->post($this->baseUrl . '/orders', [
                    'amount' => $montoCentavos,
                    'currency_code' => $moneda,
                    'description' => 'Pedido ' . $orderNumber,
                    'order_number' => $orderNumber,
                    'client_details' => $clientDetails,
                    'expiration_date' => now()->addDays(3)->timestamp,
                    'confirm' => false,
                ]);

            return $this->parseResponse($response, 'crearOrden');

        } catch (\Exception $e) {
            Log::error('Culqi crearOrden exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'No se pudo conectar con el servicio de pagos.'];
        }
    }

    private function parseResponse($response, string $operation): array
    {
        $body = $response->json() ?? [];
        $rawBody = $response->body();

        if ($response->successful()) {
            return ['success' => true, 'data' => $body];
        }

        Log::error('Culqi ' . $operation . ' error', [
            'status' => $response->status(),
            'body' => $body,
            'raw' => $rawBody,
        ]);

        return [
            'success' => false,
            'error' => ($body['user_message'] ?? $body['merchant_message'] ?? 'Error desconocido')
                . ' [' . $response->status() . ']',
        ];
    }

    public function obtenerCargo(string $chargeId): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ])
                ->withoutVerifying()
                ->retry(1, 200)
                ->get($this->baseUrl . '/charges/' . $chargeId);

            return $this->parseResponse($response, 'obtenerCargo');

        } catch (\Exception $e) {
            Log::error('Culqi obtenerCargo exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'No se pudo conectar con el servicio de pagos.'];
        }
    }

    public function obtenerOrden(string $orderId): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ])
                ->withoutVerifying()
                ->retry(1, 200)
                ->get($this->baseUrl . '/orders/' . $orderId);

            return $this->parseResponse($response, 'obtenerOrden');

        } catch (\Exception $e) {
            Log::error('Culqi obtenerOrden exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'No se pudo conectar con el servicio de pagos.'];
        }
    }
}
