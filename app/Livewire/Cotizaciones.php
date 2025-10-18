<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class Cotizaciones extends Component
{
    public $dolares = [];
    public $monedasExtranjeras = [];
    public $error = null;

    public function mount()
    {
        $this->cargarCotizaciones();
    }

    public function cargarCotizaciones()
    {
        try {
            // Obtener todos los tipos de dólar
            $dolaresResponse = Http::timeout(8)
                ->get('https://dolarapi.com/v1/dolares');

            if (!$dolaresResponse->successful()) {
                throw new \Exception('Error al obtener tipos de dólar');
            }

            $this->dolares = $dolaresResponse->json();

            // Obtener monedas extranjeras (incluye PYG, EUR, BRL, etc.)
            $monedasResponse = Http::timeout(8)
                ->get('https://dolarapi.com/v1/cotizaciones');

            if (!$monedasResponse->successful()) {
                throw new \Exception('Error al obtener monedas extranjeras');
            }

            $allMonedas = $monedasResponse->json();

            // Filtrar solo las que NO son USD (para evitar duplicados)
            $this->monedasExtranjeras = array_filter($allMonedas, fn($m) => $m['moneda'] !== 'USD');

        } catch (\Exception $e) {
            $this->error = 'Error al cargar cotizaciones: ' . substr($e->getMessage(), 0, 100);
            $this->dolares = [];
            $this->monedasExtranjeras = [];
        }
    }

    public function render()
    {
        return view('livewire.cotizaciones');
    }
}