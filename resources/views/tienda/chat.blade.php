@extends('tienda.layout')

@section('title', 'Chat Asistente')

@push('styles')
<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 220px);
        min-height: 500px;
        background: white;
        border-radius: 1.25rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.03), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
        border: 1px solid rgba(226, 232, 240, 0.7);
        overflow: hidden;
    }

    .chat-disclaimer {
        background: linear-gradient(135deg, #fff7ed 0%, #fff1f2 100%);
        border-bottom: 1px solid rgba(251, 191, 36, 0.2);
        padding: 0.85rem 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    .chat-disclaimer-icon {
        flex-shrink: 0;
        width: 1.3rem;
        height: 1.3rem;
        margin-top: 1px;
    }
    .chat-disclaimer-text {
        font-size: 0.8rem;
        color: #9a3412;
        font-weight: 600;
        line-height: 1.5;
    }
    .chat-disclaimer-text strong {
        color: #c2410c;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        background: #f8fafc;
        scroll-behavior: smooth;
    }

    .chat-message {
        display: flex;
        gap: 0.65rem;
        max-width: 85%;
        animation: chatSlideIn 0.3s ease;
    }
    @keyframes chatSlideIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .chat-message.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .chat-message.assistant {
        align-self: flex-start;
    }

    .chat-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9rem;
    }
    .chat-avatar.user-avatar {
        background: var(--store-green);
        color: white;
    }
    .chat-avatar.assistant-avatar {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: white;
    }

    .chat-bubble {
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        font-size: 0.9rem;
        line-height: 1.6;
        word-wrap: break-word;
    }
    .chat-bubble.user-bubble {
        background: var(--store-green);
        color: white;
        border-bottom-right-radius: 0.35rem;
    }
    .chat-bubble.assistant-bubble {
        background: white;
        color: var(--store-ink);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-bottom-left-radius: 0.35rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .chat-typing {
        align-self: flex-start;
        display: none;
        gap: 0.65rem;
        align-items: center;
        padding: 0.5rem 0;
        animation: chatSlideIn 0.2s ease;
    }
    .chat-typing.visible { display: flex; }

    .typing-dots {
        display: flex;
        gap: 4px;
        align-items: center;
        padding: 0.75rem 1rem;
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1rem;
        border-bottom-left-radius: 0.35rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .typing-dots span {
        width: 7px;
        height: 7px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out;
    }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-6px); }
    }

    .chat-input-area {
        padding: 1rem 1.25rem;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
        background: white;
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
        flex-shrink: 0;
    }
    .chat-input {
        flex: 1;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1rem;
        padding: 0.7rem 1rem;
        font-size: 0.9rem;
        resize: none;
        outline: none;
        font-family: inherit;
        line-height: 1.5;
        max-height: 120px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .chat-input:focus {
        border-color: var(--store-green);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }
    .chat-send-btn {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 0;
        background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(13, 148, 136, 0.15);
    }
    .chat-send-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(13, 148, 136, 0.25);
    }
    .chat-send-btn:active {
        transform: scale(0.95);
    }
    .chat-send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .chat-send-btn svg {
        width: 1.1rem;
        height: 1.1rem;
    }

    .chat-reset-btn {
        background: transparent;
        border: 0;
        color: var(--store-muted);
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }
    .chat-reset-btn:hover {
        color: var(--store-green-dark);
        background: var(--store-green-soft);
    }

    .chat-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 1.25rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        background: white;
        flex-shrink: 0;
    }
    .chat-header-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--store-ink);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .chat-header-status {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: statusPulse 2s infinite;
    }
    @keyframes statusPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    @media (max-width: 767.98px) {
        .chat-container {
            height: calc(100vh - 160px);
            border-radius: 0;
            margin: 0 -12px;
        }
        .chat-message {
            max-width: 92%;
        }
        .chat-avatar {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
        .chat-bubble {
            font-size: 0.85rem;
            padding: 0.6rem 0.85rem;
        }
        .chat-disclaimer-text {
            font-size: 0.72rem;
        }
    }
</style>
@endpush

@section('content')
<div class="mb-3">
    <h2 class="fw-bold" style="color: var(--store-ink); font-size: 1.4rem;">Chat Asistente Farmaceutico</h2>
    <p class="muted-copy mb-0" style="font-size: 0.9rem;">Consulta tus sintomas y recibe orientacion</p>
</div>

<div class="chat-container" id="chatContainer">
    <div class="chat-header-bar">
        <div class="chat-header-title">
            <span class="chat-header-status"></span>
            Asistente Virtual en linea
        </div>
        <button class="chat-reset-btn" id="btnResetChat" title="Nueva conversacion">
            <svg style="width:1rem;height:1rem;margin-right:2px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
            </svg>
            Nueva conversacion
        </button>
    </div>

    <div class="chat-disclaimer">
        <svg class="chat-disclaimer-icon" fill="none" stroke="#c2410c" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <div class="chat-disclaimer-text">
            <strong>Importante:</strong> Este asistente <strong>NO es un medico</strong> y no reemplaza una consulta profesional. La informacion proporcionada es solo orientativa para casos de emergencia. Ante cualquier duda, <strong>consulta con un especialista.</strong>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        <div class="chat-message assistant" id="welcomeMessage">
            <div class="chat-avatar assistant-avatar">
                <svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                </svg>
            </div>
            <div class="chat-bubble assistant-bubble" id="welcomeBubble">
                <strong>Hola, soy el Asistente Virtual de la Farmacia.</strong><br><br>
                Antes de comenzar, quiero que sepas que <strong>NO soy un medico</strong>. Mi funcion es brindarte orientacion basica sobre sintomas comunes y recomendarte productos disponibles en nuestra tienda.<br><br>
                <em>Esta informacion es solo para emergencias o malestares puntuales. Lo mejor siempre es consultar con un profesional de la salud.</em><br><br>
                ¿En que puedo ayudarte?
            </div>
        </div>
    </div>

    <div class="chat-typing" id="chatTyping">
        <div class="chat-avatar assistant-avatar">
            <svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
            </svg>
        </div>
        <div class="typing-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

    <div class="chat-input-area">
        <textarea
            class="chat-input"
            id="chatInput"
            rows="1"
            placeholder="Describe tus sintomas o haz tu consulta..."
            maxlength="2000"
        ></textarea>
        <button class="chat-send-btn" id="btnSend" title="Enviar mensaje">
            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
            </svg>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const btnSend = document.getElementById('btnSend');
    const chatTyping = document.getElementById('chatTyping');
    const btnReset = document.getElementById('btnResetChat');
    const welcomeMessage = document.getElementById('welcomeMessage');
    const welcomeBubble = document.getElementById('welcomeBubble');

    let isProcessing = false;
    let currentAssistantBubble = null;

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function autoResizeTextarea() {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
    }

    function createMessageElement(role) {
        const div = document.createElement('div');
        div.className = 'chat-message ' + role;

        const avatar = document.createElement('div');
        avatar.className = 'chat-avatar ' + role + '-avatar';

        if (role === 'assistant') {
            avatar.innerHTML = '<svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>';
        } else {
            avatar.innerHTML = '<svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>';
        }

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + role + '-bubble';

        div.appendChild(avatar);
        div.appendChild(bubble);

        return { messageEl: div, bubbleEl: bubble };
    }

    function sanitizeText(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatBubbleText(text) {
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
        text = text.replace(/\[REQUIERE RECETA\]/g, '<span style="color:#dc2626;font-weight:700;">[REQUIERE RECETA]</span>');
        text = text.replace(/\[VENTA LIBRE\]/g, '<span style="color:#16a34a;font-weight:700;">[VENTA LIBRE]</span>');
        text = text.replace(/\n/g, '<br>');
        return text;
    }

    function showTyping() {
        chatTyping.classList.add('visible');
        scrollToBottom();
    }

    function hideTyping() {
        chatTyping.classList.remove('visible');
    }

    function setProcessing(state) {
        isProcessing = state;
        btnSend.disabled = state;
        chatInput.disabled = state;
        if (state) {
            btnSend.style.opacity = '0.5';
            btnSend.style.cursor = 'not-allowed';
        } else {
            btnSend.style.opacity = '';
            btnSend.style.cursor = '';
        }
    }

    function removeWelcomeMessage() {
        if (welcomeMessage) {
            welcomeMessage.remove();
        }
    }

    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text || isProcessing) return;

        removeWelcomeMessage();

        const { messageEl, bubbleEl } = createMessageElement('user');
        bubbleEl.innerHTML = formatBubbleText(text);
        chatMessages.appendChild(messageEl);
        scrollToBottom();

        chatInput.value = '';
        autoResizeTextarea();
        setProcessing(true);
        showTyping();

        const { messageEl: aiMessageEl, bubbleEl: aiBubbleEl } = createMessageElement('assistant');
        aiBubbleEl.innerHTML = '';
        chatMessages.appendChild(aiMessageEl);
        currentAssistantBubble = aiBubbleEl;
        hideTyping();

        try {
            const response = await fetch('{{ route("tienda.chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: text })
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

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
                            aiBubbleEl.innerHTML = formatBubbleText(
                                (aiBubbleEl.textContent || '') + data.content
                            );
                            scrollToBottom();
                        }
                        if (data.done) break;
                    } catch (e) {
                        // skip malformed JSON lines
                    }
                }
            }

            if (!aiBubbleEl.textContent || aiBubbleEl.textContent.trim() === '') {
                aiBubbleEl.innerHTML = '<em>No se obtuvo respuesta. Intenta de nuevo.</em>';
            }

        } catch (err) {
            aiBubbleEl.innerHTML = '<em style="color:#dc2626;">Error de conexion. Verifica que el servicio este configurado correctamente.</em>';
            console.error('Chat error:', err);
        } finally {
            currentAssistantBubble = null;
            setProcessing(false);
            scrollToBottom();
        }
    }

    async function resetChat() {
        if (isProcessing) return;

        try {
            await fetch('{{ route("tienda.chat.reset") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        } catch (e) {
            console.error('Reset error:', e);
        }

        chatMessages.innerHTML = '';
        if (welcomeMessage) {
            chatMessages.appendChild(welcomeMessage);
        } else {
            const originalWelcome = document.querySelector('#welcomeMessage');
            if (originalWelcome) {
                chatMessages.appendChild(originalWelcome);
            }
        }
        scrollToBottom();
    }

    chatInput.addEventListener('input', autoResizeTextarea);

    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    btnSend.addEventListener('click', sendMessage);

    btnReset.addEventListener('click', function () {
        if (confirm('¿Iniciar una nueva conversacion? Se perdera el historial actual.')) {
            resetChat();
        }
    });

    scrollToBottom();
})();
</script>
@endpush
