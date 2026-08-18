<!-- FarmaCopiloto Chat Widget (tiendafarma-ux) -->
<div class="farmacopiloto-widget" id="copilotoWidget">
    
    <!-- Botón Flotante Redondo -->
    <button class="copiloto-btn shadow-lg" id="btnToggleCopiloto" title="FarmaCopiloto - Asistente Clínico">
        <i class="fas fa-user-md copiloto-icon-main"></i>
        <span class="copiloto-badge-pulse"></span>
    </button>

    <!-- Ventana del Chat -->
    <div class="copiloto-chat-window card border-0" id="copilotoChatWindow">
        
        <!-- Cabecera del Chat -->
        <div class="copiloto-chat-header d-flex justify-content-between align-items-center text-white">
            <div class="d-flex align-items-center">
                <div class="copiloto-avatar-header mr-2">
                    <i class="fas fa-user-md"></i>
                </div>
                <div>
                    <div class="font-weight-bold d-flex align-items-center" style="font-size: 0.95rem; line-height: 1.2;">
                        FarmaCopiloto
                        <span class="badge badge-light ml-2" id="copilotoCounter" style="font-size: 0.65rem; color: #10ac84; padding: 2px 6px; border-radius: 10px; transition: all 0.3s ease;">0/30</span>
                    </div>
                    <small class="text-white-50"><i class="fas fa-circle text-success mr-1" style="font-size: 0.55rem;"></i> En línea - Sucursal</small>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-sm text-white-50 p-1 mr-2 hover-white-100" id="btnHistoryCopiloto" title="Historial de conversaciones">
                    <i class="fas fa-history"></i>
                </button>
                <button class="btn btn-sm text-white-50 p-1 mr-2 hover-white-100" id="btnResetCopiloto" title="Reiniciar Conversación">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="btn btn-sm text-white-50 p-1 hover-white-100" id="btnCloseCopiloto" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Advertencia Médica (tiendafarma-chat) -->
        <div class="copiloto-chat-disclaimer">
            <i class="fas fa-exclamation-triangle"></i>
            <span><strong>Aviso:</strong> Apoyo clínico/stock. No reemplaza diagnóstico médico ni recetas oficiales.</span>
        </div>

        <!-- Panel de Historial de Conversaciones -->
        <div id="copilotoHistoryPanel" class="copiloto-chat-history-panel">
            <div class="copiloto-history-header">
                <h5>Conversaciones Recientes</h5>
                <button id="btnCloseHistoryCopiloto" class="copiloto-close-history-btn" title="Cerrar historial">&times;</button>
            </div>
            <div id="copilotoHistoryList" class="copiloto-history-list"></div>
        </div>

        <!-- Cuerpo del Chat (Mensajes) -->
        <div class="copiloto-chat-body" id="copilotoChatMessages">
            <div class="copiloto-message assistant welcome">
                <div class="copiloto-bubble">
                    <strong>¡Hola! Soy FarmaCopiloto.</strong><br>
                    Tu asistente de soporte clínico y stock. ¿En qué medicamento o consulta te puedo ayudar hoy?
                </div>
            </div>
        </div>

        <!-- Indicador de Escribiendo (Typing Loader) -->
        <div class="copiloto-typing d-none" id="copilotoTyping">
            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
        </div>

        <!-- Vista previa de imagen seleccionada -->
        <div id="copilotoImagePreviewContainer" class="d-none px-3 py-2 bg-light border-top align-items-center" style="gap: 10px;">
            <div class="position-relative" style="width: 50px; height: 50px;">
                <img id="copilotoImagePreviewImg" src="" class="rounded" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #cbd5e1;">
                <button type="button" id="btnRemoveCopilotoImage" class="btn btn-danger btn-xs position-absolute d-flex align-items-center justify-content-center shadow-sm" style="top: -6px; right: -6px; border-radius: 50%; width: 18px; height: 18px; padding: 0; font-size: 10px; line-height: 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <span class="text-muted small text-truncate" id="copilotoImagePreviewName">Imagen seleccionada</span>
        </div>

        <!-- Área de Entrada de Texto -->
        <div class="copiloto-chat-input d-flex align-items-center">
            <button type="button" class="btn btn-link text-muted p-1 mr-1" id="btnVoiceCopiloto" title="Dictar por voz">
                <i class="fas fa-microphone" style="font-size: 0.95rem;"></i>
            </button>
            <!-- Botón de imagen -->
            <button type="button" class="btn btn-link text-muted p-1 mr-2" id="btnAttachCopiloto" title="Adjuntar imagen">
                <i class="fas fa-image" style="font-size: 0.95rem;"></i>
            </button>
            <input type="file" id="fileCopilotoImage" accept="image/*" style="display: none;">

            <textarea id="copilotoInput" rows="1" placeholder="Ej: Stock de Paracetamol, interacciones de..." maxlength="2000"></textarea>
            
            <button class="btn btn-success rounded-circle ml-2 p-0 d-flex align-items-center justify-content-center" id="btnSendCopiloto" disabled style="min-width: 32px; min-height: 32px;">
                <i class="fas fa-paper-plane" style="font-size: 0.85rem;"></i>
            </button>
        </div>

    </div>
