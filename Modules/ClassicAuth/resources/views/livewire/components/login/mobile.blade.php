<div class="min-h-screen flex flex-col justify-center px-4">
    {{-- Livewire Alert Manager --}}
    <div class="mb-4">
        <livewire:alert-manager />
    </div>
    
    {{-- Logo/Header Section --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-center text-gray-900 dark:text-white">
            {{ __('Welcome back') }}
        </h1>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('Sign in to continue') }}
        </p>
    </div>

    {{-- Login Form --}}
    <form wire:submit="login" class="space-y-5" id="{{ $formId }}">
        {{-- Email Field --}}
        <div>
            <label for="mobile-email" class="sr-only">
                {{ __('Email address') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input
                    wire:model.blur="form.email"
                    id="mobile-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    @class([
                        'appearance-none block w-full pl-10 pr-3 py-3 border rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base',
                        'border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white' => !$errors->has('form.email'),
                        'border-red-300 text-red-900 placeholder-red-300' => $errors->has('form.email'),
                    ])
                    placeholder="{{ __('Email address') }}"
                >
            </div>
            @error('form.email')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password Field --}}
        <div>
            <label for="mobile-password" class="sr-only">
                {{ __('Password') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input
                    wire:model="form.password"
                    id="mobile-password"
                    name="password"
                    type="{{ $showPassword ? 'text' : 'password' }}"
                    autocomplete="current-password"
                    required
                    @class([
                        'appearance-none block w-full pl-10 pr-10 py-3 border rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base',
                        'border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white' => !$errors->has('form.password'),
                        'border-red-300 text-red-900 placeholder-red-300' => $errors->has('form.password'),
                    ])
                    placeholder="{{ __('Password') }}"
                >
                <button
                    wire:click="togglePasswordVisibility"
                    type="button"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    tabindex="-1"
                >
                    @if($showPassword)
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    @endif
                </button>
            </div>
            @error('form.password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me Checkbox --}}
        <div class="flex items-center">
            <input
                wire:model="form.remember"
                id="mobile-remember"
                name="remember"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700"
            >
            <label for="mobile-remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                {{ __('Keep me signed in') }}
            </label>
        </div>

        {{-- Submit Button --}}
        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            @class([
                'w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200',
                'bg-primary-600 hover:bg-primary-700 active:bg-primary-800' => $canSubmit,
                'bg-gray-400 cursor-not-allowed' => !$canSubmit,
            ])
        >
            <span wire:loading.remove wire:target="login">{{ __('Sign in') }}</span>
            <span wire:loading wire:target="login" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Signing in...') }}
            </span>
        </button>

        {{-- Links Section --}}
        <div class="mt-6 space-y-4">
            @if(Route::has('password.request'))
                <button 
                    wire:click="redirectToPasswordReset" 
                    type="button" 
                    class="w-full text-center text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                >
                    {{ __('Forgot your password?') }}
                </button>
            @endif

            @if(Route::has('register'))
                <div class="text-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __("Don't have an account?") }}</span>
                    <button 
                        wire:click="redirectToRegister" 
                        type="button" 
                        class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                        {{ __('Sign up') }}
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
