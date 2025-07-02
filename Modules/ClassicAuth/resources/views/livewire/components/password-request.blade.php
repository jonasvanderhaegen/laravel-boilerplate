<div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">    
    {{-- Livewire Alert Manager --}}
    <livewire:flowbite::components.alert-manager />

    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">

            @if($emailSent)
                <div class="text-center">
                    <svg class="mx-auto mb-4 text-green-500 w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Check your email') }}</h3>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">
                        {{ __('We have sent a password reset link to :email', ['email' => $form->email]) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Didn\'t receive the email? Check your spam folder or request a new link.') }}
                    </p>
                </div>
            @else
                <x-flowbite::form :form="$this->form"
                          submit="submit"
                          :title="__('Forgot your password?')"
                          button-text="Send reset link"
                          class="space-y-4 md:space-y-6">

                    <x-slot name="description">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Enter your email address and we will send you a link to reset your password.') }}
                        </p>
                    </x-slot>

                    <x-slot name="fields">
                        <x-flowbite::input.text-field
                            field="form.email"
                            type="email"
                            :label="__('E-mail address')"
                            :helper="__('The email address associated with your account')"
                            required
                        />
                    </x-slot>

                    <x-slot name="underForm">
                        <x-flowbite::form-link route="flowbite.login" :grayText="__('Remember your password?')" :blueText="__('Sign in')" />
                    </x-slot>
                </x-flowbite::form>
            @endif
        </div>
    </div>
</div>
