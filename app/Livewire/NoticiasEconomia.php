<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class NoticiasEconomia extends Component
{
    public $noticias = [];
    public $error = '';
    public $loading = true;

    public function mount()
    {
        $this->cargarNoticias();
    }

    public function cargarNoticias()
    {
        $this->loading = true;
        $this->error = '';

        try {
            $apiKey = 'pub_fe6a1674c70749f48be268151caf990c';

            $response = Http::withOptions(['verify' => false])
                ->get('https://newsdata.io/api/1/news', [
                    'apikey' => $apiKey,
                    'country' => 'AR',
                    'language' => 'es',
                    'category' => 'business',
                    'q' => 'economía OR dólar OR inflación OR mercado OR financiero OR bolsa OR exportaciones',
                    'size' => 10
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['results']) && is_array($data['results'])) {
                    // Mostrar hasta 10 noticias, sin filtrar por imagen/título (por ahora)
                    $this->noticias = collect($data['results'])->take(10)->values()->all();
                } else {
                    $this->noticias = [];
                    $this->error = 'No se encontraron noticias económicas.';
                }
            } else {
                $this->error = "Error en la API: " . ($response->json()['message'] ?? $response->status());
                $this->noticias = [];
            }
        } catch (\Exception $e) {
            $this->error = "Error: " . substr($e->getMessage(), 0, 120);
            $this->noticias = [];
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.noticias-economia');
    }
}