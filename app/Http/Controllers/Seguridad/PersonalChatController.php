<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\PersonalConversacion;
use App\Models\Seguridad\PersonalMensaje;
use App\Services\AiChatService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PersonalChatController extends Controller
{
    public function getHistory(Request $request)
    {
        $userId = auth()->id();

        $conversacion = PersonalConversacion::with('mensajes')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        $history = [];
        if ($conversacion) {
            foreach ($conversacion->mensajes as $msg) {
                $history[] = [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ];
            }
        }

        // Calcular mensajes enviados hoy
        $mensajesHoy = PersonalMensaje::whereHas('conversacion', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('role', 'user')
        ->whereDate('created_at', \Carbon\Carbon::today())
        ->count();

        // Obtener límite diario
        $limiteRegistro = \App\Models\Seguridad\PersonalChatLimite::where('user_id', $userId)->first();
        $limiteDiario = $limiteRegistro ? $limiteRegistro->limite_diario : 30;

        return response()->json([
            'history' => $history,
            'messages_count' => $mensajesHoy,
            'messages_limit' => $limiteDiario,
        ]);
    }

    public function sendMessage(Request $request, AiChatService $aiChat)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
            'image' => 'nullable|string', // Base64 data URI
        ]);

        $userMessage = trim($request->input('message') ?? '');
        $image = $request->input('image');
        $user = auth()->user();
        $userId = $user->id;
        $nombreUsuario = $user->name ?: $user->username;

        if (empty($userMessage) && empty($image)) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => 'Debe ingresar un mensaje o adjuntar una imagen.'
            ], 400);
        }

        // Validar límite de mensajes de hoy antes de guardar o llamar a la IA
        $mensajesHoy = PersonalMensaje::whereHas('conversacion', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('role', 'user')
        ->whereDate('created_at', \Carbon\Carbon::today())
        ->count();

        $limiteRegistro = \App\Models\Seguridad\PersonalChatLimite::where('user_id', $userId)->first();
        $limiteDiario = $limiteRegistro ? $limiteRegistro->limite_diario : 30;

        if ($limiteDiario !== null && $mensajesHoy >= $limiteDiario) {
            return response()->json([
                'error' => 'limite_alcanzado',
                'message' => "⛔ Límite alcanzado: Has agotado tu límite de mensajes de hoy ($limiteDiario). Por favor, contacta al administrador para solicitar una ampliación de tu cuota o vuelve mañana."
            ], 403);
        }

        // Obtener la sucursal activa de la sesión
        $sucursalId = session('sucursal_id');
        $nombreSucursal = session('sucursal_nombre') ?: 'General';

        if (!$sucursalId) {
            return response()->json([
                'error' => 'no_sucursal',
                'message' => 'Por favor, selecciona una sucursal en el panel superior antes de usar FarmaCopiloto.'
            ], 400);
        }

        // 1. Obtener o crear conversación activa del personal
        $conversacion = PersonalConversacion::where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if (!$conversacion) {
            $conversacion = PersonalConversacion::create([
                'user_id' => $userId,
                'is_active' => true,
            ]);
        }

        // 2. Guardar mensaje del usuario (esto incrementará el conteo de hoy)
        // Guardamos una anotación ligera de la imagen sin almacenar el base64 pesado en DB
        $dbContent = $userMessage;
        if ($image) {
            $dbContent = "📷 [Imagen adjunta]" . ($userMessage ? " " . $userMessage : "");
        }

        $conversacion->mensajes()->create([
            'role' => 'user',
            'content' => $dbContent,
        ]);

        // 3. Buscar stock e inventario de la sucursal según la consulta
        $catalogText = $aiChat->buildPersonalCatalogTextForQuery($userMessage, $sucursalId);

        // 4. Obtener historial reciente de la conversación (excluyendo el que acabamos de guardar para el streaming)
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

        $response = new StreamedResponse(function () use ($aiChat, $userMessage, $history, $catalogText, $nombreUsuario, $nombreSucursal, $conversacion, &$fullResponse, $mensajesHoy, $limiteDiario, $image) {
            set_time_limit(60);
            if (ob_get_level()) {
                ob_end_clean();
            }

            $aiChat->chatStreamPersonal(
                $userMessage,
                $history,
                $catalogText,
                $nombreUsuario,
                $nombreSucursal,
                function ($chunk) use (&$fullResponse, $mensajesHoy, $limiteDiario) {
                    echo "data: " . json_encode([
                        'text' => $chunk,
                        'messages_count' => $mensajesHoy + 1,
                        'messages_limit' => $limiteDiario
                    ]) . "\n\n";
                    if (connection_aborted()) {
                        return false;
                    }
                    $fullResponse .= $chunk;
                    flush();
                },
                $image
            );

            // Guardar respuesta del asistente en la BD al finalizar
            if (trim($fullResponse)) {
                $conversacion->mensajes()->create([
                    'role' => 'assistant',
                    'content' => $fullResponse,
                ]);
            } else {
                $errorMsg = "Tuvimos un problema al obtener la respuesta. Por favor, vuelva a preguntar o reinicie el chat.";
                echo "data: " . json_encode([
                    'text' => $errorMsg,
                    'messages_count' => $mensajesHoy + 1,
                    'messages_limit' => $limiteDiario
                ]) . "\n\n";
                $conversacion->mensajes()->create([
                    'role' => 'assistant',
                    'content' => $errorMsg,
                ]);
            }

            echo "data: [DONE]\n\n";
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cache-Control', 'no-cache');
        return $response;
    }

    public function resetChat(Request $request)
    {
        $userId = auth()->id();

        PersonalConversacion::where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Conversacion finalizada. Iniciando una nueva.'
        ]);
    }

    public function getConversaciones(Request $request)
    {
        $userId = auth()->id();

        $conversaciones = PersonalConversacion::with(['mensajes' => function ($q) {
                $q->orderBy('id', 'desc');
            }])
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        $data = $conversaciones->map(function ($c) {
            $lastUserMessage = $c->mensajes->where('role', 'user')->first();
            $preview = $lastUserMessage ? $lastUserMessage->content : 'Conversación sin mensajes';
            
            if (mb_strlen($preview) > 40) {
                $preview = mb_substr($preview, 0, 40) . '...';
            }

            return [
                'id' => $c->id,
                'is_active' => (bool)$c->is_active,
                'preview' => $preview,
                'date' => $c->updated_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'conversaciones' => $data
        ]);
    }

    public function activateConversacion($id)
    {
        $userId = auth()->id();

        $conversacion = PersonalConversacion::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        PersonalConversacion::where('user_id', $userId)
            ->update(['is_active' => false]);

        $conversacion->update(['is_active' => true]);

        return response()->json([
            'success' => true
        ]);
    }
}
