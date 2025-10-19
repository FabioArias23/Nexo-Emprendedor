<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Tecnología',
            'AgroTech',
            'FinTech (Tecnología Financiera)',
            'Salud y Bienestar',
            'Educación',
            'Turismo y Hotelería',
            'Energías Renovables',
            'Comercio Electrónico',
            'Software como Servicio (SaaS)',
            'Alimentos y Bebidas',
            'Inmobiliario',
            'Impacto Social',
            'General'
        ];

        foreach ($categories as $categoryName) {
            DB::reconnect();

            Category::updateOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName)]
            );
        }
    }
}