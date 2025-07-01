<div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">    {{-- Livewire Alert Manager --}}

    <livewire:flowbite::components.alert-manager />

    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">

            <x-flowbite::form :form="$this->form"
                      submit="submit"
                      :title="__('Sign in to your account')"
                      button-text="Log in"
                      class="space-y-4 md:space-y-6">

            <x-slot name="fields">

                <x-flowbite::input.text-field
                    field="form.email"
                    type="email"
                    :label="__('E-mail address')"
                    :helper="__('For example: john.doe@example.com')"
                    required
                />

                <x-flowbite::input.text-field
                    field="form.password"
                    type="password"
                    label="{{ __('password') }}"
                    helper="{{ __('Your current password') }}"
                    required
                    :showPassword="$showPassword"
                />

                <div class="">
                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <input
                                id="remember"
                                wire:model.live="form.remember"
                                aria-describedby="remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border border-gray-300 bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
                            />
                        </div>
                        <div class="ml-3 text-sm">
                            <label
                                for="remember"
                                class="text-gray-500 dark:text-gray-300"
                            >
                                Remember me
                            </label>
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="underForm">
                <x-flowbite::form-link route="flowbite.register" :grayText="__('Don\'t have an account yet?')" :blueText="__('Create an account')" />
            </x-slot>
        </x-flowbite::form>
        </div>
    </div>
</div>
