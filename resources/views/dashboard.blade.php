<x-app-layout>
@php
    $activeBreak = $activeBreak ?? null;
    $stats = $stats ?? ['count_15m' => 0, 'count_60m' => 0, 'overbreaks_count' => 0, 'total_over_minutes' => 0, 'overbreaks_15m' => 0, 'overbreaks_60m' => 0];
    $teamBreaks = $teamBreaks ?? collect();
    $teamHistory = $teamHistory ?? collect();
    $teamOverbreaks = $teamOverbreaks ?? collect();
    $allActiveBreaks = $allActiveBreaks ?? collect();
    $allHistory = $allHistory ?? collect();
    $myHistory = $myHistory ?? collect();
    $performance = $performance ?? ['compliance' => 100, 'daily_breaks' => 0, 'weekly_total' => 0, 'weekly_overbreaks' => 0, 'avg_15m' => 0, 'avg_60m' => 0, 'score' => 100];
    $peerStats = $peerStats ?? ['my' => ['count_15m' => 0, 'count_60m' => 0, 'overbreaks' => 0, 'compliance' => 100], 'team' => ['count_15m_avg' => 0, 'count_60m_avg' => 0, 'overbreaks_avg' => 0, 'compliance' => 100, 'percentile' => 0], 'department' => ['count_15m_avg' => 0, 'count_60m_avg' => 0, 'overbreaks_avg' => 0, 'compliance' => 100]];
@endphp

