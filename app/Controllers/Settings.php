<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Settings extends AppController
{
    private const FIELDS = ['company_name', 'address', 'phone', 'email', 'tax_id', 'bank_details', 'invoice_prefix', 'footer_note'];

    public function index(): string
    {
        return view('settings/index', [
            'title' => 'Company settings',
            'row'   => (new SettingModel())->forUser($this->uid()),
        ]);
    }

    public function save(): RedirectResponse
    {
        $model    = new SettingModel();
        $current  = $model->forUser($this->uid());
        $data     = $this->request->getPost(self::FIELDS);
        $file     = $this->request->getFile('logo');
        $hasLogo  = $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;

        if ($hasLogo && ! $this->validate([
            'logo' => 'uploaded[logo]|max_size[logo,1024]|is_image[logo]|mime_in[logo,image/jpg,image/jpeg,image/png]|ext_in[logo,png,jpg,jpeg]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($hasLogo) {
            $dir = WRITEPATH . 'uploads';
            is_dir($dir) || mkdir($dir, 0775, true);

            $name = $file->getRandomName();
            $file->move($dir, $name);

            if (! empty($current['logo_path'])) {
                @unlink($dir . '/' . basename($current['logo_path']));
            }

            $data['logo_path'] = $name;
        }

        if (! $model->update($current['id'], $data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/settings')->with('success', 'Settings saved.');
    }

    /**
     * Logo lives in writable/ (not public), so it is served through an authenticated route.
     */
    public function logo(): ResponseInterface
    {
        $row  = (new SettingModel())->forUser($this->uid());
        $path = WRITEPATH . 'uploads/' . basename((string) $row['logo_path']);

        if (empty($row['logo_path']) || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setContentType((string) mime_content_type($path))
            ->setBody((string) file_get_contents($path));
    }
}
