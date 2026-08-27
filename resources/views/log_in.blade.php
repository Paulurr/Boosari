<x-layout title="Inicio de Sesión">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite(['resources/css/app.css', 'resources/js/log_in.js'])
    </x-slot:head>

     <x-nav></x-nav>
     <div class="w-auto h-auto pt-15 bgcol2">

         <div class="h-270 lg:h-screen w-auto  flex items-center justify-end">
             <div class="overflow-hidden bgcol2 w-full h-full hidden lg:flex items-center justify-center">
                 <object data="{{ asset('images/Log_in_animated.svg') }}" type="image/svg+xml" class="object-fill h-full"></object>
             </div>
             <div class="h-full w-3 bgcol2 hidden lg:block">
             </div>
             <form class="form-login h-full w-full lg:h-full lg:w-full" id="login_form" method="POST" action="/log_in">
                 @csrf
                 <div class="form-login-cont h-full w-full lg:h-full lg:w-full flex flex-col">
                     <div class="form-login-cont-deco"></div>
                     <div class="h-[20%] w-full flex items-center justify-center font-bold text-2xl">
                         {{ __('nav.log_in') }}
                     </div>
                     <div class="h-[35%] w-full flex flex-col items-center justify-evenly">
                         <div class="w-full flex flex-col items-center">
                             <x-label 
                                 name="email"
                                 type="email"
                                 title='{{ __("forms.box1") }}'
                                 color1="var(--col3)"
                                 color2="var(--col4)"
                                 value="{{ old('email') }}"
                             />
                             @error('email')
                                 <span class="text-red-500 text-xs mt-1 font-semibold">{{ __('validation.error') }}</span>
                             @enderror
                         </div>
                         <div class="w-full flex flex-col items-center">
                             <x-label 
                                 name="password"
                                 type="password"
                                 title='{{ __("forms.box2") }}'
                                 color1="var(--col3)"
                                 color2="var(--col4)"
                                 class="mb-2"
                             />
                         </div>
                     </div>
                     <br>
                     <div class="h-[2%] w-full flex items-center justify-center text-sm">
                         {{ __('forms.msg1') }}
                         <a href="/sign_up" class="hover:underline ml-1 col4 font-bold">
                                 {{ __('forms.btn1') }}
                         </a>
                     </div>
                     <br>
                     <div class="h-[14%] w-full flex items-center justify-center">
                         <x-button 
                             color1="var(--col3)"
                             color2="var(--col4)"
                             colortext="var(--col7)"
                             class="p-2"
                         >
                             {{ __('nav.log_in') }}
                         </x-button>
                     </div>
                     <div class="h-auto w-full flex items-center justify-center text-xs">
                         {{ __('forms.msg2') }}
                     </div>
                 </div>
             </form>
         </div>
     </div>
</x-layout>