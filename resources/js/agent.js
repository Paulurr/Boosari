document.addEventListener('DOMContentLoaded', () => {
    const messagesBox = document.getElementById('agent-messages');
    const emptyState = document.getElementById('agent-empty-state');
    const input = document.getElementById('agent-input');
    const sendBtn = document.getElementById('agent-send');
    const newChatBtn = document.getElementById('agent-new-chat');
    const historyBox = document.getElementById('agent-history');
    const charCount = document.getElementById('agent-char-count');

    const sidebar = document.getElementById('agent-sidebar');
    const sidebarOverlay = document.getElementById('agent-sidebar-overlay');
    const sidebarToggle = document.getElementById('agent-sidebar-toggle');

    let currentConversationId = null;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // =====================================================================
    // Sidebar móvil
    // =====================================================================
    function openSidebar() {
        sidebar?.classList.remove('-translate-x-full');
        sidebarOverlay?.classList.remove('hidden');
    }
    function closeSidebar() {
        sidebar?.classList.add('-translate-x-full');
        sidebarOverlay?.classList.add('hidden');
    }
    sidebarToggle?.addEventListener('click', openSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);

    // =====================================================================
    // Helpers de UI
    // =====================================================================
    function hideEmptyState() {
        const emptyState = document.getElementById('agent-empty-state');
        if (emptyState) {
            emptyState.remove(); // Elimina el elemento por completo del chat
    }
}

    function showEmptyState() {
        if (!document.getElementById('agent-empty-state')) {
            const emptyDiv = document.createElement('div');
            emptyDiv.id = 'agent-empty-state';
            emptyDiv.className = 'max-w-md mx-auto text-center pt-10';
            emptyDiv.innerHTML = `
                <div class="w-14 h-14 rounded-full bgcol4 col1 flex items-center justify-center mx-auto mb-4 text-xl font-bold">IA</div>
                <p class="col7 font-semibold mb-1">¿En qué te ayudo?</p>
                <p class="col7 opacity-60 text-sm mb-5">Pregúntame sobre tus finanzas, con base en tus datos reales.</p>
                <div class="flex flex-wrap justify-center gap-2">
                    <button class="agent-suggestion text-xs col7 border rounded-full px-3 py-1.5 hover:bgcol2 transition">¿En qué debería enfocarme este mes?</button>
                    <button class="agent-suggestion text-xs col7 border rounded-full px-3 py-1.5 hover:bgcol2 transition">¿Cómo van mis metas de ahorro?</button>
                    <button class="agent-suggestion text-xs col7 border rounded-full px-3 py-1.5 hover:bgcol2 transition">¿Qué deuda debería priorizar?</button>
                </div>
            `;
            messagesBox.appendChild(emptyDiv);
        } else {
            document.getElementById('agent-empty-state').classList.remove('hidden');
        }
    }

    function showHistoryEmptyMessageIfNeeded() {
        if (historyBox.querySelector('.agent-history-item')) return;
        if (document.getElementById('agent-history-empty')) return;

        const div = document.createElement('div');
        div.id = 'agent-history-empty';
        div.className = 'text-center col7 opacity-60 text-xs px-4 py-10';
        div.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Aún no tienes conversaciones.`;
        historyBox.appendChild(div);
    }

    function formatTime(dateInput) {
        try {
            const d = dateInput ? new Date(dateInput) : new Date();
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch {
            return '';
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // =====================================================================
    // Burbujas de mensaje
    // =====================================================================
    function addBubble(rol, contenido, fecha = null) {
        hideEmptyState();
        const esUsuario = rol === 'usuario' || rol === 'user';

        const wrap = document.createElement('div');
        wrap.className = `agent-bubble-in flex items-end gap-2 ${esUsuario ? 'justify-end' : 'justify-start'}`;

        const avatar = `<div class="w-7 h-7 rounded-full bgcol4 col1 flex items-center justify-center shrink-0 text-[10px] font-bold">${esUsuario ? 'Tú' : 'IA'}</div>`;

        // IA: fondo col2 (neutro) · Usuario: fondo col4 (color de acento), tal como en el resto de la app.
        const bubble = `
            <div class="max-w-[80%] md:max-w-lg">
                <div class="p-3 text-sm leading-relaxed whitespace-pre-wrap break-words ${esUsuario ? 'bgcol4 col1 rounded-2xl rounded-br-sm' : 'bgcol2 col7 rounded-2xl rounded-bl-sm'}">${escapeHtml(contenido)}</div>
                <p class="text-[10px] col7 opacity-50 mt-1 ${esUsuario ? 'text-right' : 'text-left'}">${formatTime(fecha)}</p>
            </div>`;

        wrap.innerHTML = esUsuario ? bubble + avatar : avatar + bubble;
        messagesBox.appendChild(wrap);
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function setActiveHistoryItem(id) {
        historyBox.querySelectorAll('.agent-history-item').forEach(item => {
            item.classList.toggle('bgcol2', String(item.dataset.id) === String(id));
        });
    }

    // =====================================================================
    // Textarea auto-resize + contador
    // =====================================================================
    function autoResize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 128) + 'px';
    }
    input?.addEventListener('input', () => {
        autoResize();
        if (charCount) charCount.textContent = String(input.value.length);
    });

    // =====================================================================
    // Carga / envío
    // =====================================================================
    async function loadConversation(id) {
        currentConversationId = id;
        messagesBox.innerHTML = '';
        setActiveHistoryItem(id);
        closeSidebar();

        try {
            const res = await fetch(`/agent/${id}/messages`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('No se pudo cargar la conversación.');
            const json = await res.json();

            if (!json.data || json.data.length === 0) {
                showEmptyState();
            } else {
                (json.data || []).forEach(m => addBubble(m.rol, m.contenido, m.created_at));
            }
        } catch (err) {
            addBubble('asistente', `⚠️ ${err.message}`);
        }
    }

    async function sendMessage(textoForzado = null) {
        const mensaje = (textoForzado ?? input.value).trim();
        if (!mensaje || sendBtn.disabled) return;

        addBubble('usuario', mensaje);
        input.value = '';
        autoResize();
        if (charCount) charCount.textContent = '0';
        sendBtn.disabled = true;

        const thinking = document.createElement('div');
        thinking.className = 'agent-bubble-in flex items-end gap-2 justify-start';
        thinking.innerHTML = `
            <div class="w-7 h-7 rounded-full bgcol4 col1 flex items-center justify-center shrink-0 text-[10px] font-bold">IA</div>
            <div class="bgcol2 col7 p-3 rounded-2xl rounded-bl-sm text-sm flex gap-1 items-center">
                <span class="agent-dot w-1.5 h-1.5 rounded-full bgcol4 inline-block" style="animation-delay:0ms"></span>
                <span class="agent-dot w-1.5 h-1.5 rounded-full bgcol4 inline-block" style="animation-delay:150ms"></span>
                <span class="agent-dot w-1.5 h-1.5 rounded-full bgcol4 inline-block" style="animation-delay:300ms"></span>
            </div>`;
        messagesBox.appendChild(thinking);
        messagesBox.scrollTop = messagesBox.scrollHeight;

        try {
            const res = await fetch('/agent/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mensaje, conversation_id: currentConversationId })
            });
            const json = await res.json().catch(() => ({}));
            thinking.remove();

            if (!res.ok) throw new Error(json.message || 'Error al contactar al asistente.');

            const esConversacionNueva = !currentConversationId;
            currentConversationId = json.conversation_id;
            addBubble('asistente', json.reply);

            if (esConversacionNueva) {
                prependHistoryItem(currentConversationId, mensaje);
            }
            setActiveHistoryItem(currentConversationId);
        } catch (err) {
            thinking.remove();
            addBubble('asistente', `⚠️ ${err.message}`);
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    }

    function buildHistoryItem(id, titulo, cuando = 'Ahora') {
        const item = document.createElement('div');
        item.dataset.id = id;
        item.className = 'agent-history-item agent-history-item-enter group relative w-full rounded-lg text-sm col7 hover:bgcol2 transition';
        item.innerHTML = `
            <button type="button" class="agent-history-open w-full text-left p-3 pr-9 flex flex-col gap-0.5 rounded-lg">
                <span class="truncate font-medium">${escapeHtml(titulo.slice(0, 40))}</span>
                <span class="text-[11px] col7 opacity-60">${escapeHtml(cuando)}</span>
            </button>
            <button type="button" class="agent-delete-btn absolute right-1.5 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center rounded-md text-red-500 hover:bg-red-500/10" title="Eliminar conversación">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
            </button>`;
        return item;
    }

    function prependHistoryItem(id, tituloBase) {
        document.getElementById('agent-history-empty')?.remove();
        historyBox.prepend(buildHistoryItem(id, tituloBase));
    }

    // =====================================================================
    // Eliminar conversación
    // =====================================================================
    async function deleteConversation(item) {
        const id = item.dataset.id;
        const titulo = item.querySelector('.agent-history-open span')?.textContent?.trim() || 'esta conversación';

        if (!confirm(`¿Eliminar "${titulo}"? Esta acción no se puede deshacer.`)) return;

        try {
            const res = await fetch(`/agent/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || 'No se pudo eliminar la conversación.');

            // Animación de salida antes de quitarlo del DOM.
            item.classList.add('agent-history-item-leave');
            item.addEventListener('transitionend', () => {
                item.remove();
                showHistoryEmptyMessageIfNeeded();
            }, { once: true });

            if (String(currentConversationId) === String(id)) {
                currentConversationId = null;
                messagesBox.innerHTML = '';
                showEmptyState();
            }
        } catch (err) {
            alert(err.message);
        }
    }

    // =====================================================================
    // Listeners
    // =====================================================================
    sendBtn.addEventListener('click', () => sendMessage());
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    newChatBtn.addEventListener('click', () => {
        currentConversationId = null;
        messagesBox.innerHTML = ''; // Limpia las burbujas antiguas
        showEmptyState();           // Vuelve a insertar/mostrar el "¿En qué te ayudo?"
        setActiveHistoryItem(null);
        closeSidebar();
        input.focus();
    });

    historyBox.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.agent-delete-btn');
        if (deleteBtn) {
            e.stopPropagation();
            deleteConversation(deleteBtn.closest('.agent-history-item'));
            return;
        }

        const openBtn = e.target.closest('.agent-history-open');
        if (openBtn) {
            const item = openBtn.closest('.agent-history-item');
            if (item) loadConversation(item.dataset.id);
        }
    });

    messagesBox.addEventListener('click', (e) => {
        const chip = e.target.closest('.agent-suggestion');
        if (chip) sendMessage(chip.textContent.trim());
    });
});