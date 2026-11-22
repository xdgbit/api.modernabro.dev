<?php

namespace Database\Seeders;

use App\Models\User\UserCustomer;
use Illuminate\Database\Seeder;

class UserCustomerSeeder extends Seeder
{
    public function run(): void
    {
        UserCustomer::query()->create([
            'first_name' => 'علی',
            'last_name' => 'رضایی',
            'mobile_number' => '09338251881',
            'password' => '09338251884',
        ]);

        UserCustomer::query()->create([
            'first_name' => 'محمد',
            'last_name' => 'احمدی',
            'mobile_number' => '09338251883',
            'password' => '09338251884',

        ]);

        UserCustomer::query()->create([
            'first_name' => 'سارا',
            'last_name' => 'کریمی',
            'mobile_number' => '093384251882',
            'password' => '09338251884',

        ]);

        UserCustomer::query()->create([
            'first_name' => 'مریم',
            'last_name' => 'محمدی',
            'mobile_number' => '0935825181',
            'password' => '09338251884',

        ]);

        UserCustomer::query()->create([
            'first_name' => 'رضا',
            'last_name' => 'حسینی',
            'mobile_number' => '093382141884',
            'password' => '09338251884',

        ]);
    }
}