<x-flowbite::html-layouts.general title="{{ __('500 Internal Error') }}">
    <section class="bg-white dark:bg-gray-900">
        <div class="grid-cols-2 gap-8 content-center py-8 px-4 mx-auto max-w-screen-xl md:grid lg:py-16 lg:px-6">
            <div class="self-center">
                <h1 class="mb-4 text-2xl font-bold text-primary-600 dark:text-primary-500">500 Internal Error</h1>
                <p class="mb-4 text-3xl tracking-tight font-bold text-gray-900 lg:mb-10 md:text-4xl dark:text-white">Whoops! That page doesn’t exist.</p>
                <p class="mb-4 text-gray-500 dark:text-gray-400">Here are some helpful links:</p>
                <ul class="flex items-center space-x-4 text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="#" class="underline hover:text-gray-900 dark:hover:text-white">Support</a>
                    </li>
                    <li>
                        <a href="#" class="underline hover:text-gray-900 dark:hover:text-white">Search</a>
                    </li>
                </ul>
            </div>
            <img class="hidden mx-auto mb-4 md:flex" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/500/500.svg" alt="500 Server Error">
        </div>
    </section>
</x-flowbite::html-layouts.general>
