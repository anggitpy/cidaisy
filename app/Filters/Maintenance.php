<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Maintenance implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        date_default_timezone_set((string) setting('App.timezone'));
        if (! setting('App.maintenanceMode')) return;
        // Authentication stays reachable so an administrator can recover access.
        $path = trim($request->getUri()->getPath(), '/');
        if (preg_match('~^(login(?:/.*)?|logout|auth/a/.*|denied)$~', $path)) return;
        $user = auth()->user();
        if ($user && $user->active && ! $user->isBanned() && $user->can('settings.edit')) return;
        return service('response')->setStatusCode(503)->setHeader('Retry-After', '3600')->setBody(view('errors/maintenance'));
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