</div>

<style>
    /* ============================================================ */
    /* ESTILOS WIDGET FARMACOPILOTO                                 */
    /* ============================================================ */
    .farmacopiloto-widget {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 1050;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Botón Flotante */
    .copiloto-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10ac84 0%, #00d2d3 100%);
        border: none;
        color: white;
        font-size: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        outline: none !important;
    }

    .copiloto-btn:hover {
        transform: scale(1.08) rotate(5deg);
        box-shadow: 0 8px 25px rgba(16, 172, 132, 0.45) !important;
    }

    .copiloto-badge-pulse {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        background-color: #28a745;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 rgba(40, 167, 69, 0.4);
        animation: copilotoPulse 1.8s infinite;
    }

    @keyframes copilotoPulse {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }

    /* Ventana del Chat */
    .copiloto-chat-window {
        position: absolute;
        bottom: 75px;
        right: 0;
        width: 370px;
        height: 480px;
        border-radius: 16px !important;
        background-color: #ffffff;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        display: none; /* Flex al abrir */
        flex-direction: column;
        overflow: hidden;
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
    }

    .copiloto-chat-window.show {
        display: flex;
        opacity: 1;
        transform: translateY(0);
    }

    /* Cabecera */
    .copiloto-chat-header {
        background: linear-gradient(135deg, #10ac84 0%, #00d2d3 100%);
        padding: 12px 15px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .copiloto-avatar-header {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .hover-white-100:hover {
        color: #ffffff !important;
        opacity: 1;
    }

    /* Cuerpo de Mensajes */
    .copiloto-chat-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background-color: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .copiloto-message {
        display: flex;
        max-width: 85%;
    }

    .copiloto-message.user {
        align-self: flex-end;
    }

    .copiloto-message.assistant {
        align-self: flex-start;
    }

    .copiloto-bubble {
        padding: 10px 14px;
        border-radius: 14px;
        font-size: 0.85rem;
        line-height: 1.4;
        white-space: pre-wrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .copiloto-message.user .copiloto-bubble {
        background-color: #20c997;
        color: #ffffff;
        border-bottom-right-radius: 2px;
    }

    .copiloto-message.assistant .copiloto-bubble {
        background-color: #ffffff;
        color: #2d3748;
        border-bottom-left-radius: 2px;
        border: 1px solid #e2e8f0;
    }

    /* Typing Dots */
    .copiloto-typing {
        align-self: flex-start;
        margin-left: 20px;
        margin-bottom: 12px;
        padding: 8px 12px;
        background-color: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: flex;
        gap: 4px;
    }

    .copiloto-typing .dot {
        width: 6px;
        height: 6px;
        background-color: #a0aec0;
        border-radius: 50%;
        animation: copilotoBounce 1.4s infinite ease-in-out both;
    }

    .copiloto-typing .dot:nth-child(1) { animation-delay: -0.32s; }
    .copiloto-typing .dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes copilotoBounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1.0); }
    }

    /* Input Area */
    .copiloto-chat-input {
        padding: 10px 15px;
        background-color: #ffffff;
        border-top: 1px solid #e2e8f0;
    }

    .copiloto-chat-input textarea {
        flex: 1;
        border: none;
        outline: none;
        resize: none;
        font-size: 0.85rem;
        color: #2d3748;
        max-height: 80px;
        padding: 5px 0;
    }

    .copiloto-chat-input textarea::placeholder {
        color: #a0aec0;
    }

    #btnSendCopiloto {
        width: 32px;
        height: 32px;
        border: none;
        transition: all 0.2s;
    }

    #btnSendCopiloto:disabled {
        background-color: #e2e8f0 !important;
        color: #a0aec0 !important;
        cursor: not-allowed;
    }

    /* ==========================================
       MODO OSCURO COMPATIBILIDAD (AdminLTE)
       ========================================== */
    .dark-mode .copiloto-chat-window {
        background-color: #343a40;
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    }

    .dark-mode .copiloto-chat-body {
        background-color: #2b3035;
    }

    .dark-mode .copiloto-message.assistant .copiloto-bubble {
        background-color: #3e444a;
        color: #f8f9fa;
        border-color: #4f565e;
    }

    .dark-mode .copiloto-chat-input {
        background-color: #343a40;
        border-top-color: #4f565e;
    }

    .dark-mode .copiloto-chat-input textarea {
        background-color: transparent;
        color: #f8f9fa;
    }

    .dark-mode .copiloto-typing {
        background-color: #3e444a;
        border-color: #4f565e;
    }

    /* Panel de Historial */
    .copiloto-chat-history-panel {
        position: absolute;
        top: 60px; /* Justo debajo de la cabecera */
        left: 0;
        width: 100%;
        height: calc(100% - 60px);
        background: white;
        z-index: 15;
        display: flex;
        flex-direction: column;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease-in-out;
        border-top: 1px solid #e2e8f0;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }
    .copiloto-chat-history-panel.active {
        opacity: 1;
        pointer-events: auto;
    }
    .copiloto-history-header {
        padding: 12px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .copiloto-history-header h5 {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
    }
    .copiloto-close-history-btn {
        background: transparent;
        border: none;
        font-size: 1.3rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        outline: none;
    }
    .copiloto-close-history-btn:hover {
        color: #ef4444;
    }
    .copiloto-history-list {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .copiloto-history-item {
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .copiloto-history-item:hover {
        background: #f0fdf4;
        border-color: #10ac84;
        transform: translateY(-1px);
    }
    .copiloto-history-item.active {
        background: #f0fdf4;
        border-color: #10ac84;
        border-left: 4px solid #10ac84;
    }
    .copiloto-history-item-preview {
        font-size: 0.78rem;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }
    .copiloto-history-item-date {
        font-size: 0.68rem;
        color: #94a3b8;
        text-align: left;
    }
    .copiloto-history-view-all-btn {
        width: 100%;
        padding: 8px;
        background: transparent;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #10ac84;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
        margin-top: 4px;
        outline: none;
    }
    .copiloto-history-view-all-btn:hover {
        background: #f0fdf4;
        border-color: #10ac84;
    }

    /* Advertencia Médica */
    .copiloto-chat-disclaimer {
        padding: 6px 12px;
        background: #fff9db;
        border-bottom: 1px solid #ffe3e3;
        font-size: 0.7rem;
        color: #d9480f;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .copiloto-chat-disclaimer i {
        font-size: 0.75rem;
    }

    /* Estilos Dark Mode */
    .dark-mode .copiloto-chat-history-panel {
        background: #2b3035;
        border-top-color: #4f565e;
    }
    .dark-mode .copiloto-history-header {
        background: #212529;
        border-bottom-color: #4f565e;
    }
    .dark-mode .copiloto-history-header h5 {
        color: #f8f9fa;
    }
    .dark-mode .copiloto-history-item {
        background: #3e444a;
        border-color: #4f565e;
    }
    .dark-mode .copiloto-history-item:hover {
        background: #233e38;
        border-color: #10ac84;
    }
    .dark-mode .copiloto-history-item.active {
        background: #233e38;
        border-color: #10ac84;
    }
    .dark-mode .copiloto-history-item-preview {
        color: #f8f9fa;
    }
    .dark-mode .copiloto-history-item-date {
        color: #a8b2bd;
    }
    .dark-mode .copiloto-history-view-all-btn {
        border-color: #4f565e;
        color: #00d2d3;
    }
    .dark-mode .copiloto-history-view-all-btn:hover {
        background: #233e38;
        border-color: #10ac84;
    }
    .dark-mode .copiloto-chat-disclaimer {
        background: #3e382b;
        color: #ffd8a8;
        border-bottom-color: #4f565e;
    }

    /* Estilos para el botón de voz escuchando */
    #btnVoiceCopiloto.listening {
        color: #ef4444 !important;
        animation: copilotoVoicePulse 1.2s infinite;
    }
    @keyframes copilotoVoicePulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.25); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
    /* Estilos para botones de acción secundarios */
    .copiloto-chat-input .btn-link {
        text-decoration: none;
        box-shadow: none;
        outline: none;
        transition: color 0.2s ease;
    }
    .copiloto-chat-input .btn-link:hover {
        color: #10ac84 !important;
    }
    #copilotoImagePreviewContainer {
        border-top: 1px solid #e2e8f0;
    }
    .dark-mode #copilotoImagePreviewContainer {
        background-color: #2b3035 !important;
        border-top-color: #4f565e !important;
    }
    .dark-mode #copilotoImagePreviewName {
        color: #a8b2bd !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const copilotoBtn = document.getElementById('btnToggleCopiloto');
    const copilotoWindow = document.getElementById('copilotoChatWindow');
    const copilotoClose = document.getElementById('btnCloseCopiloto');
    const copilotoReset = document.getElementById('btnResetCopiloto');
    const copilotoMessages = document.getElementById('copilotoChatMessages');
    const copilotoInput = document.getElementById('copilotoInput');
    const btnSend = document.getElementById('btnSendCopiloto');
    const copilotoTyping = document.getElementById('copilotoTyping');
    
    // Selectores para el Historial de Chats
    const copilotoHistoryBtn = document.getElementById('btnHistoryCopiloto');
    const copilotoHistoryPanel = document.getElementById('copilotoHistoryPanel');
    const copilotoCloseHistory = document.getElementById('btnCloseHistoryCopiloto');
    const copilotoHistoryList = document.getElementById('copilotoHistoryList');
    
    let isChatOpen = false;
    let isHistoryLoaded = false;
    let isGenerating = false;

    // Toggle ventana de chat
    copilotoBtn.addEventListener('click', () => {
        if (!isChatOpen) {
            copilotoWindow.classList.add('show');
            copilotoWindow.style.display = 'flex';
            copilotoInput.focus();
            loadHistory(!isHistoryLoaded);
        } else {
            closeChat();
        }
        isChatOpen = !isChatOpen;
    });

    copilotoClose.addEventListener('click', (e) => {
        e.stopPropagation();
        closeChat();
        isChatOpen = false;
    });

    // Toggle del Panel de Historial
    copilotoHistoryBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleHistoryPanel();
    });

    copilotoCloseHistory.addEventListener('click', (e) => {
        e.stopPropagation();
        copilotoHistoryPanel.classList.remove('active');
    });

    async function toggleHistoryPanel() {
        const isActive = copilotoHistoryPanel.classList.toggle('active');
        if (isActive) {
            copilotoHistoryList.innerHTML = '<div style="font-size:0.75rem;color:#64748b;text-align:center;padding:20px;">Cargando historial...</div>';
            try {
                const response = await fetch('/personal-chat/conversaciones', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                renderConversationsList(data.conversaciones);
            } catch(e) {
                console.error(e);
                copilotoHistoryList.innerHTML = '<div style="font-size:0.75rem;color:#ef4444;text-align:center;padding:20px;">Error al cargar conversaciones.</div>';
            }
        }
    }

    function renderConversationsList(conversaciones) {
        copilotoHistoryList.innerHTML = '';
        if (!conversaciones || conversaciones.length === 0) {
            copilotoHistoryList.innerHTML = '<div style="font-size:0.75rem;color:#64748b;text-align:center;padding:20px;">No tienes conversaciones previas.</div>';
            return;
        }

        const limit = 5;
        let showAll = false;

        function renderItems() {
            copilotoHistoryList.innerHTML = '';
            const itemsToRender = showAll ? conversaciones : conversaciones.slice(0, limit);
            
            itemsToRender.forEach(c => {
                const div = document.createElement('div');
                div.className = `copiloto-history-item${c.is_active ? ' active' : ''}`;
                div.innerHTML = `
                    <span class="copiloto-history-item-preview">${escapeHtml(c.preview)}</span>
                    <span class="copiloto-history-item-date">${c.date}</span>
                `;
                div.addEventListener('click', () => selectConversation(c.id));
                copilotoHistoryList.appendChild(div);
            });

            if (!showAll && conversaciones.length > limit) {
                const viewAllBtn = document.createElement('button');
                viewAllBtn.className = 'copiloto-history-view-all-btn';
                viewAllBtn.innerText = 'Ver todos los chats';
                viewAllBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showAll = true;
                    renderItems();
                });
                copilotoHistoryList.appendChild(viewAllBtn);
            }
        }

        renderItems();
    }

    async function selectConversation(id) {
        copilotoHistoryPanel.classList.remove('active');
        copilotoMessages.innerHTML = '<div style="font-size:0.75rem;color:#64748b;text-align:center;padding:20px;">Cargando chat...</div>';
        try {
            await fetch(`/personal-chat/conversaciones/${id}/active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            isHistoryLoaded = false;
            await loadHistory();
        } catch (e) {
            console.error('Error al cambiar de conversación:', e);
            copilotoMessages.innerHTML = '<div style="font-size:0.75rem;color:#ef4444;text-align:center;padding:20px;">Error al cargar la conversación.</div>';
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function closeChat() {
        copilotoWindow.classList.remove('show');
        copilotoHistoryPanel.classList.remove('active');
        setTimeout(() => {
            if(!copilotoWindow.classList.contains('show')) {
                copilotoWindow.style.display = 'none';
            }
        }, 300);
    }

    let selectedImageBase64 = null;

    // Activar/desactivar botón de enviar según texto o imagen
    function checkSendButton() {
        const hasText = copilotoInput.value.trim() !== '';
        const hasImage = selectedImageBase64 !== null;
        btnSend.disabled = ((!hasText && !hasImage) || isGenerating);
    }

    copilotoInput.addEventListener('input', () => {
        checkSendButton();
        adjustTextareaHeight();
    });

    function adjustTextareaHeight() {
        copilotoInput.style.height = 'auto';
        copilotoInput.style.height = (copilotoInput.scrollHeight) + 'px';
    }

    function clearSelectedImage() {
        selectedImageBase64 = null;
        const fileInputEl = document.getElementById('fileCopilotoImage');
        if (fileInputEl) fileInputEl.value = '';
        const container = document.getElementById('copilotoImagePreviewContainer');
        if (container) {
            container.classList.add('d-none');
            container.classList.remove('d-flex');
        }
        const previewImgEl = document.getElementById('copilotoImagePreviewImg');
        if (previewImgEl) previewImgEl.src = '';
        const previewNameEl = document.getElementById('copilotoImagePreviewName');
        if (previewNameEl) previewNameEl.textContent = '';
        checkSendButton();
    }

    // Configuración de Adjuntar Imagen
    const btnAttach = document.getElementById('btnAttachCopiloto');
    const fileInput = document.getElementById('fileCopilotoImage');
    const previewContainer = document.getElementById('copilotoImagePreviewContainer');
    const previewImg = document.getElementById('copilotoImagePreviewImg');
    const previewName = document.getElementById('copilotoImagePreviewName');
    const btnRemoveImage = document.getElementById('btnRemoveCopilotoImage');

    if (btnAttach && fileInput && previewContainer && previewImg && previewName && btnRemoveImage) {
        btnAttach.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                if (typeof toastr !== 'undefined') toastr.error('Por favor, seleccione un archivo de imagen.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                if (typeof toastr !== 'undefined') toastr.error('La imagen es demasiado grande. Máximo 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                selectedImageBase64 = event.target.result;
                previewImg.src = selectedImageBase64;
                previewName.textContent = file.name;
                previewContainer.classList.remove('d-none');
                previewContainer.classList.add('d-flex');
                checkSendButton();
            };
            reader.readAsDataURL(file);
        });

        btnRemoveImage.addEventListener('click', () => {
            clearSelectedImage();
        });
    }

    // Configuración de Dictado de Voz (Speech Recognition)
    const btnVoice = document.getElementById('btnVoiceCopiloto');
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'es-PE';
        recognition.continuous = false;
        recognition.interimResults = false;

        let isListening = false;

        btnVoice.addEventListener('click', (e) => {
            e.stopPropagation();
            if (isListening) {
                recognition.stop();
            } else {
                try {
                    recognition.start();
                } catch (err) {
                    console.error('Error al iniciar dictado:', err);
                }
            }
        });

        recognition.onstart = () => {
            isListening = true;
            btnVoice.classList.add('listening');
            btnVoice.title = 'Escuchando... Haz clic para detener';
        };

        recognition.onend = () => {
            isListening = false;
            btnVoice.classList.remove('listening');
            btnVoice.title = 'Dictar por voz';
        };

        recognition.onerror = (event) => {
            console.error('Error en reconocimiento de voz:', event.error);
            isListening = false;
            btnVoice.classList.remove('listening');
            btnVoice.title = 'Dictar por voz';
            if (event.error === 'not-allowed') {
                if (typeof toastr !== 'undefined') toastr.warning('Permiso de micrófono denegado.');
            }
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            if (transcript) {
                const currentVal = copilotoInput.value;
                const spacer = currentVal.trim() === '' ? '' : ' ';
                copilotoInput.value = currentVal + spacer + transcript;
                checkSendButton();
                adjustTextareaHeight();
            }
        };
    } else {
        btnVoice.style.display = 'none';
    }

    // Actualizar el contador en la cabecera
    function updateCounter(count, limit) {
        const counterEl = document.getElementById('copilotoCounter');
        if (!counterEl) return;
        
        let limitText = (limit === null || limit === undefined) ? '∞' : limit;
        counterEl.textContent = `${count}/${limitText}`;
        
        if (limit !== null && limit !== undefined) {
            let percentage = (count / limit) * 100;
            if (percentage >= 100) {
                counterEl.style.backgroundColor = '#ff6b6b';
                counterEl.style.color = '#ffffff';
            } else if (percentage >= 80) {
                counterEl.style.backgroundColor = '#feca57';
                counterEl.style.color = '#333333';
            } else {
                counterEl.style.backgroundColor = '#ffffff';
                counterEl.style.color = '#10ac84';
            }
        } else {
            counterEl.style.backgroundColor = '#ffffff';
            counterEl.style.color = '#10ac84';
        }
    }

    // Cargar Historial y Cuota
    function loadHistory(reloadBubbles = true) {
        $.get('/personal-chat/history', function(res) {
            isHistoryLoaded = true;
            if (reloadBubbles) {
                if (res.history && res.history.length > 0) {
                    // Limpiar mensaje de bienvenida
                    copilotoMessages.innerHTML = '';
                    res.history.forEach(msg => {
                        appendMessage(msg.role, msg.content);
                    });
                    scrollToBottom();
                }
            }
            if (res.messages_count !== undefined && res.messages_limit !== undefined) {
                updateCounter(res.messages_count, res.messages_limit);
            }
        });
    }

    // Agregar Burbuja al Chat
    function appendMessage(role, content, imageUrl = null) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `copiloto-message ${role}`;
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'copiloto-bubble';
        
        let htmlContent = '';
        if (imageUrl) {
            htmlContent += `<div class="mb-2"><img src="${imageUrl}" class="rounded shadow-sm img-fluid" style="max-width: 160px; max-height: 120px; object-fit: contain; border: 1px solid rgba(0,0,0,0.1);"></div>`;
        }
        
        htmlContent += formatMarkdown(content);
        bubbleDiv.innerHTML = htmlContent;
        
        msgDiv.appendChild(bubbleDiv);
        copilotoMessages.appendChild(msgDiv);
        scrollToBottom();
        return bubbleDiv;
    }

    function scrollToBottom() {
        copilotoMessages.scrollTop = copilotoMessages.scrollHeight;
    }

    // Envío de Mensaje con Streaming
    function sendMessage() {
        const text = copilotoInput.value.trim();
        const image = selectedImageBase64;
        
        if ((text === '' && !image) || isGenerating) return;

        isGenerating = true;
        btnSend.disabled = true;
        copilotoInput.value = '';
        copilotoInput.style.height = 'auto';

        // Agregar mensaje de usuario
        appendMessage('user', text, image);

        // Ocultar previsualización
        clearSelectedImage();

        // Mostrar indicador de escribiendo
        copilotoTyping.classList.remove('d-none');
        scrollToBottom();

        // Burbuja donde se escribirá el streaming de la IA
        let responseBubble = null;
        let accumulatedText = '';

        // Iniciar conexión EventSource para streaming
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/personal-chat/message', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        
        let lastIndex = 0;
        xhr.onprogress = function() {
            copilotoTyping.classList.add('d-none');

            const currentText = xhr.responseText;
            const doubleNewlineIndex = currentText.lastIndexOf('\n\n');

            if (doubleNewlineIndex > lastIndex) {
                const chunk = currentText.substring(lastIndex, doubleNewlineIndex);
                lastIndex = doubleNewlineIndex;

                const lines = chunk.split('\n\n');
                lines.forEach(raw => {
                    const line = raw.trim();
                    if (line.startsWith('data: ')) {
                        const dataStr = line.substring(6).trim();
                        
                        if (dataStr === '[DONE]') {
                            return;
                        }
                        
                        try {
                            const parsed = JSON.parse(dataStr);
                            if (parsed) {
                                if (parsed.text) {
                                    if (!responseBubble) {
                                        responseBubble = appendMessage('assistant', '');
                                    }
                                    accumulatedText += parsed.text;
                                    responseBubble.innerHTML = formatMarkdown(accumulatedText);
                                    scrollToBottom();
                                }
                                if (parsed.messages_count !== undefined && parsed.messages_limit !== undefined) {
                                    updateCounter(parsed.messages_count, parsed.messages_limit);
                                }
                            }
                        } catch(e) {
                            // Ignorar errores parciales de JSON
                        }
                    }
                });
            }
        };

        xhr.onload = function() {
            isGenerating = false;
            copilotoTyping.classList.add('d-none');
            
            if (xhr.status !== 200) {
                try {
                    const errRes = JSON.parse(xhr.responseText);
                    appendMessage('assistant', `⚠️ ${errRes.message || 'Error al procesar la solicitud.'}`);
                } catch(e) {
                    appendMessage('assistant', '⚠️ Error de comunicación con FarmaCopiloto.');
                }
            }
        };

        xhr.onerror = function() {
            isGenerating = false;
            copilotoTyping.classList.add('d-none');
            appendMessage('assistant', '⚠️ Error de red. No se pudo conectar con el servidor.');
        };

        xhr.send(JSON.stringify({ message: text, image: image }));
    }

    // Enviar con Enter (sin Shift)
    copilotoInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    btnSend.addEventListener('click', sendMessage);

    // Reiniciar Conversación
    copilotoReset.addEventListener('click', () => {
        Swal.fire({
            title: '¿Nueva conversación?',
            text: "Se archivará este chat y comenzaremos una sesión limpia.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10ac84',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, reiniciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/personal-chat/reset', { _token: '{{ csrf_token() }}' }, function() {
                    copilotoMessages.innerHTML = `
                        <div class="copiloto-message assistant welcome">
                            <div class="copiloto-bubble">
                                <strong>¡Chat reiniciado!</strong><br>
                                ¿En qué medicamento o consulta de esta sucursal te puedo ayudar ahora?
                            </div>
                        </div>
                    `;
                    isHistoryLoaded = true;
                    scrollToBottom();
                    if (typeof toastr !== 'undefined') toastr.success('Chat reiniciado');
                });
            }
        });
    });

    // Función unificada para ejecutar la venta y agregar el medicamento al carrito directamente
    function ejecutarVentaMed(medId, $btn = null) {
        if (window.posEngine && typeof window.posEngine.renderCarrito === 'function') {
            // Obtener sucursalId desde el DOM si es posible, de lo contrario usar sesión
            let sucursalId = $('#sucursal_id').val();
            if (!sucursalId) {
                sucursalId = typeof SUCURSAL_ID !== 'undefined' ? SUCURSAL_ID : {{ session('sucursal_id', 0) }};
            }
            
            // 1. Obtener los lotes de inmediato para leer la ubicación FEFO y el stock
            $.get('/ventas/lookup-lotes', { 
                medicamento_id: medId, 
                sucursal_id: sucursalId 
            }, function(lotes) {
                // Seteamos medicamentoSeleccionado obteniendo los datos del medicamento
                $.get('/ventas/lookup-medicamentos', { q: medId, sucursal_id: sucursalId }, function(res) {
                    let match = res.find(m => m.medicamento_id == medId);
                    if (match) {
                        window.posEngine.medicamentoSeleccionado = {
                            medicamento_id: match.medicamento_id,
                            nombre: match.nombre,
                            presentacion: match.presentacion,
                            precio_venta: parseFloat(match.precio_venta)
                        };

                        if (lotes && lotes.length > 0) {
                            let firstLote = lotes[0];
                            let loteId = firstLote.id;
                            let stockReal = parseInt(firstLote.stock_actual) || 0;
                            let codigoLote = firstLote.codigo_lote || 'Sin lote';
                            let vencimiento = firstLote.fecha_vencimiento || 'Sin vencimiento';
                            let precio = parseFloat(firstLote.precios ? firstLote.precios.unidad : match.precio_venta) || 0.00;
                            let ubicacion = firstLote.ubicacion || 'General';

                            // Agregar directamente al carrito
                            let currentCarrito = window.posEngine.carrito || {};
                            let uniqueId = loteId + '-UNIDAD';

                            if (currentCarrito[uniqueId]) {
                                currentCarrito[uniqueId].cantidad += 1;
                            } else {
                                currentCarrito[uniqueId] = {
                                    unique_id: uniqueId,
                                    unidad_medida: 'UNIDAD',
                                    lote_id: loteId,
                                    nombre: match.nombre,
                                    presentacion: `${match.presentacion || ''} [UNIDAD]`,
                                    codigo_lote: codigoLote,
                                    cantidad: 1,
                                    precio_venta: precio,
                                    stock_max: stockReal,
                                    factor: 1
                                };
                            }

                            // Actualizar carrito y renderizar en el POS
                            window.posEngine.carrito = currentCarrito;
                            window.posEngine.renderCarrito();
                            
                            // Mostrar toast no invasivo por 2.5 segundos con ubicación FEFO
                            Swal.fire({
                                title: '¡Agregado al Carrito!',
                                html: `<div style="font-size: 0.85rem; text-align: left;">
                                         Medicamento: <strong>${match.nombre}</strong><br>
                                         Ubicación FEFO: <strong>${ubicacion}</strong><br>
                                         Lote: <strong>${codigoLote}</strong> (Vence: ${vencimiento})
                                       </div>`,
                                icon: 'success',
                                timer: 2500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end',
                                background: '#10ac84',
                                color: '#ffffff',
                                iconColor: '#ffffff'
                            });
                        } else {
                            if (typeof toastr !== 'undefined') toastr.error('No hay lotes con stock para este medicamento.');
                        }
                    }
                    if ($btn) {
                        $btn.prop('disabled', false);
                    }
                });
            });
        } else {
            if ($btn) {
                $btn.prop('disabled', false);
            }
        }
    }

    // Manejar Click en botón "Usar" (Vender directamente)
    $(document).on('click', '.btn-usar-med', function(e) {
        e.preventDefault();
        const medId = $(this).data('id');
        let $btn = $(this);
        $btn.prop('disabled', true);
        
        // 1. Verificamos si ya estamos en la vista de Punto de Venta (/ventas/create)
        if (window.location.pathname.includes('/ventas/create')) {
            ejecutarVentaMed(medId, $btn);
            closeChat();
            isChatOpen = false;
        } else {
            // 2. Si estamos en otra página, redireccionamos a ventas/create pasándole el ID
            window.location.href = `/ventas/create?add_med_id=${medId}`;
        }
    });

    // Carga automática del modal de lotes si venimos redirigidos con ?add_med_id (Polling robusto)
    if (window.location.pathname.includes('/ventas/create')) {
        const urlParams = new URLSearchParams(window.location.search);
        const autoAddId = urlParams.get('add_med_id');
        if (autoAddId) {
            let attempts = 0;
            const maxAttempts = 50; // Hasta 5 segundos de espera
            const interval = setInterval(() => {
                attempts++;
                if (window.posEngine && typeof window.posEngine.renderCarrito === 'function') {
                    clearInterval(interval);
                    ejecutarVentaMed(autoAddId);
                    // Limpiar la URL sin recargar la página
                    const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.pushState({path: newUrl}, '', newUrl);
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    console.error("FarmaCopiloto: No se pudo conectar con el motor del POS.");
                }
            }, 100);
        }
    }

    // Formateador Markdown Básico
    function formatMarkdown(text) {
        if (!text) return '';
        let html = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // Soporte para botón de Venta [Vender:ID] (antes de escapar corchetes)
        html = html.replace(/\[Vender:(\d+)\]/g, '<button class="btn btn-xs btn-success ml-2 py-0 px-2 btn-usar-med" data-id="$1" style="font-size: 0.75rem; border-radius: 4px; padding: 2px 6px; font-weight: 500;"><i class="fas fa-cart-plus"></i> Usar</button>');

        // Negritas (**text**)
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Cursivas (*text*)
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // Código en línea (`code`)
        html = html.replace(/`(.*?)`/g, '<code class="bg-light px-1 rounded">$1</code>');

        // Listas con viñetas
        html = html.replace(/^\s*[-*]\s+(.*)$/gm, '• $1');

        return html;
    }
});
</script>
