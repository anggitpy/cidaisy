<?php
namespace Config;

class Auth extends \CodeIgniter\Shield\Config\Auth
{
    public bool $allowRegistration = false;
    public int $minimumPasswordLength = 12;
    public array $redirects = [
        'register' => 'admin', 'login' => 'admin', 'logout' => 'login',
        'force_reset' => 'admin/profile', 'permission_denied' => 'denied', 'group_denied' => 'denied',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->views = array_replace($this->views, [
            'layout' => 'layouts/auth', 'login' => 'auth/login',
            'magic-link-login' => 'auth/magic_link_form', 'magic-link-message' => 'auth/magic_link_message',
        ]);
    }

    public function loginRedirect(): string
    {
        if (session()->getTempdata('magicLogin') === true) return site_url('admin/profile');
        return parent::loginRedirect();
    }
}
