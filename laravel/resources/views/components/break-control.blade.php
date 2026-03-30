@props(['activeBreak' => null])

<style>
    /* Break Control - Matches Dashboard Theme */
    .break-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        overflow: hidden;
    }

    .break-card-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(180deg, #fff 0%, var(--gray-50) 100%);
    }

    .break-card-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
    }

    /* Status Tag */
    .status-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .status-tag .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-tag.ready {
        background: var(--green-50);
        color: var(--green-600);
    }
    .status-tag.ready .dot {
        background: var(--green-600);
    }

    .status-tag.on-break {
        background: var(--gray-100);
        color: var(--gray-700);
    }
    .status-tag.on-break .dot {
        background: var(--gray-600);
        animation: pulse 1.5s infinite;
    }

    .status-tag.overbreak {
        background: var(--red-50);
        color: var(--red-600);
    }
    .status-tag.overbreak .dot {
        background: var(--red-600);
        animation: pulse 0.8s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Card Body */
    .break-card-body {
        padding: 16px;
    }

    /* Status Display */
    .break-status-display {
        padding: 16px;
        background: var(--gray-50);
        border-radius: 6px;
        margin-bottom: 16px;
        text-align: center;
    }

    .break-status-display.on-break {
        background: linear-gradient(135deg, var(--gray-50) 0%, #e8f5e9 100%);
        border: 1px solid #c8e6c9;
    }

    .break-status-display.overbreak {
        background: var(--red-50);
        border: 1px solid #fecaca;
    }

    .break-status-display strong {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 2px;
    }

    .break-status-display p {
        font-size: 12px;
        color: var(--gray-500);
        margin: 0;
    }

    /* Timer Section */
    .break-timer-section {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
        padding: 12px;
        background: var(--gray-50);
        border-radius: 6px;
    }

    .digital-timer {
        width: 90px;
        height: 90px;
        flex-shrink: 0;
        background: linear-gradient(180deg, #1a1a1a 0%, #0d0d0d 100%);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--gray-700);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255,255,255,0.05);
    }

    .digital-timer.on-break {
        border-color: var(--gray-600);
    }

    .digital-timer.overbreak {
        border-color: var(--red-600);
        animation: timer-danger 0.5s ease-in-out infinite;
    }

    @keyframes timer-danger {
        0%, 100% { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255,255,255,0.05), 0 0 0 rgba(220, 38, 38, 0); }
        50% { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255,255,255,0.05), 0 0 20px rgba(220, 38, 38, 0.5); }
    }

    .digital-display {
        font-family: 'JetBrains Mono', monospace;
        font-size: 24px;
        font-weight: 700;
        color: var(--green-600);
        letter-spacing: 2px;
        text-shadow: 0 0 10px currentColor;
    }

    .digital-timer.on-break .digital-display {
        color: var(--gray-400);
        text-shadow: none;
    }

    .digital-timer.overbreak .digital-display {
        color: var(--red-600);
        text-shadow: 0 0 10px var(--red-600);
    }

    .digital-label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 8px;
        font-weight: 600;
        color: var(--gray-500);
        letter-spacing: 1px;
        margin-top: 2px;
    }

    .digital-timer.overbreak .digital-label {
        color: var(--red-600);
    }

    .timer-info {
        flex: 1;
    }

    .timer-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .timer-info-row:last-child {
        border-bottom: none;
    }

    .timer-info-label {
        font-size: 12px;
        color: var(--gray-500);
    }

    .timer-info-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
    }

    /* Action Buttons */
    .break-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .btn-break {
        width: 100%;
        height: 42px;
        background: var(--navy-800);
        color: var(--white);
        border: none;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0.02em;
        cursor: pointer;
        transition: all 0.15s ease;
        display: block;
        text-align: center;
        line-height: 42px;
    }

    .btn-break:hover {
        background: var(--navy-700);
        transform: translateY(-1px);
    }

    .btn-break:active {
        transform: translateY(0);
    }

    .btn-break.secondary {
        background: var(--gray-600);
    }

    .btn-break.secondary:hover {
        background: var(--gray-500);
    }

    .btn-break.end {
        background: var(--red-600);
        grid-column: 1 / -1;
    }

    .btn-break.end:hover {
        background: #b91c1c;
    }

    .btn-break:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Break Type Selector */
    .break-type-selector {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .break-type-btn {
        flex: 1;
        height: 38px;
        background: var(--gray-100);
        color: var(--gray-600);
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .break-type-btn:hover {
        background: var(--gray-50);
        border-color: var(--gray-300);
    }

    .break-type-btn.active {
        background: var(--navy-800);
        color: var(--white);
        border-color: var(--navy-800);
    }

    .break-type-btn.active:hover {
        background: var(--navy-700);
    }

    .break-type-btn svg {
        width: 16px;
        height: 16px;
    }

    /* Responsive */
    @media (max-width: 400px) {
        .break-timer-section {
            flex-direction: column;
            text-align: center;
        }

        .break-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="break-card">
    <div class="break-card-header">
        <h2 class="break-card-title">Break Control</h2>
        <span id="current-status-tag" class="status-tag {{ $activeBreak ? 'on-break' : 'ready' }}">
            <span class="dot"></span>
            <span>{{ $activeBreak ? 'On Break' : 'Ready' }}</span>
        </span>
    </div>

    <div class="break-card-body">
        <!-- Timer Section -->
        <div class="break-timer-section">
            <div class="digital-timer {{ $activeBreak ? 'on-break' : '' }}" id="timer-circle">
                <div class="digital-display" id="timer-display">00:00</div>
                <div class="digital-label" id="timer-label">READY</div>
            </div>
            <div class="timer-info">
                <div class="timer-info-row">
                    <span class="timer-info-label">Break Type</span>
                    <span class="timer-info-value" id="current-break-type">{{ $activeBreak ? ucfirst($activeBreak->break_category) . ' (' . $activeBreak->break_type . ')' : '—' }}</span>
                </div>
                <div class="timer-info-row">
                    <span class="timer-info-label">Expected Return</span>
                    <span class="timer-info-value" id="current-break-return">{{ $activeBreak ? $activeBreak->expected_end_at?->format('H:i') : '—' }}</span>
                </div>
                <div class="timer-info-row">
                    <span class="timer-info-label">Elapsed</span>
                    <span class="timer-info-value" id="current-break-elapsed">—</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        @if(!$activeBreak)
        <!-- Break Type Selector -->
        <div class="break-type-selector">
            <button type="button" class="break-type-btn active" data-type="break" onclick="selectBreakType('break')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Break
            </button>
            <button type="button" class="break-type-btn" data-type="lunch" onclick="selectBreakType('lunch')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m0-3a5 5 0 11-10 0 5 5 0 0110 0z" />
                </svg>
                Lunch
            </button>
        </div>

        <input type="hidden" name="break_category" id="break-category-input" value="break">

        <div class="break-actions">
            <form method="POST" action="{{ route('break.start') }}" id="start-15-form">
                @csrf
                <input type="hidden" name="type" value="15m">
                <input type="hidden" name="break_category" id="15m-category" value="break">
                <button type="submit" class="btn-break">15 Min</button>
            </form>
            <form method="POST" action="{{ route('break.start') }}" id="start-60-form">
                @csrf
                <input type="hidden" name="type" value="60m">
                <input type="hidden" name="break_category" id="60m-category" value="break">
                <button type="submit" class="btn-break secondary">1 Hour</button>
            </form>
        </div>

        <script>
            function selectBreakType(type) {
                document.querySelectorAll('.break-type-btn').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.type === type);
                });
                document.getElementById('break-category-input').value = type;
                document.getElementById('15m-category').value = type;
                document.getElementById('60m-category').value = type;
            }
        </script>
        @else
        <form method="POST" action="{{ route('break.end') }}" id="end-break-form">
            @csrf
            <button type="submit" class="btn-break end">End Break Now</button>
        </form>
        @endif
    </div>
</div>
