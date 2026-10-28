<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\ValidationException;

class Users extends BaseController
{
    public function index(): string
    {
        $model = auth()->getProvider();
        $search = substr(trim((string) $this->request->getGet('q')), 0, 100);
        if ($search !== '') {
            // Email lives in a Shield identity. Read-only joins keep persistence with its provider.
            $tables = config('Auth')->tables;
            $model->select($tables['users'] . '.*')->join($tables['identities'] . ' identities', 'identities.user_id = ' . $tables['users'] . '.id')
                ->where('identities.type', 'email_password')->groupStart()
                ->like($tables['users'] . '.username', $search)->orLike('identities.secret', $search)->groupEnd();
        }
        return view('admin/users/index', ['title' => 'Users', 'users' => $model->withIdentities()->withGroups()->orderBy('id', 'DESC')->paginate(15), 'pager' => $model->pager, 'search' => $search]);
    }

    public function create(): string
    {
        return view('admin/users/form', ['title' => 'Create user', 'user' => null, 'groups' => $this->assignableGroups()]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        if (! $this->validateData($data, $this->rules())) return $this->invalid();
        $groups = $this->validatedGroups();
        if ($groups === null) return redirect()->back()->with('error', 'Choose valid groups you are authorized to assign.');
        $model = auth()->getProvider();
        $db = db_connect();
        $db->transStart();
        try {
            $user = new User(['username' => $data['username'], 'email' => $data['email'], 'password' => $data['password'], 'active' => 1]);
            $model->save($user);
            $user = $model->findById($model->getInsertID());
            $user->syncGroups(...$groups);
            $user->forcePasswordReset();
            $db->transComplete();
        } catch (ValidationException $e) {
            $db->transRollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
        if (! $db->transStatus()) throw new \RuntimeException('Unable to save user.');
        return redirect()->to(site_url('admin/users/' . $user->id))->with('message', 'User created. Share the temporary password securely; a password change is required at first login.');
    }

    public function show(int $id): string
    {
        return view('admin/users/show', ['title' => 'User details', 'user' => $this->findUser($id)]);
    }

    public function edit(int $id)
    {
        $user = $this->findUser($id);
        if (! $this->canManage($user)) return $this->denied();
        return view('admin/users/form', ['title' => 'Edit user', 'user' => $user, 'groups' => $this->assignableGroups()]);
    }

    public function update(int $id)
    {
        $user = $this->findUser($id);
        if (! $this->canManage($user) || $id === (int) auth()->id()) return $this->denied();
        if (! $this->validateData($this->request->getPost(), $this->rules($id))) return $this->invalid();
        $groups = $this->validatedGroups();
        if ($groups === null) return redirect()->back()->withInput()->with('error', 'Invalid group assignment.');
        $db = db_connect();
        $db->transStart();
        $user->username = $this->request->getPost('username');
        $user->email = $this->request->getPost('email');
        try {
            auth()->getProvider()->save($user);
            $user->syncGroups(...$groups);
            $db->transComplete();
        } catch (ValidationException $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        if (! $db->transStatus()) throw new \RuntimeException('Unable to save user.');
        return redirect()->to(site_url('admin/users/' . $id))->with('message', 'User updated.');
    }

    public function status(int $id)
    {
        $user = $this->findUser($id);
        if (! $this->canManage($user) || $id === (int) auth()->id()) return $this->denied();
        if ($user->active) {
            $user->deactivate();
            auth()->getAuthenticator()->forget($user);
        } else {
            $user->activate();
        }
        return redirect()->to(site_url('admin/users/' . $id))->with('message', 'Account status updated.');
    }

    public function delete(int $id)
    {
        $user = $this->findUser($id);
        if (! $this->canManage($user) || $id === (int) auth()->id()) return $this->denied();
        auth()->getAuthenticator()->forget($user);
        auth()->getProvider()->delete($id);
        return redirect()->to(site_url('admin/users'))->with('message', 'User deleted.');
    }

    public function resetPassword(int $id)
    {
        $user = $this->findUser($id);
        if (! $this->canManage($user) || $id === (int) auth()->id()) return $this->denied();
        // Do not generate or email passwords. Shield creates a short-lived link when the user requests it.
        helper('email');
        $email = emailer(['mailType' => 'text']);
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName'))->setTo($user->email)
            ->setSubject('Password recovery requested')
            ->setMessage("An administrator requested a password change. Request your secure sign-in link at:\n" . url_to('magic-link') . "\nThen choose a new password in your profile.");
        if (! $email->send()) return redirect()->back()->with('error', 'Recovery email could not be sent. Check the mail configuration.');
        $user->forcePasswordReset();
        auth()->getAuthenticator()->forget($user);
        return redirect()->back()->with('message', 'Recovery instructions sent. A password change is required at next login.');
    }

    private function findUser(int $id): User
    {
        return auth()->getProvider()->findById($id) ?? throw PageNotFoundException::forPageNotFound();
    }

    private function canManage(User $user): bool
    {
        if (auth()->user()->inGroup('superadmin')) return true;
        // Direct permissions and privileged groups must never be editable by an ordinary administrator.
        return array_diff($user->getGroups(), ['user', 'beta']) === [] && $user->getPermissions() === [];
    }

    private function assignableGroups(): array
    {
        $groups = config('AuthGroups')->groups;
        return auth()->user()->inGroup('superadmin') ? $groups : array_intersect_key($groups, array_flip(['user', 'beta']));
    }

    private function validatedGroups(): ?array
    {
        $groups = $this->request->getPost('groups');
        if (! is_array($groups) || $groups === []) return null;
        foreach ($groups as $group) if (! is_string($group) || ! array_key_exists($group, $this->assignableGroups())) return null;
        return array_values(array_unique($groups));
    }

    private function rules(?int $id = null): array
    {
        $rules = [
            'username' => ['required', 'min_length[3]', 'max_length[30]', 'regex_match[/\A[a-zA-Z0-9.]+\z/]', 'is_unique[users.username,id,' . ($id ?? 0) . ']'],
            'email' => ['required', 'valid_email', 'max_length[254]', 'is_unique[auth_identities.secret,user_id,' . ($id ?? 0) . ']'],
        ];
        if ($id === null) {
            $rules['password'] = ['required', 'max_byte[72]', 'strong_password'];
            $rules['password_confirm'] = ['required', 'matches[password]'];
        }
        return $rules;
    }

    private function invalid()
    {
        // Passwords must not be flashed back into session storage.
        return redirect()->back()->with('errors', $this->validator->getErrors());
    }

    private function denied()
    {
        return $this->response->setStatusCode(403)->setBody(view('errors/denied'));
    }
}
