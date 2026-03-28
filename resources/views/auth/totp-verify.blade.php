<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verify - AGS Break Tracker</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        <style>
            :root {
                --bg: #0a0a0f;
                --surface: #12121a;
                --surface-elevated: #1a1a24;
                --border: #2a2a3a;
                --text: #e4e4e7;
                --text-muted: #71717a;
                --accent: #6366f1;
                --accent-glow: rgba(99, 102, 241, 0.4);
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background: var(--bg);
                color: var(--text);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 24px;
                padding: 48px;
                max-width: 420px;
                width: 100%;
            }
            .dev-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: var(--surface-elevated);
                border: 1px solid var(--accent);
                color: var(--accent);
                padding: 6px 14px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 32px;
                box-shadow: 0 0 20px var(--accent-glow);
            }
            .dev-badge::before {
                content: '';
                width: 8px;
                height: 8px;
                background: var(--accent);
                border-radius: 50%;
                animation: blink 1.5s ease-in-out infinite;
            }
            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.3; }
            }
            .header {
                text-align: center;
                margin-bottom: 32px;
            }
            .icon {
                width: 72px;
                height: 72px;
                background: linear-gradient(135deg, var(--accent), #4f46e5);
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                box-shadow: 0 8px 32px var(--accent-glow);
            }
            h1 {
                font-size: 24px;
                font-weight: 700;
                color: var(--text);
                margin-bottom: 8px;
            }
            p {
                font-size: 14px;
                color: var(--text-muted);
                line-height: 1.6;
            }
            .user-info {
                display: flex;
                align-items: center;
                gap: 14px;
                background: var(--surface-elevated);
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 28px;
            }
            .avatar {
                width: 44px;
                height: 44px;
                border-radius: 10px;
                background: linear-gradient(135deg, var(--accent), #4f46e5);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: 700;
                color: #fff;
            }
            .user-details h3 {
                font-size: 15px;
                font-weight: 600;
                color: var(--text);
            }
            .user-details span {
                font-size: 12px;
                color: var(--text-muted);
                font-family: 'JetBrains Mono', monospace;
            }
            .form-group {
                margin-bottom: 24px;
            }
            label {
                display: block;
                font-size: 12px;
                font-weight: 600;
                color: var(--text-muted);
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 10px;
            }
            .code-input {
                width: 100%;
                padding: 18px 16px;
                border: 1.5px solid var(--border);
                border-radius: 14px;
                background: var(--surface-elevated);
                font-size: 22px;
                font-family: 'JetBrains Mono', monospace;
                text-align: center;
                letter-spacing: 8px;
                color: var(--text);
                transition: all 0.25s ease;
            }
            .code-input:focus {
                outline: none;
                border-color: var(--accent);
                box-shadow: 0 0 0 4px var(--accent-glow);
            }
            .code-input::placeholder {
                color: var(--text-muted);
                letter-spacing: 8px;
            }
            .btn {
                width: 100%;
                padding: 16px 24px;
                border: none;
                border-radius: 14px;
                font-size: 15px;
                font-weight: 700;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .btn-primary {
                background: linear-gradient(135deg, var(--accent), #4f46e5);
                color: #fff;
                box-shadow: 0 4px 20px var(--accent-glow);
            }
            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 32px var(--accent-glow);
            }
            .btn-primary:active {
                transform: translateY(-1px);
            }
            .btn svg {
                transition: transform 0.3s ease;
            }
            .btn-primary:hover svg {
                transform: translateX(4px);
            }
            .error-message {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #ef4444;
                padding: 14px 16px;
                border-radius: 12px;
                font-size: 13px;
                margin-bottom: 20px;
                display: none;
            }
            .error-message.show {
                display: block;
            }
            .back-link {
                display: block;
                text-align: center;
                margin-top: 24px;
                color: var(--text-muted);
                text-decoration: none;
                font-size: 14px;
                transition: color 0.2s ease;
            }
            .back-link:hover {
                color: var(--text);
            }
            .back-link svg {
                vertical-align: middle;
                margin-right: 6px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="dev-badge">2FA Verification</div>

            <div class="user-info">
                <div class="avatar">{{ substr($user->name, 0, 1) }}</div>
                <div class="user-details">
                    <h3>{{ $user->name }}</h3>
                    <span>{{ $user->email }}</span>
                </div>
            </div>

            <div class="header">
                <div class="icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <circle cx="12" cy="16" r="1"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h1>Two-Factor Authentication</h1>
                <p>Enter the 6-digit code from your Google Authenticator app</p>
            </div>

            <div id="errorMessage" class="error-message"></div>

            <form id="totpForm" method="POST" action="{{ route('dev.totp.verify') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <div class="form-group">
                    <label for="code">Verification Code</label>
                    <input type="text"
                           id="code"
                           name="code"
                           class="code-input"
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]*"
                           inputmode="numeric"
                           required autofocus>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>Verify & Login</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>

            <a href="{{ route('dev.login.show') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to user selection
            </a>
        </div>

        <script>
            const form = document.getElementById('totpForm');
            const codeInput = document.getElementById('code');
            const errorMessage = document.getElementById('errorMessage');

            // Auto-format: only allow numbers
            codeInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });

            // AJAX submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (codeInput.value.length !== 6) {
                    errorMessage.textContent = 'Please enter a 6-digit code';
                    errorMessage.classList.add('show');
                    return;
                }

                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.href = data.redirect || '{{ route("dashboard") }}';
                    } else {
                        errorMessage.textContent = data.message || 'Invalid verification code';
                        errorMessage.classList.add('show');
                        codeInput.value = '';
                        codeInput.focus();
                    }
                } catch (error) {
                    errorMessage.textContent = 'An error occurred. Please try again.';
                    errorMessage.classList.add('show');
                }
            });
        </script>
    </body>
</html>
