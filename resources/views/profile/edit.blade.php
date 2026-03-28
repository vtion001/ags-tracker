<x-app-layout>
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

    /* Page Content */
    .page-content {
        flex: 1;
        padding: 24px;
    }

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

    /* Alert */
    .alert {
        padding: 12px 14px;
        border-radius: 6px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .alert-success {
        background: var(--green-50);
        border: 1px solid #bbf7d0;
        color: var(--green-600);
    }

    .alert-error {
        background: var(--red-50);
        border: 1px solid #fecaca;
        color: var(--red-600);
    }

    /* Cards */
    .card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .card:last-child { margin-bottom: 0; }

    .card-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--gray-100);
    }

    .card-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .card-body {
        padding: 16px;
    }

    /* Profile Layout */
    .profile-grid {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 20px;
    }

    /* Profile Sidebar */
    .profile-sidebar {
        text-align: center;
        padding: 20px;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 8px;
        background: var(--gray-800);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 28px;
        font-weight: 600;
        color: var(--white);
    }

    .profile-name {
        font-size: 15px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 2px;
    }

    .profile-email {
        font-size: 12px;
        color: var(--gray-500);
        margin-bottom: 12px;
    }

    .profile-role {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        background: var(--gray-100);
        color: var(--gray-600);
    }

    .profile-stats {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--gray-200);
    }

    .profile-stat {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 12px;
    }

    .profile-stat-label {
        color: var(--gray-500);
    }

    .profile-stat-value {
        font-weight: 500;
        color: var(--gray-700);
    }

    /* Forms */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-700);
    }

    .form-group input {
        height: 38px;
        padding: 0 12px;
        border-radius: 6px;
        border: 1px solid var(--gray-300);
        background: var(--white);
        color: var(--gray-900);
        font-size: 13px;
        transition: all 0.15s;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--black);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
    }

    .form-group input:disabled {
        background: var(--gray-50);
        color: var(--gray-500);
        cursor: not-allowed;
    }

    .form-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
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

    /* TOTP Section */
    .totp-status {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        background: var(--gray-50);
        border-radius: 6px;
        margin-bottom: 14px;
    }

    .totp-icon {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        background: var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-500);
    }

    .totp-icon.enabled {
        background: var(--green-50);
        color: var(--green-600);
    }

    .totp-info {
        flex: 1;
    }

    .totp-info strong {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 2px;
    }

    .totp-info p {
        font-size: 12px;
        color: var(--gray-500);
    }

    .totp-badge {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }

    .totp-badge.enabled {
        background: var(--green-50);
        color: var(--green-600);
    }

    .totp-badge.disabled {
        background: var(--gray-100);
        color: var(--gray-500);
    }

    /* Danger Zone */
    .card.danger {
        border-color: #fecaca;
    }

    .card.danger .card-header {
        background: var(--red-50);
        border-bottom-color: #fecaca;
    }

    .card.danger .card-title {
        color: var(--red-600);
    }

    .card.danger p {
        font-size: 13px;
        color: var(--gray-600);
        margin-bottom: 14px;
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

    /* Responsive */
    @media (max-width: 1024px) {
        .sidebar { width: 72px; }
        .sidebar-brand h1, .nav-item span, .nav-section-title, .user-info { display: none; }
        .sidebar-header { padding: 16px 12px; }
        .nav-item { justify-content: center; padding: 12px; }
        .user-card { justify-content: center; }
        .main-content { margin-left: 72px; }
    }

    @media (max-width: 768px) {
        .sidebar { display: none; }
        .main-content { margin-left: 0; }
        .topbar { padding: 0 16px; }
        .page-content { padding: 16px; }
        .profile-grid { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
    }
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
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Profile</span>
                </a>
            </div>
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
                <h1 class="page-title">Profile Settings</h1>
            </div>
            <div class="topbar-right">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <div class="page-header">
                <h2>Agent Profile</h2>
                <p>Manage your account settings and preferences</p>
            </div>

            @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <div class="profile-grid">
                <!-- Left Sidebar -->
                <div class="card">
                    <div class="profile-sidebar">
                        <div class="profile-avatar">{{ substr($user->name, 0, 1) }}</div>
                        <h3 class="profile-name">{{ $user->name }}</h3>
                        <p class="profile-email">{{ $user->email }}</p>
                        <span class="profile-role">{{ ucfirst($user->role) }}</span>

                        <div class="profile-stats">
                            <div class="profile-stat">
                                <span class="profile-stat-label">Department</span>
                                <span class="profile-stat-value">{{ $user->department ?? 'N/A' }}</span>
                            </div>
                            <div class="profile-stat">
                                <span class="profile-stat-label">Position</span>
                                <span class="profile-stat-value">{{ $user->position ?? 'N/A' }}</span>
                            </div>
                            <div class="profile-stat">
                                <span class="profile-stat-label">Work Location</span>
                                <span class="profile-stat-value">{{ $user->getWorkLocationLabel() }}</span>
                            </div>
                            <div class="profile-stat">
                                <span class="profile-stat-label">Tenure</span>
                                <span class="profile-stat-value">{{ $user->getTenureMonths() }} months</span>
                            </div>
                            <div class="profile-stat">
                                <span class="profile-stat-label">Total Breaks</span>
                                <span class="profile-stat-value">{{ $user->breakHistory()->count() ?? 0 }}</span>
                            </div>
                            <div class="profile-stat">
                                <span class="profile-stat-label">Overbreaks</span>
                                <span class="profile-stat-value">{{ $user->breakHistory()->where('over_minutes', '>', 0)->count() ?? 0 }}</span>
                            </div>
                            <div class="profile-stat">
                                <span class="profile-stat-label">2FA Enabled</span>
                                <span class="profile-stat-value">{{ $user->totp_enabled ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Content -->
                <div>
                    <!-- Profile Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Profile Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="position">Position / Title</label>
                                        <input type="text" id="position" name="position" value="{{ old('position', $user->position ?? '') }}" placeholder="e.g., Senior Agent, Team Lead">
                                    </div>

                                    <div class="form-group">
                                        <label for="contact_number">Contact Number</label>
                                        <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number ?? '') }}" placeholder="e.g., +63 912 345 6789">
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Work Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Work Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="department">Department</label>
                                        @if($user->isAdmin())
                                            <input type="text" id="department" name="department" value="{{ old('department', $user->department ?? '') }}" placeholder="Enter department">
                                        @else
                                            <input type="text" value="{{ $user->department ?? 'Not assigned' }}" disabled>
                                            <input type="hidden" name="department" value="{{ $user->department ?? '' }}">
                                            <small style="color: var(--gray-500);">Contact admin to change</small>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="tl_email">Team Lead Email</label>
                                        @if($user->isAdmin() || $user->isTeamLead())
                                            <input type="email" id="tl_email" name="tl_email" value="{{ old('tl_email', $user->tl_email ?? '') }}" placeholder="Enter team lead's email">
                                        @else
                                            <input type="text" value="{{ $user->tl_email ?? 'Not assigned' }}" disabled>
                                            <input type="hidden" name="tl_email" value="{{ $user->tl_email ?? '' }}">
                                            <small style="color: var(--gray-500);">Contact admin to change</small>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="manager_name">Manager Name</label>
                                        <input type="text" id="manager_name" name="manager_name" value="{{ old('manager_name', $user->manager_name ?? '') }}" placeholder="Direct manager's name">
                                    </div>

                                    <div class="form-group">
                                        <label for="shift_schedule">Shift Schedule</label>
                                        <input type="text" id="shift_schedule" name="shift_schedule" value="{{ old('shift_schedule', $user->shift_schedule ?? '') }}" placeholder="e.g., Morning (6AM-3PM)">
                                    </div>

                                    <div class="form-group">
                                        <label for="hire_date">Hire Date</label>
                                        <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date', $user->hire_date?->format('Y-m-d') ?? '') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="work_location">Work Location</label>
                                        <select id="work_location" name="work_location" style="height: 38px; padding: 0 12px; border-radius: 6px; border: 1px solid var(--gray-300); background: var(--white); font-size: 13px; width: 100%;">
                                            <option value="">Select location</option>
                                            <option value="office" {{ old('work_location', $user->work_location ?? '') == 'office' ? 'selected' : '' }}>Office</option>
                                            <option value="wfh" {{ old('work_location', $user->work_location ?? '') == 'wfh' ? 'selected' : '' }}>Work From Home</option>
                                            <option value="hybrid" {{ old('work_location', $user->work_location ?? '') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Emergency Contact (Compliance) -->
                    <div class="card" style="border-left: 3px solid var(--red-600);">
                        <div class="card-header" style="background: var(--red-50);">
                            <h3 class="card-title">Emergency Contact</h3>
                        </div>
                        <div class="card-body">
                            <p style="font-size: 12px; color: var(--gray-500); margin-bottom: 16px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                Required for compliance and safety protocols. This information is confidential.
                            </p>
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="emergency_contact_name">Contact Name</label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name ?? '') }}" placeholder="Emergency contact name">
                                    </div>

                                    <div class="form-group">
                                        <label for="emergency_contact_phone">Contact Phone</label>
                                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone ?? '') }}" placeholder="e.g., +63 912 345 6789">
                                    </div>

                                    <div class="form-group">
                                        <label for="emergency_contact_relationship">Relationship</label>
                                        <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') }}" placeholder="e.g., Spouse, Parent, Sibling">
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Security</h3>
                        </div>
                        <div class="card-body">
                            <div class="totp-status">
                                <div class="totp-icon {{ $user->totp_enabled ? 'enabled' : '' }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                                        <circle cx="12" cy="16" r="1"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </div>
                                <div class="totp-info">
                                    <strong>Two-Factor Authentication</strong>
                                    <p>{{ $user->totp_enabled ? 'Your account is protected with Google Authenticator.' : 'Add an extra layer of security to your account.' }}</p>
                                </div>
                                <span class="totp-badge {{ $user->totp_enabled ? 'enabled' : 'disabled' }}">
                                    {{ $user->totp_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>

                            @if($user->totp_enabled)
                            <form method="POST" action="{{ route('totp.disable') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary" onclick="return confirm('Disable 2FA?')">Disable 2FA</button>
                            </form>
                            @else
                            <a href="{{ route('totp.setup') }}" class="btn btn-primary">Enable 2FA</a>
                            @endif
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Update Password</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                @method('put')

                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="password">New Password</label>
                                        <input type="password" id="password" name="password" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="card danger">
                        <div class="card-header">
                            <h3 class="card-title">Danger Zone</h3>
                        </div>
                        <div class="card-body">
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Delete your account? This cannot be undone.')">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-secondary" style="background: var(--red-600); color: white; border-color: var(--red-600);">Delete Account</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

@if(session('status'))
<script>
    document.getElementById('toast').textContent = 'Profile updated successfully.';
    document.getElementById('toast').classList.add('show');
    setTimeout(() => document.getElementById('toast').classList.remove('show'), 4000);
</script>
@endif
</x-app-layout>
