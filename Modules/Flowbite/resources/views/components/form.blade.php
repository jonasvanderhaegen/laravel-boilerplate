@props([
    'form' => null,
    'submit' => 'submit',
    'underForm' => null,
    'buttonTextRetry' => __('You can retry in'),
    'buttonText' => __('Submit'),
    'title' => null,
    'subtitle' => null,
    'status' => null
])

<div>
<form {{$attributes->merge(['class' =>''])}}
        wire:submit.prevent="{{$submit}}"
        x-data="{
            remaining: @entangle('form.secondsUntilReset'),
            timer: null,
            startTimer() {
                // clear any old timer
                if (this.timer) clearInterval(this.timer)
                // only start if remaining > 0
                if (this.remaining > 0) {
                    this.timer = setInterval(() => {
                        if (this.remaining > 0) {
                            this.remaining--
                        } else {
                            clearInterval(this.timer)
                        }
                    }, 1000)
                }
            },
        }"
        x-init="startTimer()"
        x-effect="startTimer()"
>

    @isset($title)
    <h1 @class([
      'text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white',
      'mb-0' => isset($subtitle)
    ])>{{$title}}</h1>
    @endisset

    @isset($subtitle)
        <h3 class="font-light text-gray-900 dark:text-white">{{$subtitle}}</h3>
    @endisset

    @session('status')
        @isset($status)
            {{$status}}
        @endisset
    @endsession


    <fieldset :disabled="remaining > 0" wire:loading.delay.long.attr="disabled" class="space-y-6">

        @isset($fields)
        {{$fields}}
        @endisset

        <button
            type="submit"
            {{ ! $this->canSubmit() ? 'disabled' : '' }}
            class=" cursor-pointer inline-flex items-center justify-center disabled:cursor-not-allowed w-full rounded-full bg-blue-500 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-500 focus:ring-4 focus:ring-blue-300 focus:outline-none disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-500 dark:focus:ring-blue-700 transition-opacity ease-in-out duration-200"
        >
            <svg wire:loading.delay.long.class="!opacity-100" aria-hidden="true" role="status" class="transition-opacity ease-in-out duration-200 -ms-6 opacity-0 inline w-4 h-4 me-2 text-gray-200 animate-spin " viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#1C64F2"/>
            </svg>

            <template x-if="remaining > 0">
                <span>
                    {{ $buttonTextRetry }}
                    <span
                        x-text="Math.floor(remaining / 60) + ':' + String(remaining % 60).padStart(2, '0')"
                    ></span>
                </span>
            </template>
            <template x-if="remaining === 0">
                <span>{{ $buttonText }}</span>
            </template>
        </button>


        @isset($underForm)
            {{$underForm}}
        @endisset

    </fieldset>

</form>
</div>
