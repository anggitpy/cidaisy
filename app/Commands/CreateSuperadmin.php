<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\ValidationException;

class CreateSuperadmin extends BaseCommand
{
    protected $group = 'Application';
    protected $name = 'app:superadmin';
    protected $description = 'Create the first superadmin from environment-provided credentials.';

    public function run(array $params)
    {
        $provider = config('Auth')->userProvider;
        $model = new $provider();
        foreach ($model->findAll() as $existing) {
            if ($existing->inGroup('superadmin')) {
                CLI::error('A superadmin already exists. Use shield:user for subsequent account administration.');
                return EXIT_ERROR;
            }
        }
        // Environment input avoids leaking passwords into shell arguments or command history.
        $data = ['username' => (string) env('setup.username', ''), 'email' => (string) env('setup.email', ''), 'password' => (string) env('setup.password', '')];
        $validation = service('validation');
        $validation->setRules(['username' => 'required|min_length[3]|max_length[30]|regex_match[/\A[a-zA-Z0-9.]+\z/]|is_unique[users.username]', 'email' => 'required|valid_email|max_length[254]|is_unique[auth_identities.secret]', 'password' => ['required', 'max_byte[72]', 'min_length[12]']]);
        if (! $validation->run($data)) {
            CLI::error('Set setup.username, setup.email, and setup.password in your local environment.');
            foreach ($validation->getErrors() as $error) CLI::error($error);
            return EXIT_ERROR;
        }
        // Shield's form rule reads HTTP input in a CLI context. Supply the prospective user explicitly.
        $strength = service('passwords')->check($data['password'], new User($data));
        if (! $strength->isOK()) {
            CLI::error($strength->reason());
            return EXIT_ERROR;
        }
        $db = db_connect();
        $db->transStart();
        try {
            $model->save(new User($data + ['active' => 1]));
            $user = $model->findById($model->getInsertID());
            $user->syncGroups('superadmin');
            $db->transComplete();
        } catch (ValidationException $e) {
            $db->transRollback();
            CLI::error($e->getMessage());
            return EXIT_ERROR;
        }
        if (! $db->transStatus()) return EXIT_ERROR;
        CLI::write('Superadmin created. Remove setup credentials from your environment now.', 'green');
        return EXIT_SUCCESS;
    }
}
