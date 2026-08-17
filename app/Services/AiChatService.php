<?php

namespace App\Services;

use App\Models\Inventario\Medicamento;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private float $temperature;
    private int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('ai-chat.api_key');
        $this->baseUrl = config('ai-chat.base_url');
        $this->model = config('ai-chat.model');
        $this->temperature = config('ai-chat.temperature', 0.7);
        $this->maxTokens = config('ai-chat.max_tokens', 1500);
    }

    public function buildSystemPrompt(string $catalog, ?string $antecedentes = null, ?string $nombreUsuario = null): string
    {
        $identidadSection = '';
        if ($nombreUsuario) {
            $identidadSection = "\n## IDENTIDAD DEL PACIENTE\nEl paciente con el que estás hablando está registrado en la tienda y se llama: {$nombreUsuario}. Dirígete a él por su nombre de manera natural cuando sea oportuno, sin presentarte ni saludarlo en cada respuesta. Si te pregunta quién es o cómo se llama, infórmale educadamente.\n";
        } else {
            $identidadSection = "\n## IDENTIDAD DEL PACIENTE\nEl paciente no ha iniciado sesión, es un usuario invitado. Trátalo como 'Invitado' de forma genérica.\n";
        }

        $antecedentesSection = '';
        if ($antecedentes) {
            $antecedentesSection = "\n## ANTECEDENTES MÉDICOS DEL PACIENTE (SOLO PARA REFERENCIA DE SEGURIDAD)\n";
            $antecedentesSection .= "Usa esta información de salud únicamente para evitar sugerir medicamentos contraindicados. NO asumas que la consulta actual del paciente está relacionada con estos antecedentes a menos que el propio paciente lo mencione. Antecedentes:\n";
            $antecedentesSection .= "- " . $antecedentes . "\n";
        }

        // Obtener la información de todas las sucursales activas en tiempo real
        $sucursales = \App\Models\Sucursal::where('activo', true)->get();
        $sucursalesSection = "\n## NUESTRAS SUCURSALES (RECOJO EN TIENDA)\n";
        $sucursalesSection .= "Disponemos de las siguientes ubicaciones físicas operativas para recojo de pedidos y consultas presenciales:\n";
        foreach ($sucursales as $s) {
            $sucursalesSection .= "- **{$s->nombre}**: Dirección: {$s->direccion}, {$s->distrito}, {$s->provincia}";
            if ($s->telefono) {
                $sucursalesSection .= " | Teléfono: {$s->telefono}";
            }
            if ($s->latitud && $s->longitud) {
                $sucursalesSection .= " | Coordenadas del mapa: {$s->latitud}, {$s->longitud}";
            }
            $sucursalesSection .= "\n";
        }
        $sucursalesSection .= "Si el usuario pregunta por la ubicación de alguna de nuestras tiendas, proporcionales esta información de forma amable y recuérdales que también pueden verlas en el mapa interactivo de la sección /tienda/sucursales.\n";

        return <<<PROMPT
Eres el Asistente Virtual de Farmacia, un auxiliar farmaceutico digital. Tu proposito es orientar a clientes sobre sintomas comunes y recomendar productos disponibles en nuestra tienda.
{$identidadSection}
{$antecedentesSection}
{$sucursalesSection}
## REGLAS INQUEBRANTABLES

### 1. SEGURIDAD MEDICA - LEE ESTO PRIMERO
- **NO ERES MEDICO.** No tienes licencia para diagnosticar. Dado que la interfaz del chat ya cuenta con un aviso legal visible permanente y un mensaje de bienvenida explícito, **NO repitas tu presentación ni aclaraciones de que no eres médico en cada mensaje**.
- **Jamas** des un diagnostico definitivo. No digas "Usted tiene X enfermedad". En su lugar usa frases como: *"Los sintomas que describes podrian estar asociados con..."* o *"Es comun que estas molestias se deban a..."*.
- **Prudencia**: Ofrece recomendaciones con carácter informativo y orientativo para el alivio de síntomas leves. Sugiere consultar con un especialista si las molestias persisten o empeoran.

### 2. PROTOCOLO DE EMERGENCIAS GRAVES
Ante sintomas que sugieran una condicion potencialmente mortal (dolor opresivo en el pecho, dificultad grave para respirar, perdida del conocimiento, hemorragias severas, paralisis repentina, etc.):
- **NO** recomiendes productos de la tienda.
- **Deriva INMEDIATAMENTE** a servicios de emergencia (911, ambulancias, hospital mas cercano).
- Se claro y directo: no sugerir automedicacion en estos casos.

### 3. RECOMENDACION DE PRODUCTOS
- **SOLO** recomienda productos que aparecen en el catalogo de abajo. No inventes medicamentos ni sugieras productos que no estan listados.
- Si el medicamento requiere receta medica, informalo claramente al usuario.
- Recomienda productos de venta libre (OTC) para alivio de sintomas.
- **SINTAXIS DE RECOMENDACIÓN (¡MUY IMPORTANTE!)**: Cada vez que recomiendes un producto del catálogo, debes incluirlo en tu mensaje usando exactamente esta sintaxis markdown: `[Nombre del Producto](product:ID)`, donde ID es el número ID del producto que figura en el catálogo. Por ejemplo: `[Paracetamol 500mg](product:12)`. Esto es fundamental para que el sistema lo renderice como una tarjeta interactiva con botón de compra directa.

### 4. TONO Y ESTILO
- Empatico, profesional, claro, seguro y respetuoso.
- Espanol neutro.
- Respuestas concisas y utiles, directas al grano.

## CATALOGO DE PRODUCTOS DISPONIBLES

{$catalog}

---
**Importante:** Responde de forma directa, concisa y natural a la consulta del usuario. Evita repetir disclaimers o introducciones que ya están presentes en la cabecera del chat o al inicio del historial.
PROMPT;
    }

    public function buildCatalogTextForQuery(string $userQuery): string
    {
        $cleanQuery = preg_replace('/[^\p{L}\p{N}\s]/u', '', strtolower($userQuery));
        $allWords = explode(' ', $cleanQuery);
        
        $stopWords = [
            'para', 'tengo', 'duele', 'dolor', 'busco', 'algo', 'como', 'con', 'por', 'que', 
            'una', 'este', 'esta', 'unos', 'unas', 'tiene', 'tienen', 'hola', 'buenos', 'dias',
            'tardes', 'noches', 'ayuda', 'favor', 'recomienda', 'sugiere', 'receta', 'medico'
        ];
        
        $keywords = array_filter($allWords, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });

        $query = \App\Models\Tienda\TiendaProducto::with(['medicamento.categoria', 'sucursal'])
            ->where('visible', true);

        if (!empty($keywords)) {
            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $term = '%' . $word . '%';
                    $q->orWhere('nombre_web', 'LIKE', $term)
                      ->orWhereHas('medicamento', function($med) use ($term) {
                          $med->where('nombre', 'LIKE', $term)
                              ->orWhere('descripcion', 'LIKE', $term)
                              ->orWhere('laboratorio', 'LIKE', $term)
                              ->orWhereHas('categoria', function($cat) use ($term) {
                                  $cat->where('nombre', 'LIKE', $term);
                              });
                      });
                }
            });
        }

        $productos = $query->take(15)->get();

        if ($productos->isEmpty()) {
            $productos = \App\Models\Tienda\TiendaProducto::with(['medicamento.categoria', 'sucursal'])
                ->where('visible', true)
                ->take(10)
                ->get();
        }

        $lines = [];
        foreach ($productos as $p) {
            $m = $p->medicamento;
            if (!$m) continue;

            $nombre = $p->nombre;
            $id = $p->id;
            $precio = $p->precioVenta();
            $receta = $m->receta_medica ? '[REQUIERE RECETA]' : '[VENTA LIBRE]';
            $sucursal = $p->sucursal ? $p->sucursal->nombre : 'General';
            $descripcion = $p->descripcion_web ?: ($m->descripcion ?? '');

            $linea = "ID: {$id} | {$nombre} | Precio: S/. {$precio} | Sucursal: {$sucursal} | {$receta}";

            if ($descripcion) {
                $desc = mb_substr(trim(strip_tags($descripcion)), 0, 100);
                $linea .= " | Desc: {$desc}";
            }

            $lines[] = $linea;
        }

        return implode("\n", $lines);
    }

    public function extraerYActualizarAntecedentes(string $userMessage, string $assistantResponse, $clienteId, $deviceFingerprint)
    {
        $dialog = "Paciente: {$userMessage}\nAsistente: {$assistantResponse}";

        try {
            $response = Http::timeout(10)
                ->withToken($this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Eres un extractor de antecedentes medicos clinico. Analiza el dialogo provisto y extrae unicamente si el paciente menciona: 1) Alergias a medicamentos, 2) Enfermedades cronicas (hipertension, diabetes, etc.), 3) Medicamentos que debe evitar. Responde unicamente con una lista de los antecedentes separados por comas, o la palabra 'NINGUNO' si no hay informacion nueva de este tipo. No des explicaciones."
                        ],
                        [
                            'role' => 'user',
                            'content' => $dialog
                        ]
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 100,
                ]);

            if ($response->successful()) {
                $text = trim($response->json('choices.0.message.content') ?? '');
                if ($text && strtoupper($text) !== 'NINGUNO' && stripos($text, 'ninguno') === false) {
                    $perfil = \App\Models\Tienda\ChatPerfilMedico::where(function($q) use ($clienteId, $deviceFingerprint) {
                        if ($clienteId) {
                            $q->where('cliente_id', $clienteId);
                        } else {
                            $q->where('device_fingerprint', $deviceFingerprint)
                              ->whereNull('cliente_id');
                        }
                    })->first();

                    if ($perfil) {
                        $nuevoTexto = $perfil->antecedentes . ', ' . $text;
                        $items = array_unique(array_map('trim', explode(',', $nuevoTexto)));
                        $perfil->antecedentes = implode(', ', $items);
                        $perfil->save();
                    } else {
                        \App\Models\Tienda\ChatPerfilMedico::create([
                            'cliente_id' => $clienteId,
                            'device_fingerprint' => $clienteId ? null : $deviceFingerprint,
                            'antecedentes' => $text,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al extraer antecedentes del chat: ' . $e->getMessage());
        }
    }

    public function chatStream(string $userMessage, array $history, ?string $antecedentes, ?string $nombreUsuario, callable $onChunk): string
    {
        $catalog = $this->buildCatalogTextForQuery($userMessage);
        $systemPrompt = $this->buildSystemPrompt($catalog, $antecedentes, $nombreUsuario);

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        $maxHistory = config('ai-chat.max_history_messages', 20);
        $recentHistory = array_slice($history, -$maxHistory);

        foreach ($recentHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $fullResponse = '';

        try {
            $response = Http::timeout(60)
                ->withToken($this->apiKey)
                ->withHeaders([
                    'Accept' => 'text/event-stream',
                    'Content-Type' => 'application/json',
                ])
                ->withOptions(['stream' => true])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                    'stream' => true,
                ]);

            if ($response->failed()) {
                Log::error('AI Chat API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $onChunk("Lo siento, ocurrio un error al procesar tu consulta. Por favor, intenta nuevamente.");
                return '';
            }

            $body = $response->toPsrResponse()->getBody();

            while (!$body->eof()) {
                $line = $this->readLine($body);

                if (empty($line)) {
                    continue;
                }

                if ($line === 'data: [DONE]') {
                    break;
                }

                $data = $this->parseChunk($line);

                if ($data && isset($data['choices'][0]['delta']['content'])) {
                    $content = $data['choices'][0]['delta']['content'];
                    if ($content) {
                        $fullResponse .= $content;
                        $onChunk($content);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('AI Chat stream error: ' . $e->getMessage());
            $onChunk("Lo siento, ocurrio un error en la conexion. Por favor, intenta nuevamente.");
        }

        return $fullResponse;
    }

    public function buildPersonalCatalogTextForQuery(string $userQuery, int $sucursalId): string
    {
        $cleanQuery = preg_replace('/[^\p{L}\p{N}\s]/u', '', strtolower($userQuery));
        $allWords = explode(' ', $cleanQuery);
        
        $stopWords = [
            'para', 'tengo', 'duele', 'dolor', 'busco', 'algo', 'como', 'con', 'por', 'que', 
            'una', 'este', 'esta', 'unos', 'unas', 'tiene', 'tienen', 'hola', 'buenos', 'dias',
            'tardes', 'noches', 'ayuda', 'favor', 'recomienda', 'sugiere', 'receta', 'medico',
            'tenemos', 'stock', 'de', 'del', 'la', 'el', 'en', 'hay'
        ];
        
        $keywords = array_filter($allWords, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });

        // 1. Buscamos IDs de medicamentos de forma directa en Medicamento para evitar subconsultas lentas
        $medicamentoQuery = \App\Models\Inventario\Medicamento::query();
        
        $medicamentoQuery->where(function($q) use ($keywords, $cleanQuery) {
            if (strlen($cleanQuery) > 2) {
                $q->orWhere('nombre', 'LIKE', '%' . $cleanQuery . '%')
                  ->orWhere('descripcion', 'LIKE', '%' . $cleanQuery . '%');
            }
            foreach ($keywords as $word) {
                $q->orWhere('nombre', 'LIKE', '%' . $word . '%')
                  ->orWhere('descripcion', 'LIKE', '%' . $word . '%')
                  ->orWhere('laboratorio', 'LIKE', '%' . $word . '%')
                  ->orWhere('codigo', 'LIKE', '%' . $word . '%');
            }
        });

        $medIds = $medicamentoQuery->pluck('id');

        // FALLBACK: Si no hay resultados directos, buscamos por prefijo (3 primeros caracteres) para tolerar errores tipográficos
        if ($medIds->isEmpty() && !empty($keywords)) {
            $fallbackQuery = \App\Models\Inventario\Medicamento::query();
            $fallbackQuery->where(function($q) use ($keywords) {
                foreach ($keywords as $word) {
                    if (strlen($word) >= 3) {
                        $prefix = substr($word, 0, 3) . '%';
                        $q->orWhere('nombre', 'LIKE', $prefix)
                          ->orWhere('laboratorio', 'LIKE', $prefix);
                    }
                }
            });
            $medIds = $fallbackQuery->pluck('id');
        }

        // 2. Buscamos la relación en MedicamentoSucursal con sus lotes activos (>0 stock)
        $productos = \App\Models\Inventario\MedicamentoSucursal::with(['medicamento.categoria', 'lotes' => function($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId)->where('stock_actual', '>', 0);
        }])
        ->where('sucursal_id', $sucursalId)
        ->where('activo', true)
        ->whereIn('medicamento_id', $medIds)
        ->get();

        // 3. Filtrar estrictamente: Solo nos interesan productos que tengan stock > 0 real en sus lotes vigentes
        $productosConStock = $productos->filter(function($p) {
            return $p->lotes->sum('stock_actual') > 0;
        })->take(30);

        // Si no hay productos con stock que coincidan con la búsqueda, traemos los primeros 30 productos con stock de la sucursal de forma genérica
        if ($productosConStock->isEmpty()) {
            $productosConStock = \App\Models\Inventario\MedicamentoSucursal::with(['medicamento.categoria', 'lotes' => function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)->where('stock_actual', '>', 0);
            }])
            ->where('sucursal_id', $sucursalId)
            ->where('activo', true)
            ->get()
            ->filter(function($p) {
                return $p->lotes->sum('stock_actual') > 0;
            })
            ->take(30);
        }

        $lines = [];
        foreach ($productosConStock as $p) {
            $m = $p->medicamento;
            if (!$m) continue;

            $stockTotal = $p->lotes->sum('stock_actual');
            $line = "ID: {$p->medicamento_id} | Nombre: {$m->nombre} | Stock Total: {$stockTotal}";
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    public function buildPersonalSystemPrompt(string $catalog, string $nombreUsuario, string $nombreSucursal): string
    {
        return <<<PROMPT
Eres FarmaCopiloto, un asistente clinico y de inventario inteligente para el personal interno de la farmacia. Estas hablando con el empleado: {$nombreUsuario}, de la sucursal activa: {$nombreSucursal}.

Tu proposito es ayudar al farmaceutico en sus labores diarias: consultar rápidamente la disponibilidad de medicamentos y abrir el flujo de venta directa.

## DIRECTRICES DE RESPUESTA (ESTRICTAS)
- **Formato Ultra Simplificado (Solo Nombre y Botón Usar):**
  - Muestra la lista de medicamentos encontrados usando viñetas simples (`-`).
  - Para cada medicamento, debes imprimir **ÚNICAMENTE** el nombre del medicamento en negrita seguido inmediatamente del botón de venta `[Vender:[ID]]` y nada más.
  - **NO incluyas** laboratorio, lotes, fecha de vencimiento, categoría, precios, stock restante ni ninguna otra especificación o texto de relleno.
  - Ejemplo de formato exacto a seguir:
    `- **Amoxicilina 500mg** [Vender:12]`
- **Manejo de Existencias:** El catálogo de abajo solo contiene los medicamentos que **sí tienen stock** en la sucursal. Si el usuario te pregunta por un medicamento que no está en el catálogo, dile de forma muy breve que no se encontraron existencias del producto con ese nombre en esta sucursal y sugiérele buscar con otro nombre o principio activo.
- **Máximo de Medicamentos:** En tu respuesta, **sugiere o muestra un máximo de 5 medicamentos o menos** para mantener la respuesta limpia. Solo si el usuario te solicita explícitamente listar más o mostrar todas las alternativas disponibles, puedes exceder este límite.
- **Sin Rodeos ni Repeticiones:** Sé extremadamente claro, directo al grano y preciso. No repitas información en la misma respuesta ni des explicaciones redundantes. Evita introducciones largas o saludos repetitivos en cada mensaje.
- **Tono Profesional y Tecnico:** Puedes usar terminologia clinica porque estas hablando con un profesional o auxiliar de farmacia calificado, no con el cliente final. Se preciso, breve y util.

## INVENTARIO CON STOCK EN LA SUCURSAL ({$nombreSucursal})
{$catalog}
PROMPT;
    }

    public function chatStreamPersonal(string $userMessage, array $history, string $catalog, string $nombreUsuario, string $nombreSucursal, callable $onChunk): string
    {
        $systemPrompt = $this->buildPersonalSystemPrompt($catalog, $nombreUsuario, $nombreSucursal);

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        $maxHistory = config('ai-chat.max_history_messages', 20);
        $recentHistory = array_slice($history, -$maxHistory);

        foreach ($recentHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $fullResponse = '';

        try {
            $response = Http::timeout(60)
                ->withToken($this->apiKey)
                ->withHeaders([
                    'Accept' => 'text/event-stream',
                    'Content-Type' => 'application/json',
                ])
                ->withOptions(['stream' => true])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.2,
                    'max_tokens' => $this->maxTokens,
                    'stream' => true,
                ]);

            if ($response->failed()) {
                Log::error('Personal AI Chat API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $onChunk("Tuvimos un problema al obtener la respuesta de la IA. Por favor, vuelva a preguntar o reinicie el chat.");
                return '';
            }

            $body = $response->toPsrResponse()->getBody();

            while (!$body->eof()) {
                $line = $this->readLine($body);

                if (empty($line)) {
                    continue;
                }

                if ($line === 'data: [DONE]') {
                    break;
                }

                $data = $this->parseChunk($line);

                if ($data && isset($data['choices'][0]['delta']['content'])) {
                    $content = $data['choices'][0]['delta']['content'];
                    if ($content) {
                        $fullResponse .= $content;
                        $onChunk($content);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Personal AI Chat stream error: ' . $e->getMessage());
            $onChunk("Tuvimos un problema al obtener la respuesta de la IA. Por favor, vuelva a preguntar o reinicie el chat.");
        }

        return $fullResponse;
    }

    private function readLine($stream): string
    {
        $buffer = '';

        while (!$stream->eof()) {
            $byte = $stream->read(1);
            if ($byte === false || $byte === '') {
                break;
            }
            if ($byte === "\n") {
                break;
            }
            if ($byte === "\r") {
                continue;
            }
            $buffer .= $byte;
        }

        return trim($buffer);
    }

    private function parseChunk(string $line): ?array
    {
        if (!str_starts_with($line, 'data: ')) {
            return null;
        }

        $json = substr($line, 6);

        if ($json === '[DONE]') {
            return null;
        }

        $data = json_decode($json, true);
        return $data ?: null;
    }
}
