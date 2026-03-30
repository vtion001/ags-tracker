<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>⚡ GOD MODE ⚡ - AGS Break Tracker</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --bg-primary: #0a0a0f;
                --bg-secondary: #12121a;
                --bg-card: #1a1a24;
                --border: #2a2a3a;
                --text-primary: #ffffff;
                --text-secondary: #8888aa;
                --text-muted: #555566;
                --accent: #00ff88;
                --accent-glow: rgba(0, 255, 136, 0.3);
                --admin: #ff3366;
                --admin-glow: rgba(255, 51, 102, 0.3);
                --tl: #ffaa00;
                --tl-glow: rgba(255, 170, 0, 0.3);
                --agent: #00ccff;
                --agent-glow: rgba(0, 204, 255, 0.3);
            }

            * { box-sizing: border-box; margin: 0; padding: 0; }

            body {
                font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
                background: var(--bg-primary);
                color: var(--text-primary);
                min-height: 100vh;
                overflow-x: hidden;
            }

            /* Animated background grid */
            body::before {
                content: '';
                position: fixed;
                inset: 0;
                background-image:
                    linear-gradient(var(--border) 1px, transparent 1px),
                    linear-gradient(90deg, var(--border) 1px, transparent 1px);
                background-size: 50px 50px;
                opacity: 0.3;
                pointer-events: none;
                z-index: 0;
            }

            .container {
                position: relative;
                z-index: 1;
                max-width: 1200px;
                margin: 0 auto;
                padding: 60px 24px;
            }

            /* Header */
            .god-header {
                text-align: center;
                margin-bottom: 60px;
            }

            .god-badge {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                padding: 12px 24px;
                background: linear-gradient(135deg, var(--accent), #00cc66);
                border-radius: 999px;
                font-family: 'JetBrains Mono', monospace;
                font-size: 14px;
                font-weight: 700;
                letter-spacing: 2px;
                color: #000;
                margin-bottom: 24px;
                animation: pulse-glow 2s ease-in-out infinite;
            }

            @keyframes pulse-glow {
                0%, 100% { box-shadow: 0 0 20px var(--accent-glow), 0 0 40px var(--accent-glow); }
                50% { box-shadow: 0 0 30px var(--accent-glow), 0 0 60px var(--accent-glow); }
            }

            .god-title {
                font-size: 48px;
                font-weight: 700;
                letter-spacing: -1px;
                margin-bottom: 12px;
                background: linear-gradient(135deg, var(--text-primary), var(--accent));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .god-subtitle {
                font-size: 16px;
                color: var(--text-secondary);
            }

            .back-link {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: var(--text-muted);
                text-decoration: none;
                font-size: 14px;
                margin-top: 24px;
                transition: color 0.2s ease;
            }

            .back-link:hover {
                color: var(--accent);
            }

            /* Role Sections */
            .role-section {
                margin-bottom: 48px;
            }

            .role-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 20px;
            }

            .role-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }

            .role-icon.admin {
                background: var(--admin);
                box-shadow: 0 0 20px var(--admin-glow);
            }

            .role-icon.tl {
                background: var(--tl);
                box-shadow: 0 0 20px var(--tl-glow);
            }

            .role-icon.agent {
                background: var(--agent);
                box-shadow: 0 0 20px var(--agent-glow);
            }

            .role-title {
                font-size: 20px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .role-title.admin { color: var(--admin); }
            .role-title.tl { color: var(--tl); }
            .role-title.agent { color: var(--agent); }

            /* User Grid */
            .user-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 16px;
            }

            /* User Card */
            .user-card {
                background: var(--bg-card);
                border: 1px solid var(--border);
                border-radius: 16px;
                padding: 20px;
                cursor: pointer;
                transition: all 0.25s ease;
                position: relative;
                overflow: hidden;
            }

            .user-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                opacity: 0;
                transition: opacity 0.25s ease;
            }

            .user-card.admin::before { background: var(--admin); }
            .user-card.tl::before { background: var(--tl); }
            .user-card.agent::before { background: var(--agent); }

            .user-card:hover {
                transform: translateY(-4px);
                border-color: var(--accent);
                box-shadow: 0 8px 32px rgba(0, 255, 136, 0.15);
            }

            .user-card:hover::before {
                opacity: 1;
            }

            .user-card-header {
                display: flex;
                align-items: center;
                gap: 14px;
                margin-bottom: 14px;
            }

            .avatar {
                width: 48px;
                height: 48px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'JetBrains Mono', monospace;
                font-size: 18px;
                font-weight: 700;
                color: #000;
            }

            .admin .avatar { background: var(--admin); }
            .tl .avatar { background: var(--tl); }
            .agent .avatar { background: var(--agent); }

            .user-info h3 {
                font-size: 15px;
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 4px;
            }

            .user-email {
                font-size: 12px;
                font-family: 'JetBrains Mono', monospace;
                color: var(--text-muted);
            }

            .user-meta {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .badge {
                display: inline-flex;
                align-items: center;
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .badge.admin {
                background: rgba(255, 51, 102, 0.15);
                color: var(--admin);
            }

            .badge.tl {
                background: rgba(255, 170, 0, 0.15);
                color: var(--tl);
            }

            .badge.agent {
                background: rgba(0, 204, 255, 0.15);
                color: var(--agent);
            }

            .badge.totp {
                background: rgba(0, 255, 136, 0.15);
                color: var(--accent);
            }

            .dept {
                font-size: 12px;
                color: var(--text-secondary);
                margin-top: 10px;
            }

            /* Hidden form */
            .hidden-form { display: none; }

            /* Selection indicator */
            .user-card.selected {
                border-color: var(--accent);
                background: rgba(0, 255, 136, 0.05);
            }

            .login-btn {
                display: none;
                width: 100%;
                padding: 14px;
                margin-top: 20px;
                background: linear-gradient(135deg, var(--accent), #00cc66);
                border: none;
                border-radius: 12px;
                color: #000;
                font-family: 'Space Grotesk', sans-serif;
                font-size: 14px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .login-btn:hover {
                transform: scale(1.02);
                box-shadow: 0 4px 20px var(--accent-glow);
            }

            .login-btn.visible {
                display: block;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .god-title { font-size: 32px; }
                .user-grid { grid-template-columns: 1fr; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="god-header">
                <div class="god-badge">⚡ DEVELOPER MODE ⚡</div>
                <h1 class="god-title">GOD MODE</h1>
                <p class="god-subtitle">Select any user to instantly login as them (no password, no TOTP)</p>
                <a href="{{ route('login') }}" class="back-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back to Login
                </a>
            </div>

            <form id="godLoginForm" method="POST" action="{{ route('god.login') }}" class="hidden-form">
                @csrf
                <input type="hidden" name="user_id" id="selectedUserId">
            </form>

            <!-- Admins -->
            @if($groupedUsers['admin']->count() > 0)
            <section class="role-section">
                <div class="role-header">
                    <div class="role-icon admin">👑</div>
                    <h2 class="role-title admin">Administrators</h2>
                </div>
                <div class="user-grid">
                    @foreach($groupedUsers['admin'] as $user)
                    <div class="user-card admin" onclick="selectUser('{{ $user->id }}', this)">
                        <div class="user-card-header">
                            <div class="avatar">{{ substr($user->name, 0, 1) }}</div>
                            <div class="user-info">
                                <h3>{{ $user->name }}</h3>
                                <span class="user-email">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="user-meta">
                            <span class="badge admin">Admin</span>
                            @if($user->totp_enabled)
                                <span class="badge totp">2FA ON</span>
                            @endif
                        </div>
                        <div class="dept">{{ $user->department ?? 'No Department' }}</div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Team Leads -->
            @if($groupedUsers['team_lead']->count() > 0)
            <section class="role-section">
                <div class="role-header">
                    <div class="role-icon tl">⭐</div>
                    <h2 class="role-title tl">Team Leaders</h2>
                </div>
                <div class="user-grid">
                    @foreach($groupedUsers['team_lead'] as $user)
                    <div class="user-card tl" onclick="selectUser('{{ $user->id }}', this)">
                        <div class="user-card-header">
                            <div class="avatar">{{ substr($user->name, 0, 1) }}</div>
                            <div class="user-info">
                                <h3>{{ $user->name }}</h3>
                                <span class="user-email">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="user-meta">
                            <span class="badge tl">Team Lead</span>
                            @if($user->totp_enabled)
                                <span class="badge totp">2FA ON</span>
                            @endif
                        </div>
                        <div class="dept">{{ $user->department ?? 'No Department' }}</div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Agents -->
            @if($groupedUsers['agent']->count() > 0)
            <section class="role-section">
                <div class="role-header">
                    <div class="role-icon agent">👤</div>
                    <h2 class="role-title agent">Agents</h2>
                </div>
                <div class="user-grid">
                    @foreach($groupedUsers['agent'] as $user)
                    <div class="user-card agent" onclick="selectUser('{{ $user->id }}', this)">
                        <div class="user-card-header">
                            <div class="avatar">{{ substr($user->name, 0, 1) }}</div>
                            <div class="user-info">
                                <h3>{{ $user->name }}</h3>
                                <span class="user-email">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="user-meta">
                            <span class="badge agent">Agent</span>
                            @if($user->totp_enabled)
                                <span class="badge totp">2FA ON</span>
                            @endif
                        </div>
                        <div class="dept">{{ $user->department ?? 'No Department' }}</div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Login Button -->
            <button type="button" id="loginBtn" class="login-btn" onclick="submitLogin()">
                ⚡ LOGIN AS SELECTED USER ⚡
            </button>
        </div>

        <script>
            let selectedUserId = null;
            let selectedElement = null;

            function selectUser(userId, element) {
                // Remove previous selection
                document.querySelectorAll('.user-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Add selection to clicked card
                element.classList.add('selected');
                selectedUserId = userId;
                selectedElement = element;

                // Show login button
                document.getElementById('loginBtn').classList.add('visible');
            }

            function submitLogin() {
                if (selectedUserId) {
                    document.getElementById('selectedUserId').value = selectedUserId;
                    document.getElementById('godLoginForm').submit();
                }
            }

            // Allow keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && selectedUserId) {
                    submitLogin();
                }
            });
        </script>
    </body>
</html>