<style>
    :root {
        --black: #000000;
        --navy-900: #0a1929;
        --navy-800: #0f2847;
        --navy-700: #143663;
        --gray-950: #0a1929;
        --gray-900: #171717;
        --gray-800: #262626;
        --gray-700: #374151;
        --gray-600: #4b5563;
        --gray-500: #6b7280;
        --gray-400: #9ca3af;
        --gray-300: #d1d5db;
        --gray-200: #e5e7eb;
        --gray-100: #f3f4f6;
        --gray-50: #f9fafb;
        --white: #ffffff;
        --green-600: #16a34a;
        --green-50: #f0fdf4;
        --red-600: #dc2626;
        --red-50: #fef2f2;
        --yellow-600: #ca8a04;
        --yellow-500: #eab308;
        --yellow-50: #fefce8;
        --orange-500: #f97316;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 14px; }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: var(--gray-100);
        color: var(--gray-900);
        min-height: 100vh;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    /* Layout */
    .app-layout {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: 240px;
        background: var(--gray-950);
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--gray-800);
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-logo {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }

    .sidebar-brand h1 {
        font-size: 15px;
        font-weight: 600;
        color: var(--white);
        letter-spacing: -0.01em;
    }

    .sidebar-nav {
        flex: 1;
        padding: 16px 12px;
    }

    .nav-section {
        margin-bottom: 24px;
    }

    .nav-section-title {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gray-500);
        padding: 0 12px;
        margin-bottom: 8px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 6px;
        color: var(--gray-400);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.15s ease;
        margin-bottom: 2px;
    }

    .nav-item:hover {
        background: var(--gray-800);
        color: var(--white);
    }

    .nav-item.active {
        background: var(--gray-800);
        color: var(--white);
    }

    .nav-item svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        opacity: 0.7;
    }

    .nav-item:hover svg,
    .nav-item.active svg {
        opacity: 1;
    }

    .sidebar-footer {
        padding: 16px;
        border-top: 1px solid var(--gray-800);
    }

    .user-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        border-radius: 6px;
        background: var(--gray-900);
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--gray-700);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--white);
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-size: 12px;
        font-weight: 500;
        color: var(--white);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 11px;
        color: var(--gray-500);
    }

    /* Main Content */
    .main-content {
        flex: 1;
        margin-left: 240px;
        display: flex;
        flex-direction: column;
    }

    /* Top Bar */
    .topbar {
        height: 56px;
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 24px;
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .page-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--green-600);
        font-weight: 500;
    }

    .status-indicator::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--green-600);
    }

    /* Page Content */
    .page-content {
        flex: 1;
        padding: 24px;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 24px;
    }

    .page-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 4px;
    }

    .page-header p {
        font-size: 13px;
        color: var(--gray-500);
    }

    /* Alert Banners */
    .alert {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        background: var(--red-50);
        border: 1px solid #fecaca;
    }

    .alert.show { display: flex; }

    .alert-content strong {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--red-600);
    }

    .alert-content p {
        font-size: 12px;
        color: var(--red-600);
    }

    .btn-alert-dismiss {
        background: transparent;
        border: 1px solid var(--gray-300);
        color: var(--gray-600);
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-alert-dismiss:hover {
        background: var(--gray-100);
    }

    /* Cards */
    .card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
    }

    .card-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .card-body {
        padding: 16px;
    }

    /* User Info Grid */
    .user-info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .user-info-item {
        padding: 12px;
        background: var(--gray-50);
        border-radius: 6px;
    }

    .user-info-item label {
        display: block;
        font-size: 11px;
        font-weight: 500;
        color: var(--gray-500);
        margin-bottom: 4px;
    }

    .user-info-item span {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
    }

    /* Break Control */
    .break-control-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .break-status-display {
        padding: 16px;
        background: var(--gray-50);
        border-radius: 6px;
        margin-bottom: 16px;
    }

    .break-status-display.on-break {
        background: var(--gray-100);
        border: 1px solid var(--gray-200);
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

    .break-status-display.overbreak strong {
        color: var(--red-600);
    }

    .break-status-display p {
        font-size: 12px;
        color: var(--gray-500);
    }

    .break-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }

    .break-meta-item {
        padding: 10px 12px;
        background: var(--gray-50);
        border-radius: 6px;
    }

    .break-meta-item label {
        display: block;
        font-size: 10px;
        font-weight: 500;
        color: var(--gray-500);
        margin-bottom: 2px;
    }

    .break-meta-item span {
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .break-actions {
        display: flex;
        gap: 8px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 36px;
        padding: 0 18px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--navy-800);
        color: var(--white);
        border-color: var(--navy-800);
    }

    .btn-primary:hover {
        background: var(--navy-700);
    }

    .btn-secondary {
        background: var(--white);
        color: var(--gray-700);
        border-color: var(--gray-300);
    }

    .btn-secondary:hover {
        background: var(--gray-50);
    }

    .btn-danger {
        background: var(--red-600);
        color: var(--white);
        border-color: var(--red-600);
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    .btn-break {
        flex: 1;
        height: 42px;
        background: var(--navy-800);
        color: var(--white);
        border: none;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0.02em;
    }

    .btn-break:hover {
        background: var(--navy-700);
    }

    .btn-break.end {
        background: var(--red-600);
        border-radius: 999px;
    }

    .btn-break.end:hover {
        background: #b91c1c;
    }

    .btn-break.secondary {
        background: var(--gray-600);
        border-radius: 999px;
    }

    .btn-break.secondary:hover {
        background: var(--gray-500);
    }

    /* Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .stat-card {
        padding: 14px;
        background: var(--gray-50);
        border-radius: 6px;
        border: 1px solid var(--gray-200);
    }

    .stat-card.over {
        border-color: #fecaca;
        background: var(--red-50);
    }

    .stat-card .value {
        font-size: 24px;
        font-weight: 600;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-card.over .value {
        color: var(--red-600);
    }

    .stat-card .label {
        font-size: 11px;
        font-weight: 500;
        color: var(--gray-500);
    }

    /* Tables */
    .table-wrapper {
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        overflow: hidden;
        background: var(--white);
    }

    .table-scroll {
        max-height: 280px;
        overflow-y: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    thead {
        position: sticky;
        top: 0;
        z-index: 5;
        background: var(--gray-50);
    }

    th {
        padding: 10px 12px;
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        background: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
        white-space: nowrap;
    }

    td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--gray-100);
        color: var(--gray-700);
    }

    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--gray-50); }

    /* Tags */
    .tag {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }

    .tag-ok {
        background: var(--green-50);
        color: var(--green-600);
    }

    .tag-over {
        background: var(--red-50);
        color: var(--red-600);
    }

    .tag-active {
        background: var(--gray-100);
        color: var(--gray-600);
    }

    /* Section */
    .section {
        margin-bottom: 20px;
    }

    .section:last-child { margin-bottom: 0; }

    /* History Split */
    .history-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .history-block {
        padding: 12px;
        background: var(--gray-50);
        border-radius: 6px;
    }

    .history-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--gray-500);
        margin-bottom: 10px;
    }

    .history-block.over .history-label {
        color: var(--red-600);
    }

    /* Form */
    .form-row {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 200px;
    }

    .field label {
        font-size: 11px;
        font-weight: 500;
        color: var(--gray-600);
    }

    .field input {
        height: 36px;
        padding: 0 12px;
        border-radius: 6px;
        border: 1px solid var(--gray-300);
        background: var(--white);
        color: var(--gray-900);
        font-size: 13px;
        transition: all 0.15s;
    }

    .field input:focus {
        outline: none;
        border-color: var(--black);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
    }

    /* Empty state */
    .empty-state {
        padding: 24px;
        text-align: center;
        color: var(--gray-500);
        font-size: 12px;
    }

    /* Toast */
    .toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 1000;
        padding: 12px 16px;
        border-radius: 6px;
        background: var(--gray-900);
        color: var(--white);
        font-size: 13px;
        display: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .toast.show { display: block; }
    .toast.error { background: var(--red-600); }

    /* Status Tag in Header */
    .status-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        background: var(--gray-100);
        color: var(--gray-600);
    }

    .status-tag .dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--gray-400);
    }

    .status-tag.ready {
        background: var(--green-50);
        color: var(--green-600);
    }

    .status-tag.ready .dot {
        background: var(--green-600);
    }

    .status-tag.overbreak {
        background: var(--red-50);
        color: var(--red-600);
    }

    .status-tag.overbreak .dot {
        background: var(--red-600);
    }

    .status-tag.warning {
        background: var(--yellow-50);
        color: var(--yellow-600);
    }

    .status-tag.warning .dot {
        background: var(--yellow-600);
        animation: pulse 1s infinite;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .sidebar { width: 72px; }
        .sidebar-brand h1, .nav-item span, .nav-section-title, .user-info { display: none; }
        .sidebar-header { padding: 16px 12px; }
        .nav-item { justify-content: center; padding: 12px; }
        .user-card { justify-content: center; }
        .main-content { margin-left: 72px; }
        .break-control-grid { grid-template-columns: 1fr; }
        .user-info-grid { grid-template-columns: repeat(2, 1fr); }
        .history-split { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 640px) {
        .sidebar { display: none; }
        .main-content { margin-left: 0; }
        .topbar { padding: 0 16px; }
        .page-content { padding: 16px; }
        .user-info-grid { grid-template-columns: 1fr; }
        .break-actions { flex-direction: column; }
        .form-row { flex-direction: column; }
        .field { width: 100%; }
    }

    /* Profile Overview (Enhanced User Info Card) */
    .profile-overview-grid {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 24px;
        align-items: start;
    }

    .profile-identity {
        text-align: center;
    }

    .profile-avatar-lg {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        background: var(--navy-800);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 26px;
        font-weight: 700;
        color: var(--white);
    }

    .profile-name-lg {
        font-size: 15px;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 4px;
    }

    .profile-role-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        background: var(--gray-100);
        color: var(--gray-600);
        margin-bottom: 6px;
    }

    .profile-dept {
        font-size: 12px;
        color: var(--gray-500);
    }

    .profile-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 24px;
    }

    .profile-detail-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .detail-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
    }

    .detail-value {
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-900);
    }

    .location-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .location-badge.location-office {
        background: var(--gray-100);
        color: var(--gray-700);
    }

    .location-badge.location-wfh {
        background: var(--green-50);
        color: var(--green-600);
    }

    .location-badge.location-hybrid {
        background: var(--yellow-50);
        color: var(--yellow-600);
    }

    .tl-email-link {
        color: var(--navy-800);
        text-decoration: none;
        font-weight: 500;
    }

    .tl-email-link:hover {
        text-decoration: underline;
    }

    /* Performance Metrics Card */
    .performance-grid {
        display: grid;
        grid-template-columns: 90px 1fr 100px;
        gap: 20px;
        align-items: center;
    }

    .perf-ring-col {
        text-align: center;
    }

    .perf-ring {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto 6px;
    }

    .ring-svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .ring-bg {
        fill: none;
        stroke: var(--gray-200);
        stroke-width: 3;
    }

    .ring-fill {
        fill: none;
        stroke: var(--green-600);
        stroke-width: 3;
        stroke-linecap: round;
        transition: stroke-dasharray 0.6s ease, stroke 0.3s ease;
    }

    .ring-fill.warn {
        stroke: var(--yellow-600);
    }

    .ring-fill.danger {
        stroke: var(--red-600);
    }

    .ring-label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1px;
    }

    .ring-pct {
        font-size: 18px;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
    }

    .ring-sub {
        font-size: 10px;
        font-weight: 600;
        color: var(--gray-500);
        margin-top: 2px;
    }

    .perf-ring-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .perf-metrics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .perf-metric {
        background: var(--gray-50);
        border-radius: 8px;
        padding: 10px 14px;
        text-align: center;
    }

    .perf-metric-value {
        display: block;
        font-size: 18px;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 2px;
    }

    .perf-metric-label {
        font-size: 10px;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .perf-score-col {
        text-align: center;
        padding: 10px;
        background: var(--gray-50);
        border-radius: 10px;
    }

    .perf-score {
        font-size: 32px;
        font-weight: 800;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 4px;
    }

    .perf-score.good { color: var(--green-600); }
    .perf-score.warn { color: var(--yellow-600); }
    .perf-score.danger { color: var(--red-600); }

    .perf-score-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        margin-bottom: 8px;
    }

    .perf-score-bar {
        height: 4px;
        background: var(--gray-200);
        border-radius: 2px;
        overflow: hidden;
    }

    .perf-score-fill {
        height: 100%;
        background: var(--green-600);
        border-radius: 2px;
        transition: width 0.6s ease, background 0.3s ease;
    }

    .perf-score-fill.warn { background: var(--yellow-600); }
    .perf-score-fill.danger { background: var(--red-600); }

