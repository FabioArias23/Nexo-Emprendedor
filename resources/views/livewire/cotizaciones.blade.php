<div class="px-6 py-8 max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800 dark:text-white">💱 Cotizaciones Argentina y Región</h2>

    @if($error)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-center">
            {{ $error }}
        </div>
    @endif

    <!-- Sección: Tipos de Dólar -->
    @if(!empty($dolares))
        <div class="mb-10">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300 flex items-center">
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded mr-2">USD</span>
                Tipos de Dólar en Argentina
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($dolares as $d)
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-850 rounded-xl shadow p-4 border-l-4 border-blue-500">
                        <h4 class="font-bold text-lg text-gray-800 dark:text-white">{{ $d['nombre'] }}</h4>
                        <div class="mt-2 space-y-1 text-sm">
                            @if(isset($d['compra']))
                                <p><span class="font-medium">Compra:</span> ${{ number_format($d['compra'], 2, ',', '.') }}</p>
                            @endif
                            @if(isset($d['venta']))
                                <p><span class="font-medium">Venta:</span> ${{ number_format($d['venta'], 2, ',', '.') }}</p>
                            @endif
                            @if(isset($d['fechaActualizacion']))
                                <p class="text-xs text-gray-500 mt-1">
                                    🕒 {{ \Carbon\Carbon::parse($d['fechaActualizacion'])->format('d/m H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Sección: Monedas Extranjeras (incluye PYG) -->
    @if(!empty($monedasExtranjeras))
        <div>
            <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">🌎 Monedas Internacionales y Regionales</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($monedasExtranjeras as $m)
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-850 rounded-xl shadow p-4 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-lg text-gray-800 dark:text-white">{{ $m['nombre'] }}</h4>
                            <span class="bg-gray-200 dark:bg-gray-700 text-xs px-2 py-0.5 rounded">{{ $m['moneda'] }}</span>
                        </div>
                        <div class="mt-2 space-y-1 text-sm">
                            @if(isset($m['compra']))
                                <p><span class="font-medium">Compra:</span> ${{ number_format($m['compra'], 2, ',', '.') }}</p>
                            @endif
                            @if(isset($m['venta']))
                                <p><span class="font-medium">Venta:</span> ${{ number_format($m['venta'], 2, ',', '.') }}</p>
                            @endif
                            @if(isset($m['fechaActualizacion']))
                                <p class="text-xs text-gray-500 mt-1">
                                    🕒 {{ \Carbon\Carbon::parse($m['fechaActualizacion'])->format('d/m H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(empty($dolares) && empty($monedasExtranjeras) && !$error)
        <div class="text-center text-gray-500 py-6">
            Cargando cotizaciones...
        </div>
    @endif
</div>