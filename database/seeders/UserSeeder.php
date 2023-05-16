<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
        	'first_name' => 'Administrador', 
        	'last_name' => 'Logistica',
        	'user_name' => 'admin_logistica',
        	'photo' => 'default/photo.png',
        	'email' => 'calidad@logisticacollantes.es',
        	'is_admin' => 1,
        	'password' => Hash::make('Logistic4')
        ]);

        /*User::create([
        	'first_name' => 'Chofer' , 
        	'last_name' => 'Uno',
        	'user_name' => 'chofer1',
        	'photo' => 'default/photo.png',
        	'email' => 'chofer@logistica.com',
        	'is_admin' => 0,
        	'password' => Hash::make('chofer123')
        ]);*/
    }
}
