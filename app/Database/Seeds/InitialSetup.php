<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSetup extends Seeder
{
    public function run()
    {
        // Defaults already come from App config; initialize missing values without overwriting customization.
        $settings = service('settings');
        foreach (['siteName', 'siteDescription', 'contactEmail', 'timezone', 'dateFormat', 'maintenanceMode'] as $key) {
            $settings->set('App.' . $key, $settings->get('App.' . $key));
        }
        echo "Application settings initialized. Create your account with php spark app:superadmin.\n";
    }
}
