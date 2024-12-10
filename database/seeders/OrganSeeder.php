<?php

namespace Database\Seeders;

use App\Models\Organ;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class OrganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organList = [
            [
                'name' => 'células-tronco',
                'organ_type' => 'nervoso',
                'slug' => 'celulas-tronco'
            ],
            [
                'name' => 'Fígado',
                'organ_type' => 'digestivo',
                'slug' => 'figado'
            ],
            [
                'name' => 'Pulmões',
                'organ_type' => 'respiratório',
                'slug' => 'pulmoes'
            ],
            [
                'name' => 'Coração',
                'organ_type' => 'circulatório',
                'slug' => 'coracao'
            ],
            [
                'name' => 'Rins',
                'organ_type' => 'urinário',
                'slug' => 'rins'
            ],
            [
                'name' => 'óvulos',
                'organ_type' => 'reprodutor',
                'slug' => 'ovulos'
            ],
            [
                'name' => 'Pãncreas',
                'organ_type' => 'endócrino',
                'slug' => 'pancreas'
            ],
            [
                'name' => 'Cabelos',
                'organ_type' => 'tegumentar',
                'slug' => 'cabelos'
            ],
            [
                'name' => 'Ossos',
                'organ_type' => 'locomotor',
                'slug' => 'ossos'
            ],
            [
                'name' => 'Tímpano',
                'organ_type' => 'sensorial',
                'slug' => 'timpano'
            ],
        ];
        
        foreach($organList as $organ)
        {
            $organArr[] = [
                'id' => Uuid::uuid4(),
                'name' => $organ['name'],
                'slug' => $organ['slug'],
                'organ_type' => $organ['organ_type'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Schema::disableForeignKeyConstraints();
        Organ::truncate();
        Schema::enableForeignKeyConstraints();
        Organ::insert($organArr);
    }
}
