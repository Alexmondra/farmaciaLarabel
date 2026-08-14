<style>
    /* Estilos del Widget de Chat Flotante */
    .chat-widget-trigger {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981 0%, #0f766e 100%);
        border: none;
        color: white;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        cursor: pointer;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }
    .chat-widget-trigger:hover {
        transform: scale(1.08) translateY(-2px);
        box-shadow: 0 14px 30px rgba(13, 148, 136, 0.35);
    }
    .chat-widget-trigger:active {
        transform: scale(0.95);
    }
    .chat-widget-trigger svg {
        width: 28px;
        height: 28px;
        transition: transform 0.3s ease;
    }
    .chat-widget-trigger:hover svg {
        transform: rotate(8deg);
    }

    .chat-widget-trigger-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(16, 185, 129, 0.4);
        animation: chatPulse 2.5s infinite;
        z-index: -1;
    }
    @keyframes chatPulse {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }

    .chat-widget-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.2);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 9998;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .chat-widget-backdrop.active {
        opacity: 1;
        pointer-events: auto;
    }

    .chat-widget-drawer {
        position: fixed;
        top: 0;
        right: 0;
        height: 100%;
        width: 440px;
        max-width: 100%;
        background: #f8fafc;
        box-shadow: -10px 0 35px rgba(15, 23, 42, 0.12);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .chat-widget-drawer.active {
        transform: translateX(0);
    }

    .chat-widget-header {
        position: relative;
        z-index: 11;
        padding: 16px 20px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-widget-header-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .chat-widget-status-dot {
        width: 10px;
        height: 10px;
        background-color: #22c55e;
        border-radius: 50%;
        animation: statusPulse 2s infinite;
    }
    @keyframes statusPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .chat-widget-header-info h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .chat-widget-header-info p {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
    }

    .chat-widget-header-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .chat-widget-header-btn {
        background: transparent;
        border: none;
        color: #64748b;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .chat-widget-header-btn:hover {
        background: #f1f5f9;
        color: #0d9488;
    }
    .chat-widget-header-btn.close-btn:hover {
        background: #fee2e2;
        color: #ef4444;
    }
    .chat-widget-header-btn svg {
        width: 18px;
        height: 18px;
    }

    .chat-widget-disclaimer {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-bottom: 1px solid rgba(253, 230, 138, 0.5);
        padding: 10px 16px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .chat-widget-disclaimer svg {
        width: 16px;
        height: 16px;
        color: #d97706;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .chat-widget-disclaimer p {
        margin: 0;
        font-size: 0.72rem;
        color: #92400e;
        line-height: 1.4;
    }

    .chat-widget-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        background: #f8fafc;
    }
    .chat-widget-message {
        display: flex;
        gap: 10px;
        max-width: 85%;
        animation: chatSlideIn 0.25s ease;
    }
    .chat-widget-message.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }
    .chat-widget-message.assistant {
        align-self: flex-start;
    }
    .chat-widget-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .chat-widget-avatar.user {
        background: #0d9488;
        color: white;
    }
    .chat-widget-avatar.assistant {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: white;
    }

    .chat-widget-bubble {
        padding: 10px 14px;
        border-radius: 1.25rem;
        font-size: 0.88rem;
        line-height: 1.5;
        word-wrap: break-word;
    }
    .chat-widget-message.user .chat-widget-bubble {
        background: #0d9488;
        color: white;
        border-bottom-right-radius: 4px;
    }
    .chat-widget-message.assistant .chat-widget-bubble {
        background: white;
        color: #1e293b;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
    }

    /* Tarjetas de Producto dentro del Chat */
    .chat-product-card {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 12px;
        overflow: hidden;
        margin-top: 8px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        width: 260px;
        max-width: 100%;
        transition: all 0.3s ease;
    }
    .chat-product-card:hover {
        box-shadow: 0 8px 16px -1px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
        border-color: rgba(13, 148, 136, 0.2);
    }
    .chat-product-img {
        height: 110px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    }
    .chat-product-img img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }
    .chat-product-info {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .chat-product-tag {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        width: fit-content;
    }
    .chat-product-tag.libre {
        background: #f0fdf4;
        color: #16a34a;
    }
    .chat-product-tag.receta {
        background: #fef2f2;
        color: #dc2626;
    }
    .chat-product-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e293b;
        margin: 4px 0 0 0;
        line-height: 1.3;
        height: 32px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .chat-product-meta {
        font-size: 0.7rem;
        color: #64748b;
    }
    .chat-product-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(226, 232, 240, 0.5);
    }
    .chat-product-price {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.95rem;
    }
    .chat-product-btn {
        background: #0d9488;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 5px 10px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .chat-product-btn:hover {
        background: #0f766e;
    }

    .chat-widget-typing {
        display: none;
        gap: 10px;
        align-items: center;
        padding: 10px 20px;
        background: transparent;
    }
    .chat-widget-typing.visible {
        display: flex;
    }
    .chat-widget-typing-dots {
        display: flex;
        gap: 4px;
        padding: 8px 14px;
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1rem;
        border-bottom-left-radius: 4px;
    }
    .chat-widget-typing-dots span {
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: chatBounce 1.4s infinite ease-in-out;
    }
    .chat-widget-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .chat-widget-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes chatBounce {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-4px); }
    }

    .chat-widget-input-area {
        padding: 14px 20px;
        background: white;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }
    .chat-widget-input-area textarea {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 0.85rem;
        resize: none;
        outline: none;
        line-height: 1.4;
        max-height: 80px;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
    }
    .chat-widget-input-area textarea:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
    }
    .chat-widget-send-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #0d9488;
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .chat-widget-send-btn:hover {
        background: #0f766e;
        transform: translateY(-1px);
    }
    .chat-widget-send-btn:active {
        transform: scale(0.95);
    }
    .chat-widget-send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .chat-widget-send-btn svg {
        width: 16px;
        height: 16px;
    }

    /* Panel de Historial */
    .chat-widget-history-panel {
        position: absolute;
        top: 73px; /* Justo debajo de la cabecera */
        left: 0;
        width: 100%;
        height: calc(100% - 73px);
        background: white;
        z-index: 10;
        display: flex;
        flex-direction: column;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease-in-out;
        border-top: 1px solid #e2e8f0;
    }
    .chat-widget-history-panel.active {
        opacity: 1;
        pointer-events: auto;
    }
    .history-panel-header {
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .history-panel-header h5 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
    }
    .close-history-btn {
        background: transparent;
        border: none;
        font-size: 1.4rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
    .close-history-btn:hover {
        color: #ef4444;
    }
    .history-panel-list {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .history-item {
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .history-item:hover {
        background: #f0fdf4;
        border-color: #0d9488;
        transform: translateY(-1px);
    }
    .history-item.active {
        background: #f0fdf4;
        border-color: #0d9488;
        border-left: 4px solid #0d9488;
    }
    .history-item-preview {
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .history-item-date {
        font-size: 0.7rem;
        color: #94a3b8;
    }
    .history-view-all-btn {
        width: 100%;
        padding: 10px;
        background: transparent;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        color: #0d9488;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
        margin-top: 6px;
    }
    .history-view-all-btn:hover {
        background: #f0fdf4;
        border-color: #0d9488;
    }

    @media (max-width: 480px) {
        .chat-widget-drawer {
            width: 100%;
        }
        .chat-widget-trigger {
            bottom: 16px;
            right: 16px;
        }
    }
</style>

<!-- Botón Flotante (Robotito) -->
<button id="chatWidgetTrigger" class="chat-widget-trigger" title="Chat de Asistente Virtual">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zM5 8h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2zM9 13h.01M15 13h.01M8 17h8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="chat-widget-trigger-pulse"></span>
</button>

<!-- Backdrop del Widget -->
<div id="chatWidgetBackdrop" class="chat-widget-backdrop"></div>

<!-- Drawer / Panel Lateral del Chat -->
<div id="chatWidgetDrawer" class="chat-widget-drawer">
    <!-- Cabecera -->
    <div class="chat-widget-header">
        <div class="chat-widget-header-title">
            <span class="chat-widget-status-dot"></span>
            <div class="chat-widget-header-info">
                <h4>Farmabot</h4>
                <p>Asistente de Farmacia</p>
            </div>
        </div>
        <div class="chat-widget-header-actions">
            <!-- Historial de chats -->
            <button id="chatWidgetHistoryBtn" class="chat-widget-header-btn" title="Historial de conversaciones">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
            <!-- Reiniciar Conversación -->
            <button id="chatWidgetReset" class="chat-widget-header-btn" title="Nueva conversación">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                </svg>
            </button>
            <!-- Cerrar Chat -->
            <button id="chatWidgetClose" class="chat-widget-header-btn close-btn" title="Cerrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Panel de Historial de Conversaciones -->
    <div id="chatWidgetHistoryPanel" class="chat-widget-history-panel">
        <div class="history-panel-header">
            <h5>Conversaciones Recientes</h5>
            <button id="closeHistoryPanel" class="close-history-btn" title="Cerrar historial">&times;</button>
        </div>
        <div id="historyPanelList" class="history-panel-list"></div>
    </div>

    <!-- Advertencia Médica (tiendafarma-chat) -->
    <div class="chat-widget-disclaimer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <p><strong>Nota importante:</strong> No soy un médico. Sugiero orientaciones de venta libre y urgencias para nuestra tienda. Ante dudas, consulta a un especialista médico.</p>
    </div>

    <!-- Contenedor de Mensajes -->
    <div id="chatWidgetMessages" class="chat-widget-messages"></div>

    <!-- Indicador de Escritura -->
    <div id="chatWidgetTyping" class="chat-widget-typing">
        <div class="chat-widget-avatar assistant">🤖</div>
        <div class="chat-widget-typing-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

    <!-- Área de Entrada de Texto -->
    <div class="chat-widget-input-area">
        <textarea id="chatWidgetInput" rows="1" placeholder="Describe tus síntomas o haz tu consulta..." maxlength="1000"></textarea>
        <button id="chatWidgetSend" class="chat-widget-send-btn" title="Enviar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
            </svg>
        </button>
    </div>
</div>

<script>
(function() {
    const trigger = document.getElementById('chatWidgetTrigger');
    const backdrop = document.getElementById('chatWidgetBackdrop');
    const drawer = document.getElementById('chatWidgetDrawer');
    const closeBtn = document.getElementById('chatWidgetClose');
    const resetBtn = document.getElementById('chatWidgetReset');
    const historyBtn = document.getElementById('chatWidgetHistoryBtn');
    const historyPanel = document.getElementById('chatWidgetHistoryPanel');
    const closeHistoryBtn = document.getElementById('closeHistoryPanel');
    const historyPanelList = document.getElementById('historyPanelList');
    const messagesContainer = document.getElementById('chatWidgetMessages');
    const input = document.getElementById('chatWidgetInput');
    const sendBtn = document.getElementById('chatWidgetSend');
    const typingIndicator = document.getElementById('chatWidgetTyping');

    let isOpen = false;
    let isHistoryLoaded = false;
    let isProcessing = false;
    let currentAiBubble = null;

    // Obtener o generar huella digital (UUID)
    let fingerprint = localStorage.getItem('chat_device_fingerprint');
    if (!fingerprint) {
        fingerprint = 'f_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        localStorage.setItem('chat_device_fingerprint', fingerprint);
    }

    // Identidad del cliente logueado
    const customerName = '@auth("tienda"){{ auth("tienda")->user()->nombre }}@endauth';

    // Configuración de rutas
    const historyRoute = '{{ route("tienda.chat.history") }}';
    const sendRoute = '{{ route("tienda.chat.send") }}';
    const resetRoute = '{{ route("tienda.chat.reset") }}';
    const productJsonBaseRoute = '/tienda/productos/json';

    // Abrir/Cerrar
    function toggleChat(forceState) {
        isOpen = (typeof forceState === 'boolean') ? forceState : !isOpen;
        if (isOpen) {
            drawer.classList.add('active');
            backdrop.classList.add('active');
            input.focus();
            if (!isHistoryLoaded) {
                loadHistory();
            }
        } else {
            drawer.classList.remove('active');
            backdrop.classList.remove('active');
        }
    }

    trigger.addEventListener('click', () => toggleChat(true));
    backdrop.addEventListener('click', () => toggleChat(false));
    closeBtn.addEventListener('click', () => toggleChat(false));

    // Permitir que elementos globales con la clase '.open-chat-widget' abran el chat
    document.addEventListener('click', function(e) {
        if (e.target.closest('.open-chat-widget')) {
            e.preventDefault();
            toggleChat(true);
        }
    });

    // Auto-ajustar alto del textarea
    function autoResize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 80) + 'px';
    }
    input.addEventListener('input', autoResize);

    // Tecla Enter para enviar
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);
    resetBtn.addEventListener('click', resetChat);
    historyBtn.addEventListener('click', toggleHistoryPanel);
    closeHistoryBtn.addEventListener('click', () => historyPanel.classList.remove('active'));

    // Cargar historial
    async function loadHistory() {
        showTyping();
        try {
            setProcessing(false); // Desbloquear input al recargar
            const response = await fetch(historyRoute, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Device-Fingerprint': fingerprint
                }
            });
            const data = await response.json();
            
            messagesContainer.innerHTML = '';
            
            if (!data.history || data.history.length === 0) {
                renderWelcomeMessage();
            } else {
                for (const msg of data.history) {
                    renderMessage(msg.role, msg.content);
                }
            }
            isHistoryLoaded = true;
        } catch (e) {
            console.error('Error al cargar historial:', e);
            renderWelcomeMessage();
        } finally {
            hideTyping();
            scrollToBottom();
        }
    }

    function renderWelcomeMessage() {
        const greetingName = customerName ? `, ${customerName}` : '';
        const welcomeText = `**Hola${greetingName}, soy Farmabot, tu Asistente Virtual.**\n\nAntes de comenzar, recuerda que **NO soy un médico**. Mi función es orientarte sobre síntomas leves y recomendarte medicamentos de venta libre disponibles en nuestra tienda.\n\n*Esta información es orientativa y para emergencias menores. Lo recomendable siempre es consultar con un médico.*`;
        renderMessage('assistant', welcomeText);
    }

    // Renderizar un mensaje en pantalla
    function renderMessage(role, text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-widget-message ${role}`;

        const avatar = document.createElement('div');
        avatar.className = `chat-widget-avatar ${role}`;
        avatar.innerHTML = role === 'assistant' ? '🤖' : '👤';

        const bubble = document.createElement('div');
        bubble.className = `chat-widget-bubble ${role}-bubble`;
        bubble.innerHTML = formatText(text);

        messageDiv.appendChild(avatar);
        messageDiv.appendChild(bubble);
        messagesContainer.appendChild(messageDiv);
        
        // Cargar tarjetas de producto si el mensaje es del asistente
        if (role === 'assistant') {
            loadProductCards(bubble);
        }
        
        scrollToBottom();
        return bubble;
    }

    // Convertir markdown básico e interceptar sintaxis de producto
    function formatText(text) {
        let html = text
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');

        // Reemplazar sintaxis [Nombre](product:ID) por un placeholder temporal
        html = html.replace(/\[([^\]]+)\]\(product:(\d+)\)/g, function(match, name, id) {
            return `<div class="product-card-placeholder" data-id="${id}">Cargando recomendado...</div>`;
        });

        return html;
    }

    // Buscar los placeholders de producto y cargar sus datos vía AJAX
    function loadProductCards(container) {
        const placeholders = container.querySelectorAll('.product-card-placeholder');
        placeholders.forEach(async (placeholder) => {
            const id = placeholder.dataset.id;
            try {
                const response = await fetch(`${productJsonBaseRoute}/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Status ' + response.status);
                
                const product = await response.json();
                
                // Reemplazar placeholder por tarjeta HTML
                placeholder.outerHTML = `
                    <div class="chat-product-card">
                        <div class="chat-product-img">
                            <img src="${product.imagen_url || '/vendor/adminlte/dist/img/avatar.png'}" alt="${product.nombre}">
                        </div>
                        <div class="chat-product-info">
                            <span class="chat-product-tag ${product.receta ? 'receta' : 'libre'}">
                                ${product.receta ? 'Receta Médica' : 'Venta Libre'}
                            </span>
                            <h5 class="chat-product-title">${product.nombre}</h5>
                            <span class="chat-product-meta">Sucursal: ${product.sucursal}</span>
                            <div class="chat-product-footer">
                                <span class="chat-product-price">S/. ${parseFloat(product.precio).toFixed(2)}</span>
                                <button class="chat-product-btn" onclick="agregarAlCarritoDesdeChat(${product.id})">Agregar</button>
                            </div>
                        </div>
                    </div>
                `;
            } catch (e) {
                console.error(`Error cargando producto ${id}:`, e);
                placeholder.outerHTML = `<div style="font-size:0.75rem;color:#dc2626;margin-top:4px;">(Producto no disponible)</div>`;
            }
            scrollToBottom();
        });
    }

    // Enviar mensaje
    async function sendMessage() {
        const text = input.value.trim();
        if (!text || isProcessing) return;

        // Renderizar mensaje de usuario
        renderMessage('user', text);
        
        input.value = '';
        autoResize();
        setProcessing(true);
        showTyping();

        // Crear burbuja de respuesta del asistente vacía
        const bubble = renderMessage('assistant', '');
        currentAiBubble = bubble;

        try {
            const response = await fetch(sendRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Device-Fingerprint': fingerprint
                },
                body: JSON.stringify({ message: text })
            });

            if (!response.ok) {
                if (response.status === 429) {
                    const errData = await response.json();
                    hideTyping();
                    bubble.innerHTML = `<em style="color:#ef4444;font-style:normal;font-weight:600;">⚠️ ${errData.message || 'Límite alcanzado.'}</em>`;
                    setProcessing(true); // Bloquear el input
                    input.disabled = true;
                    return;
                }
                throw new Error('HTTP ' + response.status);
            }

            hideTyping();

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            let rawResponseText = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() || '';

                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;
                    
                    try {
                        const data = JSON.parse(line.substring(6));
                        if (data.content) {
                            rawResponseText += data.content;
                            bubble.innerHTML = formatText(rawResponseText);
                            scrollToBottom();
                        }
                        if (data.done) break;
                    } catch (e) {
                        // Saltar líneas JSON mal formadas
                    }
                }
            }

            // Realizar el renderizado final para gatillar tarjetas de productos
            bubble.innerHTML = formatText(rawResponseText);
            loadProductCards(bubble);

            if (!rawResponseText.trim()) {
                bubble.innerHTML = '<em>No se obtuvo respuesta. Intenta nuevamente.</em>';
            }

        } catch (err) {
            hideTyping();
            bubble.innerHTML = '<em style="color:#dc2626;">Error de conexión. Intenta nuevamente.</em>';
            console.error('Chat error:', err);
        } finally {
            currentAiBubble = null;
            setProcessing(false);
            scrollToBottom();
        }
    }

    // Resetear conversación (Sin confirmación molesta)
    async function resetChat() {
        if (isProcessing) return;
        historyPanel.classList.remove('active');

        showTyping();
        try {
            setProcessing(false); // Desbloquear el input
            await fetch(resetRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Device-Fingerprint': fingerprint
                }
            });
            messagesContainer.innerHTML = '';
            renderWelcomeMessage();
        } catch (e) {
            console.error('Reset error:', e);
        } finally {
            hideTyping();
            scrollToBottom();
        }
    }

    // Toggle del Panel de Historial de Chats
    async function toggleHistoryPanel() {
        const isActive = historyPanel.classList.toggle('active');
        if (isActive) {
            historyPanelList.innerHTML = '<div style="font-size:0.8rem;color:#64748b;text-align:center;padding:20px;">Cargando historial...</div>';
            try {
                const response = await fetch('{{ route("tienda.chat.conversaciones") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Device-Fingerprint': fingerprint
                    }
                });
                const data = await response.json();
                renderConversationsList(data.conversaciones);
            } catch(e) {
                console.error(e);
                historyPanelList.innerHTML = '<div style="font-size:0.8rem;color:#ef4444;text-align:center;padding:20px;">Error al cargar conversaciones.</div>';
            }
        }
    }

    // Renderizar la lista de conversaciones (Max 5 por defecto, con botón de "Ver todos")
    function renderConversationsList(conversaciones) {
        historyPanelList.innerHTML = '';
        if (!conversaciones || conversaciones.length === 0) {
            historyPanelList.innerHTML = '<div style="font-size:0.8rem;color:#64748b;text-align:center;padding:20px;">No tienes conversaciones previas.</div>';
            return;
        }

        const limit = 5;
        let showAll = false;

        function renderItems() {
            historyPanelList.innerHTML = '';
            const itemsToRender = showAll ? conversaciones : conversaciones.slice(0, limit);
            
            itemsToRender.forEach(c => {
                const div = document.createElement('div');
                div.className = `history-item${c.is_active ? ' active' : ''}`;
                div.innerHTML = `
                    <span class="history-item-preview">${escapeHtml(c.preview)}</span>
                    <span class="history-item-date">${c.date}</span>
                `;
                div.addEventListener('click', () => selectConversation(c.id));
                historyPanelList.appendChild(div);
            });

            if (!showAll && conversaciones.length > limit) {
                const viewAllBtn = document.createElement('button');
                viewAllBtn.className = 'history-view-all-btn';
                viewAllBtn.innerText = 'Ver todos los chats';
                viewAllBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showAll = true;
                    renderItems();
                });
                historyPanelList.appendChild(viewAllBtn);
            }
        }

        renderItems();
    }

    // Cambiar a una conversación seleccionada
    async function selectConversation(id) {
        showTyping();
        historyPanel.classList.remove('active');
        try {
            await fetch(`/tienda/chat/conversaciones/${id}/active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Device-Fingerprint': fingerprint
                }
            });
            isHistoryLoaded = false;
            await loadHistory();
        } catch (e) {
            console.error('Error al cambiar de conversación:', e);
        } finally {
            hideTyping();
        }
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Helpers
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function showTyping() {
        typingIndicator.classList.add('visible');
        scrollToBottom();
    }

    function hideTyping() {
        typingIndicator.classList.remove('visible');
    }

    function setProcessing(state) {
        isProcessing = state;
        sendBtn.disabled = state;
        input.disabled = state;
    }

    // Abrir automáticamente si viene el parámetro open_chat en la URL
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('open_chat')) {
            toggleChat(true);
            // Limpiar parámetro de la URL
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });

})();

// Función global para agregar al carrito de manera robusta
function agregarAlCarritoDesdeChat(productoId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/tienda/carrito/${productoId}`;
    
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (token) {
        const inputToken = document.createElement('input');
        inputToken.type = 'hidden';
        inputToken.name = '_token';
        inputToken.value = token;
        form.appendChild(inputToken);
    }
    
    document.body.appendChild(form);
    form.submit();
}
</script>
