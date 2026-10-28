<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class Settings extends BaseController
{
    public function index(): string
    {
        $values = [];
        foreach (['siteName', 'siteDescription', 'contactEmail', 'timezone', 'dateFormat', 'maintenanceMode'] as $key) $values[$key] = setting('App.' . $key);
        return view('admin/settings/index', ['title' => 'Application settings', 'values' => $values]);
    }

    public function update()
    {
        // This also demonstrates authorization inside a controller, in addition to the route filter.
        if (! auth()->user()->can('settings.edit')) return $this->response->setStatusCode(403);
        $data = $this->request->getPost();
        if (! $this->validateData($data, [
            'siteName' => 'required|max_length[100]', 'siteDescription' => 'permit_empty|max_length[500]',
            'contactEmail' => 'required|valid_email|max_length[254]', 'timezone' => 'required|max_length[64]',
            'dateFormat' => 'required|in_list[d M Y,Y-m-d,d/m/Y,m/d/Y]', 'maintenanceMode' => 'permit_empty|in_list[1]',
        ])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        if (! in_array($data['timezone'], timezone_identifiers_list(), true)) return redirect()->back()->withInput()->with('error', 'Choose a valid timezone.');
        $db = db_connect();
        $db->transStart();
        foreach (['siteName', 'siteDescription', 'contactEmail', 'timezone', 'dateFormat'] as $key) service('settings')->set('App.' . $key, $data[$key]);
        service('settings')->set('App.maintenanceMode', ($data['maintenanceMode'] ?? '') === '1');
        $db->transComplete();
        if (! $db->transStatus()) throw new \RuntimeException('Unable to save settings.');
        return redirect()->back()->with('message', 'Settings saved.');
    }
}
