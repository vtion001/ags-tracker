<x-guest-layout>
    <div class="onboarding-header">
        <h1 class="form-title">Emergency Contact</h1>
        <p class="form-desc">In case of emergency, who should we contact?</p>
    </div>

    <form method="POST" action="{{ route('onboarding.emergency.store') }}">
        @csrf

        <div class="form-group">
            <x-input-label for="emergency_contact_name" :value="__('Contact Name')" />
            <x-text-input id="emergency_contact_name" name="emergency_contact_name" type="text" class="form-input" :value="old('emergency_contact_name', $user->emergency_contact_name)" placeholder="e.g., Jane Doe" />
            @error('emergency_contact_name')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <x-input-label for="emergency_contact_phone" :value="__('Contact Phone')" />
            <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="text" class="form-input" :value="old('emergency_contact_phone', $user->emergency_contact_phone)" placeholder="e.g., +1 234 567 8900" />
            @error('emergency_contact_phone')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <x-input-label for="emergency_contact_relationship" :value="__('Relationship')" />
            <x-text-input id="emergency_contact_relationship" name="emergency_contact_relationship" type="text" class="form-input" :value="old('emergency_contact_relationship', $user->emergency_contact_relationship)" placeholder="e.g., Spouse, Parent, Sibling" />
            @error('emergency_contact_relationship')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-footer-row">
            <a href="{{ route('onboarding.security') }}" class="skip-link">Skip for now</a>
            <button type="submit" class="submit-btn">
                Continue
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
