<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'vendor']);
        // إنشاء الأدوار
        $adminRole = Role::create(['name' => 'admin']);
        $vendorRole = Role::create(['name' => 'vendor']);

        // تعيين الأذونات إذا كنت بحاجة إليها
        // يمكنك إضافة أذونات خاصة هنا للمستخدمين الذين لديهم الأدوار المختلفة
        // مثال: إنشاء إذن جديد
        // $permission = Permission::create(['name' => 'edit products']);
        // $adminRole->givePermissionTo($permission);

        // إضافة المستخدمين للأدوار
        $adminUser = User::where('email', 'admin@example.com')->first();
        $vendorUser = User::where('email', 'vendor@example.com')->first();

        // تعيين دور "admin" للمستخدم
        if ($adminUser) {
            $adminUser->assignRole('admin');
        }

        // تعيين دور "vendor" للمستخدم
        if ($vendorUser) {
            $vendorUser->assignRole('vendor');
        }
    }
}
