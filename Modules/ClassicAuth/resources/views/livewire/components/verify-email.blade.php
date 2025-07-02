<div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">    
    {{-- Livewire Alert Manager --}}
    <livewire:flowbite::components.alert-manager />

    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <div class="text-center">
                <svg class="mx-auto mb-4 text-yellow-400 w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                </svg>
                
                <h1 class="mb-2 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    {{ __('Verify Your Email Address') }}
                </h1>
                
                <p class="mb-4 text-gray-500 dark:text-gray-400">
                    {{ __('Before proceeding, please check your email for a verification link.') }}
                </p>
                
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('We sent a verification email to') }}
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->userEmail }}</span>
                </p>

                @if($emailSent)
                    <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('If you did not receive the email') }}
                        </p>
                        
                        @if($this->canResend)
                            <button wire:click="resendVerificationEmail" 
                                    type="button" 
                                    class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                {{ __('Click here to request another') }}
                            </button>
                        @else
                            <button type="button" 
                                    disabled 
                                    class="w-full text-white bg-gray-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-not-allowed">
                                {{ __('Please wait :seconds seconds', ['seconds' => $secondsUntilReset]) }}
                            </button>
                        @endif
                    </div>

                    <div class="text-sm">
                        <p class="mb-2 text-gray-500 dark:text-gray-400">
                            {{ __('Need to use a different email address?') }}
                        </p>
                        <button wire:click="logout" 
                                type="button" 
                                class="font-medium text-blue-600 hover:underline dark:text-blue-500">
                            {{ __('Log out and create a new account') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    let countdown = null;
    
    $wire.on('countdown-tick', () => {
        if (countdown) clearInterval(countdown);
        
        countdown = setInterval(() => {
            if ($wire.secondsUntilReset > 0) {
                $wire.secondsUntilReset--;
            } else {
                clearInterval(countdown);
            }
        }, 1000);
    });
</script>
@endscript
