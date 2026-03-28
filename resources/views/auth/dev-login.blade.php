<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Dev Login - AGS Break Tracker</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --navy-900: #0a1929;
                --navy-800: #0f2847;
                --navy-700: #143663;
                --navy-600: #1a4a8a;
                --green-500: #22c55e;
                --green-600: #16a34a;
                --red-500: #ef4444;
                --red-600: #dc2626;
                --text-primary: #0a1929;
                --text-secondary: #64748b;
                --border: #e2e8f0;
                --surface: #ffffff;
                --surface-alt: #f8fafc;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: var(--navy-900);
                color: var(--text-primary);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
            }

            .dev-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: rgba(255, 255, 255, 0.9);
                padding: 8px 16px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 32px;
            }
            .dev-badge::before {
                content: '';
                width: 8px;
                height: 8px;
                background: var(--green-500);
                border-radius: 50%;
                animation: blink 1.5s ease-in-out infinite;
            }
            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.3; }
            }

            .container {
                width: 100%;
                max-width: 900px;
            }

            .header {
                text-align: center;
                margin-bottom: 40px;
            }
            .header h1 {
                font-size: 28px;
                font-weight: 700;
                color: var(--white);
                margin-bottom: 8px;
            }
            .header p {
                color: rgba(255, 255, 255, 0.6);
                font-size: 14px;
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 16px;
            }

            .user-card {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 16px;
                padding: 24px;
                cursor: pointer;
                transition: all 0.2s ease;
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
                transition: opacity 0.2s ease;
            }
            .user-card:hover {
                border-color: var(--navy-700);
                transform: translateY(-3px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            }
            .user-card:hover::before {
                opacity: 1;
            }
            .user-card.admin::before { background: var(--red-500); }
            .user-card.tl::before { background: #f59e0b; }
            .user-card.agent::before { background: var(--green-500); }

            .user-card.admin:hover { border-color: var(--red-500); }
            .user-card.tl:hover { border-color: #f59e0b; }
            .user-card.agent:hover { border-color: var(--green-500); }

            .card-header {
                display: flex;
                align-items: center;
                gap: 14px;
                margin-bottom: 16px;
            }
            .avatar {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: 700;
                color: #fff;
            }
            .admin .avatar { background: linear-gradient(135deg, var(--red-500), #dc2626); }
            .tl .avatar { background: linear-gradient(135deg, #f59e0b, #d97706); }
            .agent .avatar { background: linear-gradient(135deg, var(--green-500), #16a34a); }

            .user-info h3 {
                font-size: 16px;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 4px;
            }
            .user-info .email {
                font-size: 12px;
                color: var(--text-secondary);
                font-family: 'SF Mono', Monaco, monospace;
            }
            .role-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 12px;
            }
            .admin .role-badge {
                background: rgba(239, 68, 68, 0.1);
                color: var(--red-600);
            }
            .tl .role-badge {
                background: rgba(245, 158, 11, 0.1);
                color: #b45309;
            }
            .agent .role-badge {
                background: rgba(34, 197, 94, 0.1);
                color: var(--green-600);
            }
            .totp-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 8px;
                margin-left: 6px;
                background: rgba(15, 40, 71, 0.1);
                color: var(--navy-700);
            }
            .dept {
                font-size: 12px;
                color: var(--text-secondary);
                margin-top: 8px;
            }
            .back-link {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: rgba(255, 255, 255, 0.6);
                text-decoration: none;
                font-size: 14px;
                margin-top: 32px;
                transition: color 0.2s ease;
            }
            .back-link:hover {
                color: rgba(255, 255, 255, 0.9);
            }
            .warning-banner {
                background: rgba(245, 158, 11, 0.15);
                border: 1px solid rgba(245, 158, 11, 0.3);
                border-radius: 12px;
                padding: 16px 20px;
                margin-bottom: 32px;
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 13px;
                color: #fbbf24;
            }
            .hidden-form { display: none; }
        </style>
    </head>
    <body>
        <div class="dev-badge">Development Mode</div>

        <div class="container">
            <div class="warning-banner">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                This is a development-only login. Do not expose this page in production.
            </div>

            <div class="header">
                <h1>Developer Login</h1>
                <p>Select a user to login as (no password required)</p>
            </div>

            <form id="devLoginForm" method="POST" action="{{ route('dev.login') }}" class="hidden-form">
                @csrf
                <input type="hidden" name="user_id" id="selectedUserId">
            </form>

            <div class="grid">
                @foreach($users as $user)
                <div class="user-card {{ $user->role }}"
                     onclick="document.getElementById('selectedUserId').value='{{ $user->id }}'; document.getElementById('devLoginForm').submit();">
                    <div class="card-header">
                        <div class="avatar">{{ substr($user->name, 0, 1) }}</div>
                        <div class="user-info">
                            <h3>{{ $user->name }}</h3>
                            <span class="email">{{ $user->email }}</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                        <span class="role-badge">{{ $user->role }}</span>
                        @if($user->totp_enabled)
                            <span class="totp-badge">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                                    <circle cx="12" cy="16" r="1"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                2FA
                            </span>
                        @endif
                    </div>
                    <div class="dept">{{ $user->department ?? 'No Department' }}</div>
                </div>
                @endforeach
            </div>

            <div style="text-align: center;">
                <a href="{{ route('login') }}" class="back-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back to regular login
                </a>
            </div>
        </div>
    </body>
</html>
