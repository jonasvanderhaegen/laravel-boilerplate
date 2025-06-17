<a
    href="{{ route($route) }}"
    wire:navigate.hover
    {{ $attributes->merge(['class' => 'flex cursor-pointer items-center justify-between text-sm font-medium text-blue-600 dark:text-blue-500']) }}
>
                <span>
                    <span class="text-xs text-gray-400 block">
                        {{$grayText}}
                    </span>
                    <span>
                        {{$blueText}}
                    </span>
                </span>

    <svg
        class="ms-2 h-4 w-4 rtl:rotate-180"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 14 10"
    >
        <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M1 5h12m0 0L9 1m4 4L9 9"
        />
    </svg>
</a>
