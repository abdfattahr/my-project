<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Supermarket;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء الأدوار مع التحقق من وجودها
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);

        // إنشاء الصلاحيات (مع التحقق من وجودها)
        $manageUsers = Permission::firstOrCreate(['name' => 'manage users']);
        $manageSupermarkets = Permission::firstOrCreate(['name' => 'manage supermarkets']);

        // تخصيص الصلاحيات للأدوار
        $adminRole->givePermissionTo($manageUsers, $manageSupermarkets);
        $vendorRole->givePermissionTo($manageSupermarkets);

        // إنشاء مستخدم admin (مع التحقق من عدم وجوده)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        // إنشاء مستخدم vendor (مع التحقق من عدم وجوده)
        $vendor = User::firstOrCreate(
            ['email' => 'vendor@example.com'],
            [
                'name' => 'Vendor User',
                'password' => bcrypt('password'),
            ]
        );
        $vendor->assignRole('vendor');

        // إنشاء متجر مرتبط بالمستخدم vendor
        $supermarket = Supermarket::firstOrCreate(
            ['name' => 'Vendor Supermarket'],
            [
                'email' => 'vendor@supermarket.com',
                'position' => 'Somewhere',
                'phone_number' => '1234567890',
                'user_id' => $vendor->id, // المتجر مرتبط بالتاجر (vendor)
            ]
        );
    }
}
