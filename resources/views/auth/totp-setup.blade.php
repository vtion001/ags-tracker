<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Setup Google Authenticator</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: #ffffff;
                border-radius: 24px;
                padding: 48px;
                max-width: 440px;
                width: 100%;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            }
            .header {
                text-align: center;
                margin-bottom: 32px;
            }
            .icon {
                width: 64px;
                height: 64px;
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                border-radius: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                color: #fff;
            }
            h1 {
                font-size: 24px;
                font-weight: 700;
                color: #172235;
                margin-bottom: 8px;
            }
            p {
                font-size: 14px;
                color: #64748b;
                line-height: 1.6;
            }
            .qr-container {
                background: #f8fafc;
                border-radius: 16px;
                padding: 24px;
                text-align: center;
                margin: 24px 0;
            }
            .qr-code {
                width: 200px;
                height: 200px;
                background: #fff;
                border-radius: 12px;
                padding: 8px;
                margin: 0 auto 16px;
                display: block;
            }
            .secret {
                font-family: 'JetBrains Mono', monospace;
                font-size: 13px;
                color: #64748b;
                background: #fff;
                padding: 12px 16px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
                word-break: break-all;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 8px;
            }
            input {
                width: 100%;
                padding: 14px 16px;
                border: 1.5px solid #d9e2ef;
                border-radius: 12px;
                font-size: 16px;
                font-family: inherit;
                text-align: center;
                letter-spacing: 4px;
                transition: all 0.2s ease;
            }
            input:focus {
                outline: none;
                border-color: #2563eb;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            }
            .btn {
                width: 100%;
                padding: 14px 24px;
                border: none;
                border-radius: 12px;
                font-size: 15px;
                font-weight: 700;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .btn-primary {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                color: #fff;
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            }
            .alert {
                padding: 14px 16px;
                border-radius: 12px;
                font-size: 14px;
                margin-bottom: 20px;
            }
            .alert-success {
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                color: #15803d;
            }
            .alert-error {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #dc2626;
            }
            .back-link {
                display: block;
                text-align: center;
                margin-top: 20px;
                color: #64748b;
                text-decoration: none;
                font-size: 14px;
            }
            .back-link:hover {
                color: #2563eb;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <circle cx="12" cy="16" r="1"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h1>Set Up Google Authenticator</h1>
                <p>Scan the QR code with your Google Authenticator app to secure your account.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <div class="qr-container">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}"
                     alt="QR Code"
                     class="qr-code">
                <p style="font-size: 12px; color: #64748b; margin-top: 12px;">Or enter this code manually:</p>
                <div class="secret">{{ $secret }}</div>
            </div>

            <form method="POST" action="{{ route('totp.setup') }}">
                @csrf
                <div class="form-group">
                    <label for="code">Verification Code</label>
                    <input type="text"
                           id="code"
                           name="code"
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]*"
                           inputmode="numeric"
                           required autofocus>
                </div>
                <button type="submit" class="btn btn-primary">Verify & Enable</button>
            </form>

            <a href="{{ route('dashboard') }}" class="back-link">Skip for now</a>
        </div>
    </body>
</html>
