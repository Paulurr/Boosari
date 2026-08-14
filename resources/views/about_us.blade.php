

<x-layout title="Sobre nosotros">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css'])
    </x-slot:head>

    <x-nav/>
<div class="bgcol2 w-full pt-15">
    <div class="h-auto w-full">
        <object data="{{ asset('images/About_us.svg') }}" type="image/svg+xml" class="w-full object-contain"></object>
    </div>
    <div class="h-auto pt-15 pb-15 w-full bgcol1">
        <h1 class="col7 text-4xl lg:text-6xl font-bold text-center">
            Nuestra Mision
        </h1>
    </div>
    <div class="h-auto w-auto bgcol1 pb-15">
        <div class="h-auto w-full">
            <object data="{{ asset('images/BarAppere.svg') }}" type="image/svg+xml" class="w-full object-contain"></object>
        </div>
        <div class=" h-100 lg:pl-50 lg:pr-50 pr-10 text-sm lg:text-lg pl-10 bgcol4 mt-5 mb-5 w-full flex items-center justify-center">
            <p class="col1 text-center">
                Ayudar a nuestros usuarios a gestionar su dinero de manera más eficiente mediante la automatización, organización y visualización gráfica de diversos tipos de datos financieros. Nuestra plataforma busca simplificar el control de ingresos, gastos, ahorros y presupuestos, permitiendo que la información financiera sea accesible, clara y fácil de interpretar. A través de procesos automatizados, se reduce el tiempo dedicado al registro manual de datos y se minimizan posibles errores, mientras que la organización estructurada de la información facilita su consulta y seguimiento. Además, la representación gráfica de los datos permite identificar tendencias, patrones de consumo y oportunidades de mejora financiera, apoyando a los usuarios en la toma de decisiones más informadas y en el logro de una administración responsable y eficiente de sus recursos económicos.            
            </p>
        </div>
        <div class="h-auto w-full rotate-180">
            <object data="{{ asset('images/BarAppere.svg') }}" type="image/svg+xml" class="w-full object-contain"></object>
        </div>
    </div>
    <x-footer/>
    
</div>
</x-layout>