<input
    @if($isMobile ?? false)
        wire:model="{{ $field }}"
    @else
        wire:model.live.debounce="{{ $field }}"
    @endif
    type="{{ $type }}"
    id="{{ $field }}"
    name="{{ $field }}"
    {{ $attributes }}
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    class="peer {{-- border and focus-border --}} {{
        $error
        ? 'border-red-500 focus:border-red-500 dark:border-red-500 dark:focus:border-red-500'
        : 'border-gray-200 focus:border-gray-600 dark:border-gray-700 dark:focus:border-white'
    }} {{-- text color --}} {{
        $error
        ? 'text-red-600 dark:text-red-500'
        : 'text-gray-900 dark:text-white'
    }} block w-full appearance-none border-0 border-b-2 bg-transparent px-0 py-2.5 text-sm focus:ring-0 focus:outline-none"
    placeholder=" "
/>
