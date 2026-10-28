<?php
namespace Config;

class AuthGroups extends \CodeIgniter\Shield\Config\AuthGroups
{
    public string $defaultGroup = 'user';
    public array $groups = [
        'superadmin' => ['title' => 'Super administrator', 'description' => 'Full administrative access.'],
        'admin' => ['title' => 'Administrator', 'description' => 'Manage ordinary users and settings.'],
        'developer' => ['title' => 'Developer', 'description' => 'Inspect settings and experimental features.'],
        'user' => ['title' => 'User', 'description' => 'Dashboard and own profile.'],
        'beta' => ['title' => 'Beta tester', 'description' => 'Optional experimental access.'],
    ];
    // EXTENSION POINT: Add module permissions here and grant them in the matrix below.
    public array $permissions = [
        'dashboard.access' => 'Access dashboard', 'users.view' => 'View users',
        'users.create' => 'Create users', 'users.edit' => 'Edit users', 'users.delete' => 'Delete users',
        'settings.view' => 'View settings', 'settings.edit' => 'Change settings',
        'profile.edit' => 'Edit own profile', 'beta.access' => 'Access experimental features',
    ];
    public array $matrix = [
        'superadmin' => ['dashboard.*', 'users.*', 'settings.*', 'profile.*', 'beta.*'],
        'admin' => ['dashboard.access', 'users.view', 'users.create', 'users.edit', 'users.delete', 'settings.view', 'settings.edit', 'profile.edit'],
        'developer' => ['dashboard.access', 'settings.view', 'profile.edit', 'beta.access'],
        'user' => ['dashboard.access', 'profile.edit'],
        'beta' => ['dashboard.access', 'profile.edit', 'beta.access'],
    ];
}
