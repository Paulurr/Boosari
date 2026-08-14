<x-layout title="Registro">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite(['resources/css/app.css', 'resources/js/sign_up.js'])
    </x-slot:head>
    <x-nav></x-nav>

    <div class="w-auto pt-15 h-auto bgcol2 lg:overflow-hidden">
          <div class=" h-270 lg:h-screen w-auto  flex items-center justify-center lg:justify-between" >
            <form method="POST" action="/sign_up" class="form-signup barpag overflow-y-auto overflow-x-hidden h-full w-full lg:h-full lg:2/5" >
                @csrf
                <div class="form-signup-cont h-full w-full lg:h-auto lg:w-full flex flex-col ">
                    <div class="form-signup-cont-deco"></div>
                    <div class="h-60 w-full flex items-center justify-center font-bold text-2xl">
                        {{ __('nav.sign_up') }}
                    </div>
                    <div class="lg:h-150 h-200 w-full flex flex-col items-center justify-evenly">
                        <div class="w-[90%] flex flex-col items-center justify-center gap-y-2">
                           <x-label 
                                name="name"
                                type="text"
                                title='{{ __("forms.box3") }}'
                                color1="var(--col3)"
                                color2="var(--col4)"
                                value="{{ old('name') }}"
                                :required=false
                            />

                            @error('name')
                                <span class="text-red-500 text-xs font-semibold text-left block w-[60%] pl-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="w-[90%] flex flex-col items-center justify-center gap-y-2">
                           <x-label 
                                name="email"
                                type="email"
                                title='{{ __("forms.box1") }}'
                                color1="var(--col3)"
                                color2="var(--col4)"
                                value="{{ old('email') }}"
                            />
                            @error('email')
                                <span class="text-red-500 text-xs font-semibold text-left block w-[60%] pl-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="w-[90%] flex flex-col items-center justify-center gap-y-2">
                            <x-label 
                                name="password"
                                type="password"
                                title='{{ __("forms.box2") }}'
                                color1="var(--col3)"
                                color2="var(--col4)"
                            />
                            @error('password')
                                <span class="text-red-500 text-xs font-semibold text-left block w-[60%] pl-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="w-[90%] flex flex-col items-center justify-center gap-y-2">
                            <x-label 
                                name="repeat_password"
                                type="password"
                                title='{{ __("forms.box4") }}'
                                color1="var(--col3)"
                                color2="var(--col4)"
                            />
                            @error('repeat_password')
                                <span class="text-red-500 text-xs font-semibold text-left block w-[60%] pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        
                    </div>
                    <br>
                    <div class="h-20 w-full flex items-center justify-center text-sm">
                        {{ __('forms.msg3') }} 
                        <a href="/log_in" class="hover:underline ml-1 col3 font-bold">
                            {{ __('nav.log_in') }}
                        </a>
                        
                    </div>
                    <br>
                    <div class="h-20 w-full flex items-center justify-center text-xs">
                        {{ __('forms.msg4') }}
                        <a href="/terms" class="hover:underline ml-1 col3 font-bold">
                            {{ __('nav.terms') }}
                        </a>
                        
                    </div>
                    <div class="h-30 w-full flex items-center justify-center">
                        <x-button 
                            color1="var(--col4)"
                            color2="var(--col3)"
                            colortext="var(--col1)"
                            class="p-2"
                        >
                            {{ __('forms.btn2') }}
                        </x-button>
                    </div>
                    <div class="h-30 w-full flex items-center justify-center text-xs">
                        {{ __('forms.msg2') }}
                    </div>
                </div>
            </form>
            <div class="overflow-hidden bgcol2 w-full h-full hidden lg:flex items-center justify-center">
                <object data="{{ asset('images/Sign_up_animated.svg') }}" type="image/svg+xml" class="object-fill h-full"></object>
            </div>
        </div>
    </div>
</x-layout>