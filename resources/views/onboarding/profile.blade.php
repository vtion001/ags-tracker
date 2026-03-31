<x-guest-layout>
    <div class="onboarding-header">
        <h1 class="form-title">Complete Your Profile</h1>
        <p class="form-desc">Tell us a bit more about yourself so your team can find you.</p>
    </div>

    <form method="POST" action="{{ route('onboarding.profile.store') }}">
        @csrf

        <div class="form-group">
            <x-input-label for="position" :value="__('Position / Title')" />
            <x-text-input id="position" name="position" type="text" class="form-input" :value="old('position', $user->position)" placeholder="e.g., Support Specialist" />
            @error('position')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <x-input-label for="contact_number" :value="__('Contact Number')" />
            <x-text-input id="contact_number" name="contact_number" type="text" class="form-input" :value="old('contact_number', $user->contact_number)" placeholder="e.g., +1 234 567 8900" />
            @error('contact_number')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <x-input-label for="shift_schedule" :value="__('Shift Schedule')" />
            <x-text-input id="shift_schedule" name="shift_schedule" type="text" class="form-input" :value="old('shift_schedule', $user->shift_schedule)" placeholder="e.g., 9 AM - 6 PM, Mon-Fri" />
            @error('shift_schedule')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <x-input-label for="work_location" :value="__('Work Location')" />
            <select name="work_location" id="work_location" class="form-input">
                <option value="">-- Select --</option>
                <option value="office" {{ old('work_location', $user->work_location) === 'office' ? 'selected' : '' }}>Office</option>
                <option value="wfh" {{ old('work_location', $user->work_location) === 'wfh' ? 'selected' : '' }}>Work From Home</option>
                <option value="hybrid" {{ old('work_location', $user->work_location) === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
            </select>
            @error('work_location')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <x-input-label for="manager_name" :value="__('Manager Name (Optional)')" />
            <x-text-input id="manager_name" name="manager_name" type="text" class="form-input" :value="old('manager_name', $user->manager_name)" placeholder="e.g., John Smith" />
            @error('manager_name')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <x-input-label for="hire_date" :value="__('Hire Date')" />
            <x-text-input id="hire_date" name="hire_date" type="date" class="form-input" :value="old('hire_date', $user->hire_date?->format('Y-m-d'))" />
            @error('hire_date')
                <p class="error-message mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-footer-row">
            <a href="{{ route('onboarding.emergency') }}" class="skip-link">Skip for now</a>
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
