<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الصلاحيات
        $permissions = [
            // إدارة الكتب
            'books.view',
            'books.create',
            'books.edit',
            'books.delete',
            
            // إدارة الملفات
            'files.view',
            'files.upload',
            'files.edit',
            'files.delete',
            'files.manage',
            
            // معالجة الذكاء الاصطناعي
            'ai.process',
            'ai.history',
            'ai.results',
            
            // إدارة المستخدمين
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // إدارة الأدوار والصلاحيات
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // إدارة التوجيهات
            'prompts.view',
            'prompts.create',
            'prompts.edit',
            'prompts.delete',
            
            // الوصول للوحة الإدارة
            'admin.access',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // إنشاء الأدوار
        $roles = [
            'super_admin' => $permissions, // جميع الصلاحيات
            'admin' => [
                'books.view', 'books.create', 'books.edit', 'books.delete',
                'files.view', 'files.upload', 'files.edit', 'files.delete', 'files.manage',
                'ai.process', 'ai.history', 'ai.results',
                'users.view', 'users.create', 'users.edit',
                'prompts.view', 'prompts.create', 'prompts.edit',
                'admin.access',
            ],
            'moderator' => [
                'books.view', 'books.create', 'books.edit',
                'files.view', 'files.upload', 'files.edit',
                'ai.process', 'ai.history',
                'users.view',
                'prompts.view',
            ],
            'user' => [
                'books.view',
                'files.view',
                'ai.process',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo($rolePermissions);
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
