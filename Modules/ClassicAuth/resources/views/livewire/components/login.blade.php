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

                @env('local')
                    <div class="inline-flex rounded-md shadow-xs w-full" role="group">
                        <button wire:click="fillCorrectUser" type="button" class="cursor-pointer px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                            Correct user
                        </button>
                        <button wire:click="fillCorrectUser(true)" type="button" class="cursor-pointer px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                            Correct user with remember me
                        </button>
                        <button wire:click="fillIncorrectUser" type="button" class="cursor-pointer px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                            Wrong user
                        </button>
                    </div>
                @endenv

                    <x-flowbite::form-link route="flowbite.register" :grayText="__('Don\'t have an account yet?')" :blueText="__('Create an account')" />
                    <x-flowbite::form-link route="flowbite.password.request" :grayText="__('Oops, forgot password?')" :blueText="__('Request to reset password')" />
            </x-slot>
        </x-flowbite::form>
        </div>
    </div>
</div>
