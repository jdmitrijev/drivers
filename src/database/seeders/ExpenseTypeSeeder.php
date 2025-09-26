<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseType;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Fuel (EFS)',
            'Fuel (Comdata)',
            'Insurance (Truck)',
            'Insurance (Trailer)',
            'Engine oil',
            'Tires',
            'Truck wash',
            'Trailer wash',
            'Flight ticket',
            'Tolls',
            'Parking fees',
            'Repairs & maintenance (general service)',
            'Permits & licenses',
            'Lodging/Accommodation',
            'Meals on the road',
        ];

        foreach ($names as $name) {
            ExpenseType::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}


