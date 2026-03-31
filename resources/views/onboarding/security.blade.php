<x-guest-layout>
    <div class="onboarding-header">
        <h1 class="form-title">You're All Set!</h1>
        <p class="form-desc">Your account is ready. You can complete your security setup anytime from your profile settings.</p>
    </div>

    <div class="security-summary">
        <div class="summary-card">
            <div class="summary-icon success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="summary-content">
                <h3>Profile Setup</h3>
                <p>Your basic information has been saved.</p>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <div class="summary-content">
                <h3>Two-Factor Authentication</h3>
                <p>Optional but recommended. Set it up anytime from your profile.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('onboarding.security.store') }}">
        @csrf
        <div class="form-footer-row" style="margin-top: 32px;">
            <a href="{{ route('onboarding.skip') }}" class="skip-link">Complete setup later</a>
            <button type="submit" class="submit-btn">
                Go to Dashboard
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</x-guest-layout>

<style>
    .onboarding-header {
        margin-bottom: 32px;
    }
    .security-summary {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .summary-card {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 20px;
        background: var(--surface-alt);
        border-radius: 12px;
        border: 1px solid var(--border);
    }
    .summary-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .summary-icon.success {
        background: rgba(21, 128, 61, 0.1);
        color: #15803d;
    }
    .summary-icon.info {
        background: rgba(20, 74, 138, 0.1);
        color: var(--navy-700);
    }
    .summary-content h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    .summary-content p {
        font-size: 13px;
        color: var(--text-secondary);
        margin: 0;
    }
    .skip-link {
        font-size: 14px;
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .skip-link:hover {
        color: var(--navy-700);
        text-decoration: underline;
    }
</style>
