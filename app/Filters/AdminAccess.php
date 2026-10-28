<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAccess implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();
        // Shield skips activation checks without an email Activator. Enforce active on every protected request.
        if ($user && (! $user->active || $user->isBanned())) {
            auth()->logout();
            return redirect()->to(url_to('login'))->with('error', 'Your account is inactive. Contact an administrator.');
        }
        if ($user && ! $user->can($arguments[0] ?? 'dashboard.access')) {
            return service('response')->setStatusCode(403)->setBody(view('errors/denied'));
        }
        if ($user && $user->requiresPasswordReset() && ! str_ends_with(trim($request->getUri()->getPath(), '/'), 'admin/profile') && ! str_ends_with(trim($request->getUri()->getPath(), '/'), 'admin/profile/password')) {
            return redirect()->to(site_url('admin/profile'))->with('message', 'Please change your password to continue.');
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Cache-Control', 'no-store, private');
    }
}
