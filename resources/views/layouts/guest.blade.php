<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'AGS Break Tracker') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --navy-900: #0a1929;
                --navy-800: #0f2847;
                --navy-700: #143663;
                --navy-600: #1a4a8a;
                --text-primary: #0a1929;
                --text-secondary: #64748b;
                --border: #e2e8f0;
                --surface: #ffffff;
                --surface-alt: #f8fafc;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--surface); color: var(--text-primary); }

            .login-container {
                display: flex;
                min-height: 100vh;
                width: 100%;
            }

            /* Left Panel - Brand (Navy) */
            .brand-panel {
                flex: 1.2;
                background: var(--navy-900);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 80px 60px;
                position: relative;
                overflow: hidden;
            }
            .brand-panel::before {
                content: '';
                position: absolute;
                inset: 0;
                background:
                    radial-gradient(ellipse 80% 60% at 50% 40%, rgba(15, 40, 71, 0.8) 0%, transparent 70%),
                    radial-gradient(ellipse 60% 80% at 80% 80%, rgba(20, 54, 99, 0.4) 0%, transparent 60%);
                pointer-events: none;
            }
            .brand-panel::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 1px;
                height: 100%;
                background: linear-gradient(180deg, transparent, rgba(255,255,255,0.1), transparent);
            }
            .brand-content {
                position: relative;
                z-index: 1;
                text-align: center;
                max-width: 420px;
            }
            .brand-logo-wrapper {
                position: relative;
                display: inline-block;
                margin-bottom: 48px;
            }
            .brand-logo-glow {
                position: absolute;
                inset: -20px;
                background: radial-gradient(circle, rgba(15, 40, 71, 0.5) 0%, transparent 70%);
                border-radius: 50%;
                filter: blur(20px);
                animation: pulse 4s ease-in-out infinite;
            }
            @keyframes pulse {
                0%, 100% { opacity: 0.5; transform: scale(1); }
                50% { opacity: 0.8; transform: scale(1.05); }
            }
            .brand-logo {
                position: relative;
                width: 120px;
                height: 120px;
                object-fit: contain;
                border-radius: 24px;
                box-shadow: 0 8px 40px rgba(0, 0, 0, 0.3);
                animation: logoReveal 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            @keyframes logoReveal {
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            .brand-title {
                font-size: 32px;
                font-weight: 700;
                color: var(--white);
                margin-bottom: 16px;
                letter-spacing: -0.5px;
                animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
                opacity: 0;
                transform: translateY(20px);
            }
            .brand-subtitle {
                font-size: 15px;
                color: rgba(255, 255, 255, 0.7);
                line-height: 1.7;
                font-weight: 400;
                animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.4s forwards;
                opacity: 0;
                transform: translateY(20px);
            }
            @keyframes fadeUp {
                to { opacity: 1; transform: translateY(0); }
            }
            .brand-features {
                display: flex;
                gap: 32px;
                margin-top: 48px;
                justify-content: center;
                animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
                opacity: 0;
            }
            .brand-feature {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            .brand-feature-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: rgba(255, 255, 255, 0.1);
                display: flex;
                align-items: center;
                justify-content: center;
                color: rgba(255, 255, 255, 0.8);
            }
            .brand-feature-text {
                font-size: 12px;
                color: rgba(255, 255, 255, 0.6);
                font-weight: 500;
            }

            /* Right Panel - Form */
            .form-panel {
                width: 520px;
                background: var(--surface);
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 80px;
                position: relative;
            }
            .form-panel::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: var(--navy-800);
            }
            .form-inner {
                animation: slideIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                opacity: 0;
                transform: translateX(40px);
            }
            @keyframes slideIn {
                to { opacity: 1; transform: translateX(0); }
            }
            .form-header {
                margin-bottom: 40px;
            }
            .form-title {
                font-size: 26px;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 8px;
                letter-spacing: -0.3px;
            }
            .form-desc {
                font-size: 14px;
                color: var(--text-secondary);
            }
            .form-group {
                margin-bottom: 24px;
            }
            .form-label {
                display: block;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: 0.3px;
                color: var(--text-secondary);
                margin-bottom: 10px;
            }
            .form-input {
                width: 100%;
                min-height: 50px;
                padding: 0 18px;
                border-radius: 999px;
                border: 1.5px solid var(--border);
                background: var(--surface);
                font-size: 14px;
                font-family: inherit;
                color: var(--text-primary);
                transition: all 0.2s ease;
            }
            .form-input:focus {
                outline: none;
                border-color: var(--navy-700);
                box-shadow: 0 0 0 4px rgba(15, 40, 71, 0.08);
            }
            .form-input::placeholder {
                color: #94a3b8;
            }
            .google-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 14px;
                width: 100%;
                min-height: 50px;
                padding: 0 24px;
                border-radius: 999px;
                border: 1.5px solid var(--border);
                background: var(--surface);
                color: var(--text-primary);
                font-size: 14px;
                font-weight: 600;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.2s ease;
                text-decoration: none;
                margin-bottom: 8px;
            }
            .google-btn:hover {
                border-color: var(--navy-700);
                background: var(--surface-alt);
                transform: translateY(-1px);
            }
            .google-btn:active {
                transform: translateY(0);
            }
            .divider {
                display: flex;
                align-items: center;
                gap: 20px;
                margin: 28px 0;
            }
            .divider-line {
                flex: 1;
                height: 1px;
                background: var(--border);
            }
            .divider-text {
                font-size: 12px;
                color: var(--text-secondary);
                font-weight: 500;
            }
            .remember-me {
                accent-color: var(--navy-700);
                width: 18px;
                height: 18px;
                cursor: pointer;
            }
            .remember-text {
                font-size: 14px;
                color: var(--text-secondary);
            }
            .forgot-link {
                font-size: 14px;
                color: var(--navy-700);
                text-decoration: none;
                font-weight: 500;
                transition: color 0.2s ease;
            }
            .forgot-link:hover {
                color: var(--navy-600);
                text-decoration: underline;
            }
            .submit-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                width: 100%;
                min-height: 52px;
                padding: 0 28px;
                border-radius: 999px;
                background: var(--navy-800);
                color: var(--surface);
                font-size: 14px;
                font-weight: 600;
                font-family: inherit;
                border: none;
                cursor: pointer;
                transition: all 0.25s ease;
                box-shadow: 0 4px 14px rgba(15, 40, 71, 0.25);
                letter-spacing: 0.2px;
            }
            .submit-btn svg {
                transition: transform 0.25s ease;
            }
            .submit-btn:hover {
                background: var(--navy-700);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(15, 40, 71, 0.35);
            }
            .submit-btn:hover svg {
                transform: translateX(3px);
            }
            .submit-btn:active {
                transform: translateY(0);
            }
            .form-footer-links {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                margin-top: 28px;
                padding-top: 24px;
                border-top: 1px solid var(--border);
            }
            .register-link {
                font-size: 14px;
                color: var(--navy-700);
                text-decoration: none;
                font-weight: 600;
                transition: color 0.2s ease;
            }
            .register-link:hover {
                color: var(--navy-600);
                text-decoration: underline;
            }
            .error-message {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #dc2626;
                padding: 14px 18px;
                border-radius: 999px;
                font-size: 14px;
                margin-bottom: 24px;
            }
            .success-message {
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                color: #15803d;
                padding: 14px 18px;
                border-radius: 999px;
                font-size: 14px;
                margin-bottom: 24px;
            }

            /* Form Footer Row */
            .form-footer-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 24px;
            }

            /* Responsive */
            @media (max-width: 1024px) {
                .brand-panel { padding: 60px 40px; }
                .form-panel { width: 460px; padding: 60px; }
            }
            @media (max-width: 900px) {
                .login-container { flex-direction: column; }
                .brand-panel {
                    flex: none;
                    padding: 48px 24px;
                    min-height: auto;
                }
                .brand-logo { width: 100px; height: 100px; margin-bottom: 32px; }
                .brand-title { font-size: 26px; }
                .brand-features { gap: 24px; margin-top: 32px; }
                .form-panel {
                    width: 100%;
                    padding: 48px 24px;
                }
                .form-panel::before {
                    height: 3px;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="brand-panel">
                <div class="brand-content">
                    <div class="brand-logo-wrapper">
                        <div class="brand-logo-glow"></div>
                        <img src="https://res.cloudinary.com/dbviya1rj/image/upload/v1773384037/gpnkwelbdcwfjmw5axtx.webp"
                             alt="AGS Logo"
                             class="brand-logo"
                             loading="eager"
                             fetchpriority="high">
                    </div>
                    <h1 class="brand-title">AGS Break Tracker</h1>
                    <p class="brand-subtitle">Empower your team with seamless break management. Track, monitor, and optimize productivity.</p>
                    <div class="brand-features">
                        <div class="brand-feature">
                            <div class="brand-feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <span class="brand-feature-text">Real-time</span>
                        </div>
                        <div class="brand-feature">
                            <div class="brand-feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <span class="brand-feature-text">Team Focus</span>
                        </div>
                        <div class="brand-feature">
                            <div class="brand-feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </div>
                            <span class="brand-feature-text">Secure</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-panel">
                <div class="form-inner">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