/* Peer Benchmarking Card */
.peer-toggle {
    display: flex;
    gap: 4px;
}

.peer-toggle-btn {
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid var(--gray-200);
    background: var(--white);
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-600);
    cursor: pointer;
    transition: all 0.15s;
}

.peer-toggle-btn.active {
    background: var(--navy-800);
    color: var(--white);
    border-color: var(--navy-800);
}

.peer-stats-table {
    margin-bottom: 14px;
}

.peer-row {
    display: grid;
    grid-template-columns: 1fr 60px 70px;
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px solid var(--gray-100);
    font-size: 13px;
    align-items: center;
}

.peer-row:last-child { border-bottom: none; }

.peer-header {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-500);
    border-bottom: 1px solid var(--gray-200) !important;
    padding-bottom: 8px;
}

.peer-metric {
    color: var(--gray-600);
}

.peer-you {
    font-weight: 700;
    color: var(--gray-900);
    text-align: center;
}

.peer-avg {
    color: var(--gray-500);
    text-align: right;
    font-size: 12px;
}

.peer-rank {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: var(--gray-50);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-700);
}

.rank-arrow {
    font-size: 18px;
    font-weight: 700;
}

.rank-arrow.up { color: var(--green-600); }
.rank-arrow.mid { color: var(--yellow-600); }
.rank-arrow.down { color: var(--red-600); }
</style>

