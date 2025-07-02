<div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">    
    {{-- Livewire Alert Manager --}}
    <livewire:flowbite::components.alert-manager />

    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">

            <x-flowbite::form :form="$this->form"
                      submit="submit"
                      :title="__('Reset your password')"
                      button-text="Reset password"
                      class="space-y-4 md:space-y-6">

                <x-slot name="description">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Enter your new password below to reset your account password.') }}
                    </p>
                </x-slot>

                <x-slot name="fields">
                    <input type="hidden" wire:model="form.token">

                    <x-flowbite::input.text-field
                        field="form.email"
                        type="email"
                        :label="__('E-mail address')"
                        :helper="__('The email address associated with your account')"
                        required
                    />

                    <x-flowbite::input.text-field
                        field="form.password"
                        type="password"
                        label="{{ __('New password') }}"
                        helper="{{ __('At least 8 characters, including uppercase, lowercase and numbers') }}"
                        required
                        :showPassword="$showPassword"
                    />

                    <x-flowbite::input.text-field
                        field="form.password_confirmation"
                        type="password"
                        label="{{ __('Confirm new password') }}"
                        helper="{{ __('Re-enter your new password') }}"
                        required
                        :showPassword="$showPasswordConfirmation"
                    />
                </x-slot>

                <x-slot name="underForm">
                    <div class="text-center">
                        <button wire:click="requestNewLink" type="button" class="text-sm text-blue-600 hover:underline dark:text-blue-500">
                            {{ __('Request a new password reset link') }}
                        </button>
                    </div>
                </x-slot>
            </x-flowbite::form>
        </div>
    </div>
</div>
