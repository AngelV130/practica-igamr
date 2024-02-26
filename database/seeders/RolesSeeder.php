<?php

namespace Database\Seeders;

use App\Models\Roles;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles para agregar
        $roles = [
            ['name' => 'Administrador'],
            ['name' => 'Usuario'],
        ];

        // Inserta roles en la base de datos
        Roles::insert($roles);
    }
}
