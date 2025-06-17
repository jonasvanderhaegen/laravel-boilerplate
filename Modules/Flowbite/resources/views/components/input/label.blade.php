<label
    for="{{ $field }}"
    {{ $attributes }}
    class="{{-- placeholder & focus colours --}} {{
        $error
        ? 'text-red-500 peer-focus:text-red-500'
        : 'text-gray-500 peer-focus:text-blue-500 dark:text-gray-400 peer-focus:dark:text-white'
    }} {{-- preserve the other peer-placeholder-shown & rtl rules --}} absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4"
>
    {{ $label }}
    @if ($required)
        <span class="text-red-500">*</span>
    @endif
</label>
