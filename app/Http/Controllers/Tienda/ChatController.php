<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Services\AiChatService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function showChat()
    {
        return redirect()->route('tienda.index', ['open_chat' => 1]);
    }

    public function getHistory(Request $request)
    {
        $clienteId = auth('tienda')->id();
        $fingerprint = $request->header('X-Device-Fingerprint');

        $conversacion = \App\Models\Tienda\Conversacion::with('mensajes')
            ->where('is_active', true)
            ->where(function($q) use ($clienteId, $fingerprint) {
                if ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                } elseif ($fingerprint) {
                    $q->where('device_fingerprint', $fingerprint)
                      ->whereNull('cliente_id');
                } else {
                    $q->whereRaw('1=0');
                }
            })->first();

        $history = [];
        if ($conversacion) {
            foreach ($conversacion->mensajes as $msg) {
                $history[] = [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ];
            }
        }

        return response()->json([
            'history' => $history
        ]);
    }

    public function sendMessage(Request $request, AiChatService $aiChat)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userMessage = trim($request->input('message'));
        $cliente = auth('tienda')->user();
        $clienteId = $cliente ? $cliente->id : null;
        $nombreUsuario = $cliente ? $cliente->nombre : null;
        $fingerprint = $request->header('X-Device-Fingerprint');

        // 1. Validar límite de consultas en las últimas 24 horas
        $limite = $clienteId ? 20 : 6;

        $conversacionIds = \App\Models\Tienda\Conversacion::where(function($q) use ($clienteId, $fingerprint) {
                if ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                } elseif ($fingerprint) {
                    $q->where('device_fingerprint', $fingerprint)
                      ->whereNull('cliente_id');
                } else {
                    $q->whereRaw('1=0');
                }
            })->pluck('id');

        $consultasHoy = \App\Models\Tienda\MensajeConversacion::whereIn('conversacion_id', $conversacionIds)
            ->where('role', 'user')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($consultasHoy >= $limite) {
            return response()->json([
                'error' => 'limit_reached',
                'message' => $clienteId
                    ? 'Has alcanzado el límite diario de 20 consultas. Vuelve a intentarlo mañana.'
                    : 'Has alcanzado el límite de 6 consultas gratuitas. Inicia sesión para realizar hasta 20 consultas diarias o vuelve a intentarlo mañana.'
            ], 429);
        }

        // 2. Obtener o crear conversación activa
        $conversacion = \App\Models\Tienda\Conversacion::where('is_active', true)
            ->where(function($q) use ($clienteId, $fingerprint) {
                if ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                } elseif ($fingerprint) {
                    $q->where('device_fingerprint', $fingerprint)
                      ->whereNull('cliente_id');
                } else {
                    $q->whereRaw('1=0');
                }
            })->first();

        if (!$conversacion) {
            $conversacion = \App\Models\Tienda\Conversacion::create([
                'cliente_id' => $clienteId,
                'device_fingerprint' => $clienteId ? null : $fingerprint, // Only save fingerprint for guests
                'is_active' => true,
            ]);
        }

        // 3. Guardar mensaje de usuario
        $conversacion->mensajes()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // 4. Obtener perfil médico (antecedentes) para el prompt
        $perfil = \App\Models\Tienda\ChatPerfilMedico::where(function($q) use ($clienteId, $fingerprint) {
                if ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                } elseif ($fingerprint) {
                    $q->where('device_fingerprint', $fingerprint)
                      ->whereNull('cliente_id');
                } else {
                    $q->whereRaw('1=0');
                }
            })->first();
        $antecedentes = $perfil ? $perfil->antecedentes : null;

        // 5. Cargar historial reciente de la conversación (excluyendo el que acabamos de guardar)
        $history = [];
        $recentMessages = $conversacion->mensajes()
            ->where('id', '!=', $conversacion->mensajes()->max('id'))
            ->orderBy('id', 'desc')
            ->take(15)
            ->get()
            ->reverse();

        foreach ($recentMessages as $msg) {
            $history[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        $fullResponse = '';

        $response = new StreamedResponse(function () use ($aiChat, $userMessage, $history, $antecedentes, $nombreUsuario, $conversacion, $clienteId, $fingerprint, &$fullResponse) {
            set_time_limit(60);
            if (ob_get_level()) {
                ob_end_flush();
            }
            ob_implicit_flush(true);

            $aiChat->chatStream($userMessage, $history, $antecedentes, $nombreUsuario, function (string $chunk) use (&$fullResponse) {
                $fullResponse .= $chunk;
                echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            });

            // Guardar respuesta del asistente
            $conversacion->mensajes()->create([
                'role' => 'assistant',
                'content' => $fullResponse ?: 'Lo siento, no pude generar una respuesta.',
            ]);

            // Analizar y actualizar antecedentes de manera segura
            $aiChat->extraerYActualizarAntecedentes($userMessage, $fullResponse, $clienteId, $fingerprint);

            echo "data: " . json_encode(['done' => true]) . "\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    public function resetHistory(Request $request)
    {
        $clienteId = auth('tienda')->id();
        $fingerprint = $request->header('X-Device-Fingerprint');

        \App\Models\Tienda\Conversacion::where('is_active', true)
            ->where(function($q) use ($clienteId, $fingerprint) {
                if ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                } elseif ($fingerprint) {
                    $q->where('device_fingerprint', $fingerprint)
                      ->whereNull('cliente_id');
                } else {
                    $q->whereRaw('1=0');
                }
            })->update(['is_active' => false]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('tienda.chat');
    }

    public function getProductJson(\App\Models\Tienda\TiendaProducto $producto)
    {
        abort_unless($producto->visible, 404);
        return response()->json([
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'precio' => $producto->precioVenta(),
            'receta' => $producto->medicamento->receta_medica ?? false,
            'slug' => $producto->slug,
            'sucursal' => $producto->sucursal ? $producto->sucursal->nombre : 'General',
            'imagen_url' => $producto->imagen_url,
        ]);
    }

    public function getConversations(Request $request)
    {
        $clienteId = auth('tienda')->id();
        $fingerprint = $request->header('X-Device-Fingerprint');

        $conversaciones = \App\Models\Tienda\Conversacion::where(function($q) use ($clienteId, $fingerprint) {
                if ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                } elseif ($fingerprint) {
                    $q->where('device_fingerprint', $fingerprint)
                      ->whereNull('cliente_id');
                } else {
                    $q->whereRaw('1=0');
                }
            })
            ->with(['mensajes' => function($q) {
                $q->orderBy('id', 'asc');
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        $list = $conversaciones->map(function($c) {
            $firstMsg = $c->mensajes->where('role', 'user')->first();
            $preview = $firstMsg ? \Illuminate\Support\Str::limit($firstMsg->content, 35) : 'Conversacion sin mensajes';
            return [
                'id' => $c->id,
                'preview' => $preview,
                'date' => $c->updated_at->diffForHumans(),
                'is_active' => (bool)$c->is_active,
            ];
        });

        return response()->json(['conversaciones' => $list]);
    }

    public function selectConversation(Request $request, \App\Models\Tienda\Conversacion $conversacion)
    {
        $clienteId = auth('tienda')->id();
        $fingerprint = $request->header('X-Device-Fingerprint');

        if ($clienteId) {
            if ($conversacion->cliente_id !== $clienteId) {
                abort(403);
            }
        } else {
            if ($conversacion->device_fingerprint !== $fingerprint || !is_null($conversacion->cliente_id)) {
                abort(403);
            }
        }

        \App\Models\Tienda\Conversacion::where('is_active', true)
            ->where(function($q) use ($clienteId, $fingerprint) {
                if ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                } else {
                    $q->where('device_fingerprint', $fingerprint)
                      ->whereNull('cliente_id');
                }
            })->update(['is_active' => false]);

        $conversacion->is_active = true;
        $conversacion->save();

        return response()->json(['ok' => true]);
    }
}
