<span
    class="absolute start-0 top-3 cursor-pointer text-gray-500 dark:text-gray-400"
    wire:click="$toggle('showPassword')"
>
    @if ($showPassword)
        <svg
            class="h-4 w-4 text-gray-800 dark:text-white"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                d="m4 15.6 3.055-3.056A4.913 4.913 0 0 1 7 12.012a5.006 5.006 0 0 1 5-5c.178.009.356.027.532.054l1.744-1.744A8.973 8.973 0 0 0 12 5.012c-5.388 0-10 5.336-10 7A6.49 6.49 0 0 0 4 15.6Z"
            />
            <path
                d="m14.7 10.726 4.995-5.007A.998.998 0 0 0 18.99 4a1 1 0 0 0-.71.305l-4.995 5.007a2.98 2.98 0 0 0-.588-.21l-.035-.01a2.981 2.981 0 0 0-3.584 3.583c0 .012.008.022.01.033.05.204.12.402.211.59l-4.995 4.983a1 1 0 1 0 1.414 1.414l4.995-4.983c.189.091.386.162.59.211.011 0 .021.007.033.01a2.982 2.982 0 0 0 3.584-3.584c0-.012-.008-.023-.011-.035a3.05 3.05 0 0 0-.21-.588Z"
            />
            <path
                d="m19.821 8.605-2.857 2.857a4.952 4.952 0 0 1-5.514 5.514l-1.785 1.785c.767.166 1.55.25 2.335.251 6.453 0 10-5.258 10-7 0-1.166-1.637-2.874-2.179-3.407Z"
            />
        </svg>
    @else
        <svg
            class="h-4 w-4 text-gray-800 dark:text-white"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
        >
            <path
                stroke="currentColor"
                stroke-width="2"
                d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"
            />
            <path
                stroke="currentColor"
                stroke-width="2"
                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
            />
        </svg>
    @endif
</span>

<input
    wire:model.live.debounce="{{ $field }}"
    type="{{ $showPassword ? 'text' : 'password' }}"
    id="{{ $field }}"
    placeholder=" "
    class="peer {{-- border and focus-border --}} {{
        $error
        ? 'border-red-500 focus:border-red-500 dark:border-red-500 dark:focus:border-red-500'
        : 'border-gray-200 focus:border-gray-600 dark:border-gray-700 dark:focus:border-white'
    }} {{-- text color --}} {{
        $error
        ? 'text-red-600 dark:text-red-500'
        : 'text-gray-900 dark:text-white'
    }} block w-full appearance-none border-0 border-b-2 bg-transparent px-0 py-2.5 ps-6 pe-0 text-sm focus:ring-0 focus:outline-none"
/>

<!-- peer-placeholder-shown:start-6 peer-focus:start-0 -->

<label
    for="{{ $field }}"
    class="{{-- placeholder & focus colours --}} {{
        $error
        ? 'text-red-500 peer-focus:text-red-500'
        : 'text-gray-500 peer-focus:text-blue-500 dark:text-gray-400 peer-focus:dark:text-white'
    }} {{-- preserve the other peer-placeholder-shown & rtl rules --}} absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm duration-300 peer-placeholder-shown:start-6 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:start-0 peer-focus:-translate-y-6 peer-focus:scale-75 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4"
>
    {{ $label }}
</label>
