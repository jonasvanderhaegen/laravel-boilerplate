<x-flowbite::html-layouts.general title="{{ __('Unauthorized') }}">
    <section class="bg-white dark:bg-gray-900">
        <div class="mx-auto max-w-screen-xl px-4 py-8 lg:px-6 lg:py-16">
            <div class="mx-auto max-w-screen-sm text-center">
                <img
                    class="mx-auto mb-4"
                    src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/404/404-computer.svg"
                    alt="Unauthorized"
                />
                <h1
                    class="mb-4 text-2xl font-extrabold text-blue-600 dark:text-blue-500"
                >
                    Unauthorized
                </h1>
                <p
                    class="mb-10 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl dark:text-white"
                >
                    Whoops! You're not authorized to access this page.
                </p>
                <p class="mb-4 text-gray-500 dark:text-gray-400">
                    Here are some helpful links instead:
                </p>
                <ul
                    class="flex items-center justify-center space-x-4 text-gray-500 dark:text-gray-400"
                >
                    <li>
                        <a
                            href="{{ route('flowbite.homepage') }}"
                            wire:navigate.hover
                            class="underline hover:text-gray-900 dark:hover:text-white"
                        >
                            Home
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('flowbite.contact') }}"
                            wire:navigate.hover
                            class="underline hover:text-gray-900 dark:hover:text-white"
                        >
                            Contact
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</x-flowbite::html-layouts.general>
