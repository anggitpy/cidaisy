<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        // EXTENSION POINT: Add future dashboard widgets here.
        return view('admin/dashboard', ['title' => 'Dashboard',
            'totalUsers' => auth()->getProvider()->countAllResults(),
            'activeUsers' => auth()->getProvider()->where('active', 1)->countAllResults()]);
    }
}