<!-- App Layout -->
<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="{{ asset('agslogo-128.png') }}" alt="AGS" class="sidebar-logo" onerror="this.style.display='none'">
                <h1>AGS Break Tracker</h1>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu</div>
                <a href="{{ route('dashboard') }}" class="nav-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Profile</span>
                </a>
            </div>

            @if($user->isTeamLead() || $user->isAdmin())
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <a href="#" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Team</span>
                </a>
                <a href="{{ route('overbreaks') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span>Overbreaks</span>
                </a>
            </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ ucfirst($user->role) }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="topbar-right">
                <div class="status-indicator">Online</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Sign Out</button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Alerts -->
            <div id="agent-alert" class="alert">
                <div class="alert-content">
                    <strong>Overbreak Alert</strong>
                    <p>You have exceeded your allowed break time.</p>
                </div>
                <button onclick="dismissAlert('agent')" class="btn-alert-dismiss">Dismiss</button>
            </div>

            @if($user->isTeamLead() || $user->isAdmin())
            <div id="tl-alert" class="alert">
                <div class="alert-content">
                    <strong>Team Overbreak Alert</strong>
                    <p>A team member has exceeded their break time.</p>
                </div>
                <button onclick="dismissAlert('tl')" class="btn-alert-dismiss">Dismiss</button>
            </div>
            @endif

            @if(($user->isAdmin() || $user->isTeamLead()) && $teamBreaks->count() > 0)
                @php
                    $overbreakAgents = $teamBreaks->filter(fn($b) => now()->greaterThan($b->expected_end_at));
                @endphp
                @if($overbreakAgents->count() > 0)
                <div id="team-overbreak-alert" class="agent-alert" style="background: var(--red-50); border: 1px solid var(--red-600);">
                    <div class="alert-icon" style="background: var(--red-600);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div class="alert-content">
                        <strong style="color: var(--red-600);">{{ $overbreakAgents->count() }} Agent(s) on Overbreak</strong>
                        <p style="color: var(--gray-600);">{{ $overbreakAgents->pluck('user_name')->implode(', ') }}</p>
                    </div>
                    <button type="button" class="alert-dismiss" onclick="dismissAlert('team')" style="margin-left: auto;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                @endif
            @endif

            <!-- User Info -->
            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">My Information</h2>
                    </div>
                    <div class="card-body">
            <div class="profile-overview-grid">
                <!-- Left: Identity -->
                <div class="profile-identity">
                    <div class="profile-avatar-lg">{{ substr($user->name, 0, 1) }}</div>
                    <div class="profile-name-lg">{{ $user->name }}</div>
                    <div class="profile-role-badge">{{ ucfirst($user->role) }}</div>
                    <div class="profile-dept">{{ $user->department ?? 'No Department' }}</div>
                </div>
                <!-- Right: Work Details -->
                <div class="profile-details-grid">
                    <div class="profile-detail-item">
                        <span class="detail-label">Shift Schedule</span>
                        <span class="detail-value">{{ $user->shift_schedule ?? 'Not set' }}</span>
                    </div>
                    <div class="profile-detail-item">
                        <span class="detail-label">Work Location</span>
                        <span class="detail-value">
                            <span class="location-badge location-{{ $user->work_location ?? 'office' }}">
                                @if($user->work_location === 'wfh')
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> WFH
                                @elseif($user->work_location === 'hybrid')
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> Hybrid
                                @else
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg> Office
                                @endif
                            </span>
                        </span>
                    </div>
                    <div class="profile-detail-item">
                        <span class="detail-label">Team Lead</span>
                        <span class="detail-value">
                            @if($user->tl_email)
                                <a href="mailto:{{ $user->tl_email }}" class="tl-email-link">
                                    {{ $user->manager_name ?? $user->tl_email }}
                                </a>
                            @else
                                Not assigned
                            @endif
                        </span>
                    </div>
                    <div class="profile-detail-item">
                        <span class="detail-label">Hire Date</span>
                        <span class="detail-value">
                            @if($user->hire_date)
                                {{ $user->hire_date->format('M j, Y') }} ({{ $user->getTenureMonths() }} months)
                            @else
                                Not set
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
                </div>
            </section>

            <!-- Agent Break Control -->
            @if(!$user->isAdmin())
            <section class="section">
                <div class="break-control-grid">
                    <!-- Break Control -->
                    <x-break-control :active-break="$activeBreak" />

                    <!-- Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Quick Stats</h2>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="value" id="stat-1-value">{{ $stats['count_15m'] }}</div>
                                    <div class="label">15-Min Breaks</div>
                                </div>
                                <div class="stat-card">
                                    <div class="value" id="stat-2-value">{{ $stats['count_60m'] }}</div>
                                    <div class="label">1-Hour Breaks</div>
                                </div>
                                <div class="stat-card over">
                                    <div class="value" id="stat-3-value">{{ $stats['overbreaks_count'] }}</div>
                                    <div class="label">Overbreaks</div>
                                </div>
                                <div class="stat-card over">
                                    <div class="value" id="stat-4-value">{{ $stats['total_over_minutes'] }}m</div>
                                    <div class="label">Overbreak Time</div>
                                </div>
                                <div class="stat-card over">
                                    <div class="value" id="stat-5-value">{{ $stats['overbreaks_15m'] }}</div>
                                    <div class="label">15-Min Over</div>
                                </div>
                                <div class="stat-card over">
                                    <div class="value" id="stat-6-value">{{ $stats['overbreaks_60m'] }}</div>
                                    <div class="label">1-Hour Over</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Performance</h2>
                        </div>
                        <div class="card-body">
                            <div class="performance-grid">
                                <!-- Compliance Ring -->
                                <div class="perf-ring-col">
                                    <div class="perf-ring" id="compliance-ring">
                                        <svg viewBox="0 0 36 36" class="ring-svg">
                                            <path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                            <path class="ring-fill" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                        </svg>
                                        <div class="ring-label">
                                            <span class="ring-pct" id="perf-compliance" data-value="{{ $performance['compliance'] }}">{{ $performance['compliance'] }}</span>
                                            <span class="ring-sub">%</span>
                                        </div>
                                    </div>
                                    <div class="perf-ring-title">Compliance</div>
                                </div>
                                <!-- Metrics Grid -->
                                <div class="perf-metrics-grid">
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-daily">{{ $performance['daily_breaks'] }}/5</span>
                                        <span class="perf-metric-label">Today</span>
                                    </div>
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-weekly">{{ $performance['weekly_total'] }}</span>
                                        <span class="perf-metric-label">This Week</span>
                                    </div>
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-avg15">{{ $performance['avg_15m'] }}m</span>
                                        <span class="perf-metric-label">Avg 15m</span>
                                    </div>
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-avg60">{{ $performance['avg_60m'] }}m</span>
                                        <span class="perf-metric-label">Avg 1hr</span>
                                    </div>
                                </div>
                                <!-- Performance Score -->
                                <div class="perf-score-col">
                                    <div class="perf-score" id="perf-score">{{ $performance['score'] }}</div>
                                    <div class="perf-score-label">Score</div>
                                    <div class="perf-score-bar">
                                        <div class="perf-score-fill" id="perf-score-fill" style="width: {{ $performance['score'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Peer Benchmarking -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Peer Benchmarking</h2>
                            <div class="peer-toggle">
                                <button class="peer-toggle-btn active" data-scope="team" onclick="switchPeerScope('team')">My Team</button>
                                <button class="peer-toggle-btn" data-scope="dept" onclick="switchPeerScope('dept')">My Dept</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="peer-stats-table">
                                <div class="peer-row peer-header">
                                    <span>Metric</span>
                                    <span>You</span>
                                    <span id="peer-scope-label">Team Avg</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">15-min Breaks</span>
                                    <span class="peer-you">{{ $peerStats['my']['count_15m'] }}</span>
                                    <span class="peer-avg" id="peer-15m">{{ $peerStats['team']['count_15m_avg'] }}</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">1-hour Breaks</span>
                                    <span class="peer-you">{{ $peerStats['my']['count_60m'] }}</span>
                                    <span class="peer-avg" id="peer-60m">{{ $peerStats['team']['count_60m_avg'] }}</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">Overbreaks</span>
                                    <span class="peer-you">{{ $peerStats['my']['overbreaks'] }}</span>
                                    <span class="peer-avg" id="peer-over">{{ $peerStats['team']['overbreaks_avg'] }}</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">Compliance</span>
                                    <span class="peer-you">{{ $peerStats['my']['compliance'] }}%</span>
                                    <span class="peer-avg" id="peer-comp">{{ $peerStats['team']['compliance'] }}%</span>
                                </div>
                            </div>
                            <div class="peer-rank" id="peer-rank">
                                @php $pct = $peerStats['team']['percentile']; @endphp
                                @if($pct >= 70)
                                    <span class="rank-arrow up">&#8593;</span>
                                    <span>Top {{ 100 - $pct }}% of team</span>
                                @elseif($pct >= 40)
                                    <span class="rank-arrow mid">&#8594;</span>
                                    <span>Middle of team</span>
                                @else
                                    <span class="rank-arrow down">&#8595;</span>
                                    <span>Below average — improve compliance</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- My History -->
            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Break History</h2>
                    </div>
                    <div class="card-body">
                        <div class="history-split">
                            <div class="history-block">
                                <div class="history-label">15-Minute Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Started</th>
                                                    <th>Ended</th>
                                                    <th>Duration</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="my-history-15-body">
                                                @forelse($myHistory->where('break_type', '15m')->take(50) as $h)
                                                <tr>
                                                    <td>{{ $h->started_at->format('M j') }}</td>
                                                    <td>{{ $h->started_at->format('g:i A') }}</td>
                                                    <td>{{ $h->ended_at ? $h->ended_at->format('g:i A') : '—' }}</td>
                                                    <td>{{ $h->duration_minutes }}m</td>
                                                    <td>
                                                        @if($h->over_minutes > 0)
                                                        <span class="tag tag-over">+{{ $h->over_minutes }}m</span>
                                                        @else
                                                        <span class="tag tag-ok">OK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="empty-state">No 15-minute break history yet.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="history-block over">
                                <div class="history-label">1-Hour Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Started</th>
                                                    <th>Ended</th>
                                                    <th>Duration</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="my-history-60-body">
                                                @forelse($myHistory->where('break_type', '60m')->take(50) as $h)
                                                <tr>
                                                    <td>{{ $h->started_at->format('M j') }}</td>
                                                    <td>{{ $h->started_at->format('g:i A') }}</td>
                                                    <td>{{ $h->ended_at ? $h->ended_at->format('g:i A') : '—' }}</td>
                                                    <td>{{ $h->duration_minutes }}m</td>
                                                    <td>
                                                        @if($h->over_minutes > 0)
                                                        <span class="tag tag-over">+{{ $h->over_minutes }}m</span>
                                                        @else
                                                        <span class="tag tag-ok">OK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="empty-state">No 1-hour break history yet.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Admin Panel -->
            @if($user->isAdmin())
            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Admin Device Reset</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.device.reset') }}" class="form-row">
                            @csrf
                            <div class="field">
                                <label for="admin-device-search">Employee Email</label>
                                <input id="admin-device-search" name="email" type="email" placeholder="employee@company.com" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Reset Device</button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">All Currently On Break</h2>
                        <span class="tag tag-active">{{ $allActiveBreaks->count() }} active</span>
                    </div>
                    <div class="card-body">
                        <div class="history-split">
                            <div class="history-block">
                                <div class="history-label">15-Minute Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Email</th><th>Started</th><th>Elapsed</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="admin-live-15-body">
                                                @forelse($allActiveBreaks->where('break_type', '15m') as $ab)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $ab->user_name }}</td>
                                                    <td>{{ $ab->user_email }}</td>
                                                    <td>{{ $ab->started_at->format('g:i A') }}</td>
                                                    <td>{{ $ab->started_at->diffInMinutes(now()) }}m</td>
                                                    <td>
                                                        @if($ab->expected_end_at->isPast())
                                                        <span class="tag tag-over">Overbreak</span>
                                                        @else
                                                        <span class="tag tag-active">On Time</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="empty-state">No active 15-minute breaks.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="history-block over">
                                <div class="history-label">1-Hour Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Email</th><th>Started</th><th>Elapsed</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="admin-live-60-body">
                                                @forelse($allActiveBreaks->where('break_type', '60m') as $ab)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $ab->user_name }}</td>
                                                    <td>{{ $ab->user_email }}</td>
                                                    <td>{{ $ab->started_at->format('g:i A') }}</td>
                                                    <td>{{ $ab->started_at->diffInMinutes(now()) }}m</td>
                                                    <td>
                                                        @if($ab->expected_end_at->isPast())
                                                        <span class="tag tag-over">Overbreak</span>
                                                        @else
                                                        <span class="tag tag-active">On Time</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="empty-state">No active 1-hour breaks.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">All Break History</h2>
                    </div>
                    <div class="card-body">
                        <div class="history-split">
                            <div class="history-block">
                                <div class="history-label">15-Minute Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Date</th><th>Started</th><th>Ended</th><th>Duration</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="admin-history-15-body">
                                                @forelse($allHistory->where('break_type', '15m')->take(50) as $h)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $h->user_name }}</td>
                                                    <td>{{ $h->started_at->format('M j') }}</td>
                                                    <td>{{ $h->started_at->format('g:i A') }}</td>
                                                    <td>{{ $h->ended_at->format('g:i A') }}</td>
                                                    <td>{{ $h->duration_minutes }}m</td>
                                                    <td>
                                                        @if($h->over_minutes > 0)
                                                        <span class="tag tag-over">+{{ $h->over_minutes }}m</span>
                                                        @else
                                                        <span class="tag tag-ok">OK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" class="empty-state">No 15-minute break history.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="history-block over">
                                <div class="history-label">1-Hour Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Date</th><th>Started</th><th>Ended</th><th>Duration</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="admin-history-60-body">
                                                @forelse($allHistory->where('break_type', '60m')->take(50) as $h)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $h->user_name }}</td>
                                                    <td>{{ $h->started_at->format('M j') }}</td>
                                                    <td>{{ $h->started_at->format('g:i A') }}</td>
                                                    <td>{{ $h->ended_at->format('g:i A') }}</td>
                                                    <td>{{ $h->duration_minutes }}m</td>
                                                    <td>
                                                        @if($h->over_minutes > 0)
                                                        <span class="tag tag-over">+{{ $h->over_minutes }}m</span>
                                                        @else
                                                        <span class="tag tag-ok">OK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" class="empty-state">No 1-hour break history.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Team Lead Panel -->
            @if($user->isTeamLead())
            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Currently On Break</h2>
                        <span class="tag tag-active" id="team-live-tag">{{ $teamBreaks->count() }} active</span>
                    </div>
                    <div class="card-body">
                        <div class="history-split">
                            <div class="history-block">
                                <div class="history-label">15-Minute Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Department</th><th>Started</th><th>Elapsed</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="team-live-15-body">
                                                @forelse($teamBreaks->where('break_type', '15m') as $tb)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $tb->user_name }}</td>
                                                    <td>{{ $tb->department ?? '—' }}</td>
                                                    <td>{{ $tb->started_at->format('g:i A') }}</td>
                                                    <td>{{ $tb->started_at->diffInMinutes(now()) }}m</td>
                                                    <td>
                                                        @if($tb->expected_end_at->isPast())
                                                        <span class="tag tag-over">Overbreak</span>
                                                        @else
                                                        <span class="tag tag-active">On Time</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="empty-state">No team members on 15-min break.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="history-block over">
                                <div class="history-label">1-Hour Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Department</th><th>Started</th><th>Elapsed</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="team-live-60-body">
                                                @forelse($teamBreaks->where('break_type', '60m') as $tb)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $tb->user_name }}</td>
                                                    <td>{{ $tb->department ?? '—' }}</td>
                                                    <td>{{ $tb->started_at->format('g:i A') }}</td>
                                                    <td>{{ $tb->started_at->diffInMinutes(now()) }}m</td>
                                                    <td>
                                                        @if($tb->expected_end_at->isPast())
                                                        <span class="tag tag-over">Overbreak</span>
                                                        @else
                                                        <span class="tag tag-active">On Time</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="empty-state">No team members on 1-hour break.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Team Break History</h2>
                    </div>
                    <div class="card-body">
                        <div class="history-split">
                            <div class="history-block">
                                <div class="history-label">15-Minute Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Date</th><th>Started</th><th>Ended</th><th>Duration</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="team-history-15-body">
                                                @forelse($teamHistory->where('break_type', '15m')->take(50) as $th)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $th->user_name }}</td>
                                                    <td>{{ $th->started_at->format('M j') }}</td>
                                                    <td>{{ $th->started_at->format('g:i A') }}</td>
                                                    <td>{{ $th->ended_at->format('g:i A') }}</td>
                                                    <td>{{ $th->duration_minutes }}m</td>
                                                    <td>
                                                        @if($th->over_minutes > 0)
                                                        <span class="tag tag-over">+{{ $th->over_minutes }}m</span>
                                                        @else
                                                        <span class="tag tag-ok">OK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" class="empty-state">No 15-minute team history.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="history-block over">
                                <div class="history-label">1-Hour Break</div>
                                <div class="table-wrapper">
                                    <div class="table-scroll">
                                        <table>
                                            <thead>
                                                <tr><th>Name</th><th>Date</th><th>Started</th><th>Ended</th><th>Duration</th><th>Status</th></tr>
                                            </thead>
                                            <tbody id="team-history-60-body">
                                                @forelse($teamHistory->where('break_type', '60m')->take(50) as $th)
                                                <tr>
                                                    <td style="font-weight: 500;">{{ $th->user_name }}</td>
                                                    <td>{{ $th->started_at->format('M j') }}</td>
                                                    <td>{{ $th->started_at->format('g:i A') }}</td>
                                                    <td>{{ $th->ended_at->format('g:i A') }}</td>
                                                    <td>{{ $th->duration_minutes }}m</td>
                                                    <td>
                                                        @if($th->over_minutes > 0)
                                                        <span class="tag tag-over">+{{ $th->over_minutes }}m</span>
                                                        @else
                                                        <span class="tag tag-ok">OK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" class="empty-state">No 1-hour team history.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif
        </div>
    </main>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<script>
    @if(session('success'))
        showToast("{{ session('success') }}");
    @endif
    @if(session('error'))
        showToast("{{ session('error') }}", true);
    @endif

    function showToast(message, isError) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        if (isError) toast.classList.add('error');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    function dismissAlert(type) {
        document.getElementById(type + '-alert').classList.remove('show');
        localStorage.setItem('dismissed_' + type + '_alert', 'true');
    }

    function switchPeerScope(scope) {
        // Update toggle buttons
        var btns = document.querySelectorAll('.peer-toggle-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.toggle('active', btns[i].dataset.scope === scope);
        }

        // Update label
        var labelEl = document.getElementById('peer-scope-label');
        labelEl.textContent = scope === 'team' ? 'Team Avg' : 'Dept Avg';

        // Get peer data from PHP
        var peerData = @json($peerStats);
        var data = scope === 'team' ? peerData.team : peerData.department;

        document.getElementById('peer-15m').textContent = data.count_15m_avg;
        document.getElementById('peer-60m').textContent = data.count_60m_avg;
        document.getElementById('peer-over').textContent = data.overbreaks_avg;
        document.getElementById('peer-comp').textContent = data.compliance + '%';

        // Update rank badge
        var rankEl = document.getElementById('peer-rank');
        var pct = scope === 'team' ? peerData.team.percentile : 50;
        var arrow = pct >= 70 ? '\u2191' : pct >= 40 ? '\u2192' : '\u2193';
        var arrowClass = pct >= 70 ? 'up' : pct >= 40 ? 'mid' : 'down';
        var msg = scope === 'team' && pct >= 70 ? 'Top ' + (100 - pct) + '% of team' : scope === 'team' ? 'Middle of team' : 'Department comparison';
        rankEl.innerHTML = '<span class="rank-arrow ' + arrowClass + '">' + arrow + '</span><span>' + msg + '</span>';
    }

    // Auto-trigger voice alert for overbreak
    let overbreakAlertTriggered = false;
    function triggerOverbreakAlert() {
        if (overbreakAlertTriggered) return;
        overbreakAlertTriggered = true;

        const agentName = "{{ $user->name ?? 'Agent' }}";
        const overMinutes = Math.floor((Date.now() - startTime.getTime()) / 60000) - allowedMinutes;

        fetch('/alerts/overbreak?agent_name=' + encodeURIComponent(agentName) + '&over_minutes=' + overMinutes)
            .then(response => {
                if (response.ok) return response.blob();
                throw new Error('Alert failed');
            })
            .then(blob => {
                const url = URL.createObjectURL(blob);
                const audio = new Audio(url);
                audio.play().catch(() => {});
            })
            .catch(() => {
                // Fallback: try opening in new tab
                window.open('/alerts/test', '_blank');
            });
    }

    @if($activeBreak)
    const startTime = new Date("{{ $activeBreak->started_at->toISOString() }}");
    const expectedEnd = new Date("{{ $activeBreak->expected_end_at->toISOString() }}");
    const breakType = "{{ $activeBreak->break_category ?? 'break' }}";
    const allowedMinutes = {{ $activeBreak->allowed_minutes }};
    let isOverbreak = false;
    let warned5min = false;
    let warned1min = false;

    function updateElapsed() {
        const now = new Date();
        const diff = Math.floor((now - startTime) / 1000);
        const mins = Math.floor(diff / 60);
        const secs = diff % 60;
        const timeStr = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

        // Update elapsed time display
        document.getElementById('current-break-elapsed').textContent = timeStr;

        // Update digital timer display
        const timerDisplay = document.getElementById('timer-display');
        const timerCircle = document.getElementById('timer-circle');
        const timerLabel = document.getElementById('timer-label');
        const statusTag = document.getElementById('current-status-tag');

        // Calculate time remaining until overbreak
        const msRemaining = expectedEnd - now;
        const minsRemaining = Math.floor(msRemaining / 60000);

        // Progressive warnings
        if (!warned5min && minsRemaining <= 5 && minsRemaining > 1) {
            warned5min = true;
            timerCircle.style.borderColor = 'var(--yellow-500)';
            timerLabel.textContent = '5 MIN';
            statusTag.innerHTML = '<span class="dot"></span><span>Warning</span>';
            statusTag.className = 'status-tag warning';
        }

        if (!warned1min && minsRemaining <= 1 && minsRemaining > 0) {
            warned1min = true;
            timerCircle.style.borderColor = 'var(--orange-500)';
            timerLabel.textContent = '1 MIN';
            statusTag.innerHTML = '<span class="dot"></span><span>Almost Over</span>';
            statusTag.className = 'status-tag warning';
        }

        if (now > expectedEnd && !isOverbreak) {
            isOverbreak = true;

            // Update status tag to overbreak
            statusTag.innerHTML = '<span class="dot"></span><span>Overbreak</span>';
            statusTag.className = 'status-tag overbreak';

            // Update digital timer to overbreak state
            timerCircle.className = 'digital-timer overbreak';
            timerCircle.style.borderColor = '';
            timerDisplay.textContent = timeStr;
            timerLabel.textContent = 'OVERBREAK';
            timerLabel.style.color = 'var(--red-600)';

            document.getElementById('end-break-form').style.display = 'block';

            if (!localStorage.getItem('dismissed_agent_alert')) {
                document.getElementById('agent-alert').classList.add('show');
            }

            // Auto-trigger voice alert for overbreak
            triggerOverbreakAlert();
        } else if (!isOverbreak) {
            // Normal break state - update timer
            timerDisplay.textContent = timeStr;
            timerLabel.textContent = breakType === 'lunch' ? 'LUNCH' : 'BREAK';
        }
    }

    // Initial timer update
    updateElapsed();
    setInterval(updateElapsed, 1000);
    @endif

    // Poll for live updates every 15 seconds
    setInterval(async () => {
        try {
            const response = await fetch('/dashboard/live');
            const data = await response.json();

            // Update stats if available
            if (data.stats) {
                const statMappings = {
                    'stat-1-value': data.stats.count_15m,
                    'stat-2-value': data.stats.count_60m,
                    'stat-3-value': data.stats.overbreaks_count,
                    'stat-4-value': data.stats.total_over_minutes + 'm',
                    'stat-5-value': data.stats.overbreaks_15m,
                    'stat-6-value': data.stats.overbreaks_60m,
                };

                for (const [id, value] of Object.entries(statMappings)) {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value;
                }
            }

            // Update team overbreak alert for TL/Admin
            if (data.breaks && data.breaks.length > 0) {
                const now = new Date();
                const overbreakAgents = data.breaks.filter(b => new Date(b.expected_end_at) < now);

                if (overbreakAgents.length > 0) {
                    const teamAlert = document.getElementById('team-overbreak-alert');
                    const tlAlert = document.getElementById('tl-alert');

                    if (teamAlert && !localStorage.getItem('dismissed_team_alert')) {
                        const names = [...new Set(overbreakAgents.map(b => b.user_name))].join(', ');
                        teamAlert.querySelector('strong').textContent = overbreakAgents.length + ' Agent(s) on Overbreak';
                        teamAlert.querySelector('p').textContent = names;
                        teamAlert.style.display = 'flex';
                    }

                    if (tlAlert && !localStorage.getItem('dismissed_tl_alert')) {
                        tlAlert.classList.add('show');
                    }
                }
            }
        } catch (e) {
            // Silently fail polling
        }
    }, 15000);

    function updatePerformanceColors() {
        var complianceEl = document.getElementById('perf-compliance');
        var compliance = parseInt(complianceEl.dataset.value || 0);
        var ring = document.querySelector('.ring-fill');
        if (ring) {
            ring.classList.remove('warn', 'danger');
            if (compliance < 70) {
                ring.classList.add('danger');
            } else if (compliance < 90) {
                ring.classList.add('warn');
            }
        }

        var scoreEl = document.getElementById('perf-score');
        var scoreFill = document.getElementById('perf-score-fill');
        var score = parseInt(scoreEl.textContent) || 0;
        scoreEl.classList.remove('good', 'warn', 'danger');
        scoreFill.classList.remove('warn', 'danger');
        if (score < 60) {
            scoreEl.classList.add('danger');
            scoreFill.classList.add('danger');
        } else if (score < 80) {
            scoreEl.classList.add('warn');
            scoreFill.classList.add('warn');
        } else {
            scoreEl.classList.add('good');
        }
    }

    document.addEventListener('DOMContentLoaded', updatePerformanceColors);
</script>
</x-app-layout>
