@php
    $onSettingsRoute = request()->routeIs('settings.*');
@endphp

<div>
        <button
            id="accountDropdownButton"
            data-dropdown-toggle="accountDropdown"
            type="button"
            @class([
                'cursor-pointer transition duration-200',
                'font-medium' => $onSettingsRoute,
                'opacity-60 hover:opacity-100' => ! $onSettingsRoute,
            ])
        >
            <span class="">Account</span>
        </button>
        <!-- Dropdown Menu -->
        <div
            id="accountDropdown"
            class="z-50 hidden w-full max-w-lg divide-y divide-gray-100 overflow-hidden rounded-md bg-white antialiased shadow dark:divide-gray-600 dark:bg-gray-700"
        >
            <div>
                <button
                    id="dropdownUserNameButton"
                    type="button"
                    class="dark:hover-bg-gray-700 flex w-full items-center justify-between bg-white p-4 hover:bg-gray-100 focus:outline-none dark:bg-gray-700 dark:hover:bg-gray-600"
                    type="button"
                >
                    <span class="sr-only">Open user menu</span>
                    <div class="flex w-full items-center justify-between">
                        <div class="flex items-center">
                            <img
                                src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/avatars/jese-leos.png"
                                class="mr-3 h-8 w-8 rounded-md"
                                alt="Jese avatar"
                            />
                            <div class="text-left">
                                <div
                                    class="mb-0.5 leading-none font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ auth()->user()->name }}
                                </div>
                                <div
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ auth()->user()->email }}
                                </div>
                            </div>
                        </div>
                    </div>
                </button>
            </div>

            <div class="grid items-stretch py-4 sm:grid-cols-2">
                <ul
                    class="mb-4 border-b border-gray-100 pb-4 text-sm font-medium sm:mb-0 sm:border-0 sm:pb-0"
                >
                    {{--
                    @auth
                        @if (auth()->user()->onboarding()->inProgress())
                            <li class="px-4 pb-2">
                                <p
                                    class="text-base text-gray-900 dark:text-white"
                                >
                                    Finish all onboarding steps
                                </p>
                            </li>
                            @foreach (auth()->user()->onboarding()->steps as $step)
                                <li class="">
                                    <a
                                        href="{{ $step->link }}"
                                        {{ $step->complete() ? 'disabled' : '' }}
                                        wire:navigate.hover
                                        title=""
                                        class="block flex space-x-3 px-4 py-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <x-customtheme::icons.check-circle
                                            @class([
                                                'h-5 w-5 flex-shrink-0',
                                                'text-green-500' => $step->complete(),
                                            ])
                                        />

                                        <span>
                                            {{ $loop->iteration }}.
                                            {{ $step->title }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li class="px-4 pb-2">
                                <p
                                    class="text-base text-gray-900 dark:text-white"
                                >
                                    Profile completeness
                                </p>
                            </li>
                        @endif
                    @endauth
                    --}}
                </ul>

                <ul
                    class="border-l border-gray-100 text-sm font-medium dark:border-gray-600"
                >
                    <li class="px-4 pb-2">
                        <p
                            class="text-base font-medium text-gray-900 dark:text-white"
                        >
                            My Account
                        </p>
                    </li>
                    {{--
                    <li>
                        <a
                            href="{{ route('settings.profile') }}"
                            wire:navigate.hover
                            title=""
                            class="group flex items-center gap-2 px-4 py-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                        >
                            <svg
                                class="h-4 w-4 text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a9 9 0 0 0 5-1.5 4 4 0 0 0-4-3.5h-2a4 4 0 0 0-4 3.5 9 9 0 0 0 5 1.5Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                ></path>
                            </svg>
                            Edit my profile
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('settings.preferences') }}"
                            wire:navigate.hover
                            title=""
                            class="group flex items-center gap-2 px-4 py-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                        >
                            <svg
                                class="h-4 w-4 text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a9 9 0 0 0 5-1.5 4 4 0 0 0-4-3.5h-2a4 4 0 0 0-4 3.5 9 9 0 0 0 5 1.5Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                ></path>
                            </svg>
                            Adjust my preferences
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('settings.account') }}"
                            wire:navigate.hover
                            title=""
                            class="group flex items-center gap-2 px-4 py-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                        >
                            <svg
                                class="h-4 w-4 text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 21v-9m3-4H7.5a2.5 2.5 0 1 1 0-5c1.5 0 2.9 1.3 3.9 2.5M14 21v-9m-9 0h14v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8ZM4 8h16a1 1 0 0 1 1 1v3H3V9a1 1 0 0 1 1-1Zm12.2-5c-3 0-5.5 5-5.5 5h5.5a2.5 2.5 0 0 0 0-5Z"
                                />
                            </svg>
                            Account and security
                        </a>
                    </li>
                    --}}

                    <li>
                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                            class="w-full"
                        >
                            @csrf
                            <button
                                type="submit"
                                title=""
                                class="group flex w-full cursor-pointer items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 dark:text-red-500 dark:hover:bg-gray-600"
                            >
                                <svg
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"
                                    />
                                </svg>
                                Log out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
</div>
