<x-guest-layout>
    <div class="onboarding-header">
        <h1 class="form-title">Join AGS Break Tracker</h1>
        <p class="form-desc">Welcome, {{ $user->name }}! Let's get you set up. First, tell us about your role.</p>
    </div>

    <form method="POST" action="{{ route('onboarding.role.store') }}">
        @csrf

        <!-- Role Selection -->
        <div class="form-group">
            <label class="form-label">I am a...</label>
            <div class="role-cards">
                <label class="role-card" for="role-agent">
                    <input type="radio" name="role" id="role-agent" value="agent" {{ old('role') === 'agent' ? 'checked' : '' }} required>
                    <div class="role-card-content">
                        <div class="role-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="role-card-text">
                            <span class="role-card-title">Support Agent</span>
                            <span class="role-card-desc">Track breaks and manage your schedule</span>
                        </div>
                    </div>
                </label>

                <label class="role-card" for="role-tl">
                    <input type="radio" name="role" id="role-tl" value="tl" {{ old('role') === 'tl' ? 'checked' : '' }}>
                    <div class="role-card-content">
                        <div class="role-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="role-card-text">
                            <span class="role-card-title">Team Leader</span>
                            <span class="role-card-desc">Manage your team's break schedules</span>
                        </div>
                    </div>
                </label>
            </div>
            @error('role')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Team Selection (only for agents) -->
        <div id="team-selection" class="form-group {{ old('role') === 'tl' ? 'hidden' : '' }}">
            <label class="form-label" for="team_id">Select Your Team</label>
            <select name="team_id" id="team_id" class="form-input" {{ old('role') !== 'tl' ? 'required' : '' }}>
                <option value="">-- Select a Team --</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
            @error('team_id')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Department (optional) -->
        <div class="form-group">
            <label class="form-label" for="department">Department (Optional)</label>
            <x-text-input id="department" name="department" type="text" class="form-input" :value="old('department')" placeholder="e.g., Customer Support" />
            @error('department')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-footer-row">
            <div class="text-sm text-gray-500">
                @if(old('role') === 'tl')
                    <span class="text-green-600">Team leaders are auto-approved!</span>
                @else
                    <span>Agents require admin approval before full access.</span>
                @endif
            </div>

            <button type="submit" class="submit-btn">
                Continue
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>

    <script>
        document.querySelectorAll('input[name="role"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const teamSelection = document.getElementById('team-selection');
                const teamInput = document.getElementById('team_id');
                if (this.value === 'tl') {
                    teamSelection.classList.add('hidden');
                    teamInput.removeAttribute('required');
                } else {
                    teamSelection.classList.remove('hidden');
                    teamInput.setAttribute('required', 'required');
                }
            });
        });
    </script>
</x-guest-layout>

<style>
    .role-cards {
        display: flex;
        gap: 16px;
        margin-bottom: 8px;
    }
    .role-card {
        flex: 1;
        cursor: pointer;
    }
    .role-card input {
        display: none;
    }
    .role-card-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px 16px;
        border: 2px solid var(--border);
        border-radius: 16px;
        transition: all 0.2s ease;
    }
    .role-card input:checked + .role-card-content {
        border-color: var(--navy-700);
        background: rgba(20, 54, 99, 0.05);
    }
    .role-card:hover .role-card-content {
        border-color: var(--navy-600);
        transform: translateY(-2px);
    }
    .role-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(20, 54, 99, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--navy-700);
        margin-bottom: 12px;
    }
    .role-card-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    .role-card-desc {
        font-size: 12px;
        color: var(--text-secondary);
        text-align: center;
    }
    .hidden {
        display: none !important;
    }
    .onboarding-header {
        margin-bottom: 32px;
    }
    @media (max-width: 600px) {
        .role-cards {
            flex-direction: column;
        }
    }
</style>
