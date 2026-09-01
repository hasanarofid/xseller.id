<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roProducts = [
            [
                'type' => 'ro',
                'name' => 'HAZAPRO',
                'price' => 125000,
                'quantity' => 1,
                'points' => 1,
                'description' => 'Herbal 3in1 Hazapro kualitas terbaik.',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'BANDAWASA',
                'price' => 125000,
                'quantity' => 1,
                'points' => 1,
                'description' => 'Kapsul Herbal Bandawasa persendian.',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'OXIBUMIN',
                'price' => 125000,
                'quantity' => 1,
                'points' => 1,
                'description' => '90% Asli Ekstrak Ikan Gabus Oxibumin.',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'Growfit',
                'price' => 125000,
                'quantity' => 2,
                'points' => 1,
                'description' => 'Susu kambing etawa nutrisi anak Growfit Kids.',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'Growfit (Lambung Gembira)',
                'price' => 125000,
                'quantity' => 2,
                'points' => 1,
                'description' => 'Minuman herbal Lambung Gembira nutrisi sehat.',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'Etawa Ajwa',
                'price' => 125000,
                'quantity' => 2,
                'points' => 1,
                'description' => 'Susu lambung ceria Etawa Ajwa kurma.',
                'is_active' => true,
            ],
        ];

        $poProducts = [
            [
                'type' => 'po',
                'name' => 'HERBAQUEENA',
                'price' => 550000,
                'quantity' => 3,
                'points' => 2,
                'description' => 'Produk herbal Herbaqueena premium.',
                'is_active' => true,
            ],
            [
                'type' => 'po',
                'name' => 'XSELLER BEE',
                'price' => 550000,
                'quantity' => 12,
                'points' => 2,
                'description' => 'Madu murni Xseller Bee kualitas super.',
                'is_active' => true,
            ],
        ];

        foreach ($roProducts as $item) {
            Product::updateOrCreate(
                ['name' => $item['name'], 'type' => 'ro'],
                $item
            );
        }

        foreach ($poProducts as $item) {
            Product::updateOrCreate(
                ['name' => $item['name'], 'type' => 'po'],
                $item
            );
        }
    }
}
