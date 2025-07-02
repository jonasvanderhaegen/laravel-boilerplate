<section class="bg-gray-50 dark:bg-gray-900">
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
            Flowbite
        </a>

        {{-- Livewire Alert Manager --}}
        <livewire:flowbite::components.alert-manager />

        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <div class="text-center">
                    @if($verified)
                        <svg class="mx-auto mb-4 text-green-500 w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        
                        <h1 class="mb-2 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                            {{ __('Email Verified!') }}
                        </h1>
                        
                        <p class="mb-6 text-gray-500 dark:text-gray-400">
                            {{ __('Your email address has been successfully verified.') }}
                        </p>
                        
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('You will be redirected to your dashboard shortly...') }}
                        </p>
                        
                        <button wire:click="redirectToDashboard" 
                                type="button" 
                                class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            {{ __('Continue to Dashboard') }}
                        </button>
                    @elseif($error)
                        <svg class="mx-auto mb-4 text-red-500 w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        
                        <h1 class="mb-2 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                            {{ __('Verification Failed') }}
                        </h1>
                        
                        <p class="mb-6 text-gray-500 dark:text-gray-400">
                            {{ __('We could not verify your email address.') }}
                        </p>
                        
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('The verification link may be invalid or expired.') }}
                        </p>
                        
                        <a href="{{ route('verification.notice') }}" 
                           class="block w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            {{ __('Request New Verification Link') }}
                        </a>
                    @else
                        <div class="flex justify-center">
                            <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        
                        <h1 class="mt-4 mb-2 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                            {{ __('Verifying...') }}
                        </h1>
                        
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ __('Please wait while we verify your email address.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@script
<script>
    $wire.on('redirect-after-verification', () => {
        setTimeout(() => {
            $wire.redirectToDashboard();
        }, 3000);
    });
</script>
@endscript
