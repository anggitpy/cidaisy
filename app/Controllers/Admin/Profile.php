<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Exceptions\ValidationException;

class Profile extends BaseController
{
    public function index(): string
    {
        return view('admin/profile/index', ['title' => 'My profile', 'user' => auth()->user()]);
    }

    public function update()
    {
        $user = auth()->user();
        if (! $this->validateData($this->request->getPost(), [
            'username' => ['required', 'min_length[3]', 'max_length[30]', 'regex_match[/\A[a-zA-Z0-9.]+\z/]', 'is_unique[users.username,id,' . $user->id . ']'],
            'email' => ['required', 'valid_email', 'max_length[254]', 'is_unique[auth_identities.secret,user_id,' . $user->id . ']'], 'current_password' => 'required',
        ])) return redirect()->back()->with('errors', $this->validator->getErrors());
        // Re-authenticate before changing an account identifier.
        if (! service('passwords')->verify($this->request->getPost('current_password'), $user->password_hash)) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }
        $user->username = $this->request->getPost('username');
        $user->email = $this->request->getPost('email');
        try { auth()->getProvider()->save($user); }
        catch (ValidationException $e) { return redirect()->back()->with('error', $e->getMessage()); }
        return redirect()->back()->with('message', 'Profile updated.');
    }

    public function password()
    {
        $user = auth()->user();
        // Shield marks a verified magic-link login, allowing recovery without the forgotten password.
        $magicLogin = session()->getTempdata('magicLogin') === true;
        $rules = ['password' => ['required', 'max_byte[72]', 'strong_password'], 'password_confirm' => 'required|matches[password]'];
        if (! $magicLogin) $rules['current_password'] = 'required';
        if (! $this->validateData($this->request->getPost(), $rules)) return redirect()->back()->with('errors', $this->validator->getErrors());
        if (! $magicLogin && ! service('passwords')->verify($this->request->getPost('current_password'), $user->password_hash)) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }
        $user->password = $this->request->getPost('password');
        auth()->getProvider()->save($user);
        $user->undoForcePasswordReset();
        auth()->getAuthenticator()->forget($user);
        auth()->logout();
        session()->regenerate(true);
        return redirect()->to(url_to('login'))->with('message', 'Password changed. Sign in with your new password.');
    }
}
