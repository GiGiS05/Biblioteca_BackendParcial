<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bibliotecario = Role::firstOrCreate(['name'=>'bibliotecario', 'guard_name'=>'api']);
        $docente = Role::firstOrCreate(['name'=> 'docente','guard_name'=> 'api']);
        $estudiante = Role::firstOrCreate(['name'=> 'estudiante','guard_name'=> 'api']);
        
        //Permisos del bibliotecario
        collect([
            'update book',
            'create book',
            'delete book',
            'view book',
            'viewAny book',
            'viewAny loan',
        ])->each(function (string $permission) use ($bibliotecario) {
            Permission::firstOrCreate(['name'=> $permission, 'guard_name'=> 'api']);
            $bibliotecario->givePermissionTo($permission);
        });

        //Permisos del docente
        collect([
            'viewAny book',
            'view book',
            'create loan',
            'create returnLoan',
            'viewAny loan',
        ])->each(function (string $permission) use ($docente){
            Permission::firstOrCreate(['name'=> $permission, 'guard_name'=>'api']);
            $docente->givePermissionTo($permission);
        });

        //Permisos del estudiante
        collect([
            'view book',
            'viewAny book',
            'create loan',
            'create returnLoan',
            'viewAny loan',
        ])->each(function (string $permission) use ($estudiante) {
            Permission::firstOrCreate(['name'=> $permission, 'guard_name'=> 'api']);
            $estudiante->givePermissionTo($permission);
        });

        //Crear usuarios
        $bibliotecarioUser = User::firstOrCreate([
            'email'=>'bibliotecario@example.com',
        ], [
            'name'=> 'Lucas Bibliotecario',
            'email' => 'bibliotecario@example.com',
            'password'=> bcrypt('password'),
        ]);

        $bibliotecarioUser->assignRole($bibliotecario);

        $docenteUser = User::firstOrCreate([
            'email'=> 'docente@example.com',
        ], [
            'name'=> 'Pepe Docente',
            'email'=> 'docente@example.com',
            'password'=> bcrypt('password'),
        ]);

        $docenteUser->assignRole($docente);

        $estudianteUser = User::firstOrCreate([
            'email'=>'estudiante@example.com',
        ], [
            'name'=> 'Marco Estudiante',
            'email'=> 'estudiante@example.com',
            'password'=> bcrypt('password'),
        ]);

        $estudianteUser->assignRole($estudiante);
    }
}
