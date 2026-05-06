(function () {
    const toggle = document.getElementById('ss-ai-toggle');
    const box = document.getElementById('ss-ai-box');
    const closeBtn = document.getElementById('ss-ai-close');
    const form = document.getElementById('ss-ai-form');
    const input = document.getElementById('ss-ai-input');
    const messages = document.getElementById('ss-ai-messages');
    if (!toggle || !box || !closeBtn || !form || !input || !messages) return;

    const endpoint = window.StartSmartAiChat?.endpoint || '/chatbot.php';
    const chatHistory = [
        { role: 'system', content: 'You are a helpful assistant for this website.' }
    ];
    let typingEl = null;

    const addMessage = (role, text) => {
        const item = document.createElement('div');
        item.className = `ss-ai-msg ${role}`;
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
    };

    toggle.addEventListener('click', () => box.classList.toggle('open'));
    closeBtn.addEventListener('click', () => box.classList.remove('open'));

    const showTyping = () => {
        typingEl = document.createElement('div');
        typingEl.className = 'ss-ai-msg bot ss-ai-typing';
        typingEl.textContent = 'Typing...';
        messages.appendChild(typingEl);
        messages.scrollTop = messages.scrollHeight;
    };

    const hideTyping = () => {
        if (typingEl && typingEl.parentNode) {
            typingEl.parentNode.removeChild(typingEl);
        }
        typingEl = null;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) {
            addMessage('bot', 'Please type a message first.');
            return;
        }
        addMessage('user', text);
        chatHistory.push({ role: 'user', content: text });
        input.value = '';
        input.disabled = true;
        showTyping();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ messages: chatHistory }),
            });
            const raw = await response.text();
            let data = {};
            try {
                data = JSON.parse(raw);
            } catch (e) {
                data = { error: raw && raw.trim() !== '' ? raw.trim() : 'Invalid server response.' };
            }
            const botText = response.ok
                ? (data.reply || 'No reply available.')
                : (data.error || 'Unable to answer right now.');
            addMessage('bot', botText);
            if (response.ok) {
                chatHistory.push({ role: 'assistant', content: botText });
            }
        } catch (e) {
            addMessage('bot', 'Connection error. Please try again.');
        } finally {
            hideTyping();
            input.disabled = false;
            input.focus();
        }
    });
})();
