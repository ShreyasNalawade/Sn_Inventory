@extends('layouts.app')

@section('title', 'Store Assistant')

@section('styles')
    <style>
        .main-content:has(.ai-chat-page) {
            overflow: hidden;
        }

        .ai-chat-page {
            height: calc(100vh - 56px);
            max-height: calc(100vh - 56px);
            box-sizing: border-box;
            display: flex;
            padding: 16px;
        }

        .ai-chat {
            width: min(980px, 100%);
            height: 100%;
            min-height: 0;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid rgba(255, 99, 71, 0.16);
            border-radius: 22px;
            box-shadow: 0 18px 50px rgba(120, 53, 15, 0.12);
            overflow: hidden;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: #1f2937;
        }

        .ai-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #ff6347 0%, #ff8a65 100%);
            color: #fff;
        }

        .ai-logo {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.28);
            font-size: 18px;
            flex-shrink: 0;
        }

        .ai-heading {
            min-width: 0;
        }

        .ai-heading h1 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .ai-heading p {
            margin: 2px 0 0;
            font-size: 0.84rem;
            color: rgba(255, 255, 255, 0.88);
        }

        .ai-header-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .ai-retention,
        .ai-clear {
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            font-family: inherit;
        }

        .ai-retention {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
        }

        .ai-clear {
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            padding: 8px 12px;
            cursor: pointer;
        }

        .ai-clear:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.28);
        }

        .ai-clear:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .ai-suggestions {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 12px 16px;
            border-bottom: 1px solid #f3e7e3;
            background: #fff;
        }

        .ai-suggestions::-webkit-scrollbar {
            height: 0;
        }

        .ai-chip {
            border: 1px solid #f3d2cb;
            background: #fff7f5;
            color: #9a3412;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            white-space: nowrap;
            cursor: pointer;
        }

        .ai-chip:hover:not(:disabled) {
            background: #ffede8;
        }

        .ai-chip:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .ai-thread {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 18px 16px 8px;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #fff8f6 0%, #fff 42%);
            scrollbar-width: thin;
            scrollbar-color: #e7cfc8 transparent;
        }

        .ai-empty {
            margin: auto;
            max-width: 420px;
            text-align: center;
            padding: 24px 12px 36px;
        }

        .ai-empty-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 14px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: #fff1ee;
            color: #ff6347;
            font-size: 26px;
            box-shadow: inset 0 0 0 1px rgba(255, 99, 71, 0.08);
        }

        .ai-empty h2 {
            margin: 0 0 8px;
            font-size: 1.35rem;
            color: #1f2937;
        }

        .ai-empty p {
            margin: 0;
            color: #6b7280;
            line-height: 1.55;
        }

        .ai-day {
            align-self: center;
            margin: 4px 0 14px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #f1e4e0;
            color: #78716c;
            font-size: 12px;
            font-weight: 700;
        }

        .ai-msg {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            max-width: 100%;
        }

        .ai-msg-user {
            flex-direction: row-reverse;
        }

        .ai-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            font-size: 13px;
        }

        .ai-msg-assistant .ai-avatar {
            background: #fff1ee;
            color: #e4573d;
        }

        .ai-msg-user .ai-avatar {
            background: #ff6347;
            color: #fff;
        }

        .ai-msg-body {
            max-width: min(78%, 640px);
            display: flex;
            flex-direction: column;
        }

        .ai-msg-user .ai-msg-body {
            align-items: flex-end;
        }

        .ai-bubble {
            padding: 11px 14px;
            border-radius: 16px;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.5;
            font-size: 14.5px;
        }

        .ai-msg-assistant .ai-bubble {
            background: #fff;
            border: 1px solid #efe6e3;
            color: #1f2937;
            border-top-left-radius: 6px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .ai-msg-user .ai-bubble {
            background: linear-gradient(135deg, #ff6347, #ff7f62);
            color: #fff;
            border-top-right-radius: 6px;
        }

        .ai-time {
            margin-top: 5px;
            font-size: 11px;
            font-weight: 600;
        }

        .ai-msg-assistant .ai-time {
            color: #94a3b8;
        }

        .ai-msg-user .ai-time {
            color: #c2410c;
        }

        .ai-msg.is-new {
            animation: ai-rise 0.22s ease;
        }

        .ai-typing {
            display: flex;
            gap: 5px;
            align-items: center;
            min-height: 18px;
            padding: 2px 0;
        }

        .ai-typing span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #ff6347;
            animation: ai-blink 1s infinite ease-in-out;
        }

        .ai-typing span:nth-child(2) {
            animation-delay: 0.15s;
        }

        .ai-typing span:nth-child(3) {
            animation-delay: 0.3s;
        }

        .ai-composer {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            padding: 12px 16px 0;
            background: #fff;
        }

        .ai-composer textarea {
            flex: 1;
            resize: none;
            border: 1px solid #eadfdc;
            border-radius: 16px;
            padding: 12px 14px;
            min-height: 48px;
            max-height: 120px;
            font: inherit;
            line-height: 1.45;
            color: #1f2937;
            background: #fbf8f7;
        }

        .ai-composer textarea:focus {
            outline: none;
            border-color: #ff6347;
            box-shadow: 0 0 0 3px rgba(255, 99, 71, 0.16);
            background: #fff;
        }

        .ai-send {
            width: 48px;
            height: 48px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #ff6347, #f08a6a);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .ai-send:hover:not(:disabled) {
            filter: brightness(0.97);
        }

        .ai-send:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .ai-footnote {
            margin: 0;
            padding: 8px 16px 14px;
            color: #9ca3af;
            font-size: 12px;
            background: #fff;
        }

        @keyframes ai-rise {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes ai-blink {

            0%,
            80%,
            100% {
                opacity: 0.25;
                transform: translateY(0);
            }

            40% {
                opacity: 1;
                transform: translateY(-2px);
            }
        }

        @media (max-width: 767.98px) {
            .ai-chat-page {
                padding: 8px;
            }

            .ai-chat {
                border-radius: 18px;
            }

            .ai-header {
                align-items: flex-start;
                flex-wrap: wrap;
                padding: 14px;
            }

            .ai-header-actions {
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }

            .ai-msg-body {
                max-width: 86%;
            }

            .ai-retention {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .ai-msg.is-new,
            .ai-typing span {
                animation: none;
            }
        }
    </style>
@endsection

@section('content')
    <div class="ai-chat-page">
        <section class="ai-chat" aria-label="Store assistant">
            <header class="ai-header">
                <div class="ai-logo" aria-hidden="true">
                    <i class="fas fa-store"></i>
                </div>
                <div class="ai-heading">
                    <h1>Store Assistant</h1>
                    <p>Answers from the price list and Vashi Market bills</p>
                </div>
                <div class="ai-header-actions">
                    <span class="ai-retention"><i class="fas fa-clock" aria-hidden="true"></i> Kept 7 days</span>
                    <button type="button" class="ai-clear" id="aiClearChat" disabled>
                        <i class="fas fa-trash-alt" aria-hidden="true"></i> Clear
                    </button>
                </div>
            </header>

            <div class="ai-suggestions" aria-label="Suggested questions">
                <button type="button" class="ai-chip" data-question="How many products do we have?">How many products?</button>
                <button type="button" class="ai-chip" data-question="List oil products">List oil products</button>
                <button type="button" class="ai-chip" data-question="Show unpaid bills">Unpaid bills</button>
                <button type="button" class="ai-chip" data-question="Latest Vashi bills">Latest bills</button>
                <button type="button" class="ai-chip" data-question="What can you do?">What can you do?</button>
            </div>

            <div class="ai-thread" id="aiThread" role="log" aria-live="polite" aria-relevant="additions">
                <div class="ai-empty">
                    <div class="ai-empty-icon" aria-hidden="true">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h2>Ask about the store</h2>
                    <p>Look up a product price or a Vashi Market bill. This conversation stays in this browser for 7 days, then it is removed.</p>
                </div>
            </div>

            <form class="ai-composer" id="aiComposer" autocomplete="off">
                <label class="visually-hidden" for="aiMessage">Your question</label>
                <textarea id="aiMessage" rows="1" maxlength="500" placeholder="Ask for a price, a party, or a bill number" required></textarea>
                <button type="submit" class="ai-send" id="aiSend" disabled aria-label="Send question">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                </button>
            </form>
            <p class="ai-footnote">Enter to send. Shift + Enter for a new line. Each message is saved only in this browser and deleted after 7 days.</p>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const STORAGE_KEY = 'sn_inventory_store_assistant_chat';
            const RETENTION_MS = 7 * 24 * 60 * 60 * 1000;
            const askUrl = @json(route('admin.ai-chat.ask'));
            const csrfToken = @json(csrf_token());

            const thread = document.getElementById('aiThread');
            const form = document.getElementById('aiComposer');
            const input = document.getElementById('aiMessage');
            const sendButton = document.getElementById('aiSend');
            const clearButton = document.getElementById('aiClearChat');
            const chips = Array.from(document.querySelectorAll('.ai-chip'));

            let messages = loadMessages();
            let busy = false;

            function createId() {
                if (window.crypto && crypto.randomUUID) {
                    return crypto.randomUUID();
                }

                return 'm-' + Date.now() + '-' + Math.random().toString(16).slice(2);
            }

            function isFresh(message, now) {
                if (!message || (message.role !== 'user' && message.role !== 'assistant')) {
                    return false;
                }

                if (typeof message.text !== 'string' || message.text.length === 0 || message.text.length > 8000) {
                    return false;
                }

                if (typeof message.createdAt !== 'number' || !Number.isFinite(message.createdAt)) {
                    return false;
                }

                const age = now - message.createdAt;

                return age >= -60000 && age < RETENTION_MS;
            }

            function loadMessages() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) {
                        return [];
                    }

                    const data = JSON.parse(raw);
                    const stored = Array.isArray(data.messages) ? data.messages : [];
                    const now = Date.now();
                    const fresh = stored.filter((message) => isFresh(message, now)).slice(-80);

                    if (fresh.length !== stored.length) {
                        saveMessages(fresh);
                    }

                    return fresh;
                } catch (error) {
                    try {
                        localStorage.removeItem(STORAGE_KEY);
                    } catch (removeError) {
                        // Ignore storage failures and start with an empty conversation.
                    }

                    return [];
                }
            }

            function saveMessages(items) {
                try {
                    if (items.length === 0) {
                        localStorage.removeItem(STORAGE_KEY);
                        return;
                    }

                    localStorage.setItem(STORAGE_KEY, JSON.stringify({
                        version: 1,
                        messages: items.slice(-80).map((message) => ({
                            id: message.id,
                            role: message.role,
                            text: message.text,
                            createdAt: message.createdAt,
                        })),
                    }));
                } catch (error) {
                    // The conversation still works for this visit if storage is blocked.
                }
            }

            function makeMessage(role, text) {
                return {
                    id: createId(),
                    role: role,
                    text: text,
                    createdAt: Date.now(),
                };
            }

            function dayKey(timestamp) {
                const date = new Date(timestamp);
                return date.getFullYear() + '-' + date.getMonth() + '-' + date.getDate();
            }

            function dayLabel(timestamp) {
                const date = new Date(timestamp);
                const today = new Date();
                const startOf = (value) => new Date(value.getFullYear(), value.getMonth(), value.getDate()).getTime();
                const diffDays = Math.round((startOf(today) - startOf(date)) / 86400000);

                if (diffDays === 0) {
                    return 'Today';
                }

                if (diffDays === 1) {
                    return 'Yesterday';
                }

                return date.toLocaleDateString(undefined, {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                });
            }

            function timeLabel(timestamp) {
                return new Date(timestamp).toLocaleTimeString(undefined, {
                    hour: 'numeric',
                    minute: '2-digit',
                });
            }

            function emptyState() {
                const wrap = document.createElement('div');
                wrap.className = 'ai-empty';
                wrap.innerHTML = ''
                    + '<div class="ai-empty-icon" aria-hidden="true"><i class="fas fa-comment-dots"></i></div>'
                    + '<h2>Ask about the store</h2>'
                    + '<p>Look up a product price or a Vashi Market bill. This conversation stays in this browser for 7 days, then it is removed.</p>';
                return wrap;
            }

            function messageNode(message, animate) {
                const row = document.createElement('div');
                row.className = 'ai-msg ' + (message.role === 'user' ? 'ai-msg-user' : 'ai-msg-assistant');
                if (animate) {
                    row.classList.add('is-new');
                }

                const avatar = document.createElement('div');
                avatar.className = 'ai-avatar';
                avatar.setAttribute('aria-hidden', 'true');
                const icon = document.createElement('i');
                icon.className = message.role === 'user' ? 'fas fa-user' : 'fas fa-store';
                avatar.appendChild(icon);

                const body = document.createElement('div');
                body.className = 'ai-msg-body';

                const bubble = document.createElement('div');
                bubble.className = 'ai-bubble';
                bubble.textContent = message.text;

                const time = document.createElement('time');
                time.className = 'ai-time';
                time.dateTime = new Date(message.createdAt).toISOString();
                time.textContent = timeLabel(message.createdAt);

                body.appendChild(bubble);
                body.appendChild(time);
                row.appendChild(avatar);
                row.appendChild(body);

                return row;
            }

            function render(items, animateLast) {
                thread.replaceChildren();

                if (items.length === 0) {
                    thread.appendChild(emptyState());
                    clearButton.disabled = true;
                    return;
                }

                let lastDay = '';
                items.forEach((message, index) => {
                    const key = dayKey(message.createdAt);
                    if (key !== lastDay) {
                        const divider = document.createElement('div');
                        divider.className = 'ai-day';
                        divider.textContent = dayLabel(message.createdAt);
                        thread.appendChild(divider);
                        lastDay = key;
                    }

                    thread.appendChild(messageNode(message, animateLast && index === items.length - 1));
                });

                clearButton.disabled = false;
                requestAnimationFrame(() => {
                    thread.scrollTop = thread.scrollHeight;
                });
            }

            function showTyping() {
                const row = document.createElement('div');
                row.className = 'ai-msg ai-msg-assistant';
                row.id = 'aiTyping';
                row.innerHTML = ''
                    + '<div class="ai-avatar" aria-hidden="true"><i class="fas fa-store"></i></div>'
                    + '<div class="ai-msg-body"><div class="ai-bubble" aria-label="Assistant is looking that up"><div class="ai-typing"><span></span><span></span><span></span></div></div></div>';
                thread.appendChild(row);
                thread.scrollTop = thread.scrollHeight;
            }

            function hideTyping() {
                const typing = document.getElementById('aiTyping');
                if (typing) {
                    typing.remove();
                }
            }

            function resizeInput() {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 120) + 'px';
            }

            function syncSendButton() {
                sendButton.disabled = busy || input.value.trim() === '';
            }

            function setBusy(value) {
                busy = value;
                chips.forEach((chip) => {
                    chip.disabled = value;
                });
                form.setAttribute('aria-busy', value ? 'true' : 'false');
                syncSendButton();
            }

            function failureMessage(status, data) {
                if (status === 419) {
                    return 'Your session expired. Refresh the page, sign in again, and send your question.';
                }

                if (status === 429) {
                    return 'Too many questions at once. Wait a moment and try again.';
                }

                if (status === 422 && data && data.errors && Array.isArray(data.errors.message) && data.errors.message[0]) {
                    return data.errors.message[0];
                }

                return 'I could not look that up right now. Please try again.';
            }

            async function send(text) {
                const question = text.trim();
                if (!question || busy) {
                    return;
                }

                setBusy(true);
                messages.push(makeMessage('user', question));
                saveMessages(messages);
                render(messages, true);
                input.value = '';
                resizeInput();
                showTyping();

                try {
                    const response = await fetch(askUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ message: question }),
                    });
                    const data = await response.json().catch(() => ({}));
                    const answer = response.ok && typeof data.answer === 'string' && data.answer !== ''
                        ? data.answer
                        : failureMessage(response.status, data);

                    hideTyping();
                    messages.push(makeMessage('assistant', answer));
                    saveMessages(messages);
                    render(messages, true);
                } catch (error) {
                    hideTyping();
                    messages.push(makeMessage('assistant', 'I could not reach the server. Check your connection and try again.'));
                    saveMessages(messages);
                    render(messages, true);
                } finally {
                    setBusy(false);
                    input.focus();
                }
            }

            async function clearChat() {
                if (messages.length === 0) {
                    return;
                }

                let confirmed = false;
                if (window.Swal) {
                    const result = await Swal.fire({
                        title: 'Clear this chat?',
                        text: 'Messages saved in this browser will be removed.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Clear chat',
                        cancelButtonText: 'Keep messages',
                        confirmButtonColor: '#ff6347',
                        reverseButtons: true,
                        focusCancel: true,
                    });
                    confirmed = result.isConfirmed;
                } else {
                    confirmed = window.confirm('Clear this chat from this browser?');
                }

                if (!confirmed) {
                    return;
                }

                messages = [];
                saveMessages(messages);
                render(messages, false);
                input.focus();
            }

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                send(input.value);
            });

            input.addEventListener('input', () => {
                resizeInput();
                syncSendButton();
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    send(input.value);
                }
            });

            chips.forEach((chip) => {
                chip.addEventListener('click', () => {
                    send(chip.getAttribute('data-question') || '');
                });
            });

            clearButton.addEventListener('click', clearChat);

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState !== 'visible' || busy) {
                    return;
                }

                messages = loadMessages();
                render(messages, false);
            });

            render(messages, false);
            syncSendButton();

            if (window.matchMedia('(min-width: 768px)').matches) {
                input.focus();
            }
        }());
    </script>
@endsection
