@error($error)
    <p
        {{ $attributes }}
        id="helper-text-explanation"
        class="mt-2 text-xs text-red-500 dark:text-red-400"
    >
        {{ $message }}
    </p>
@enderror
