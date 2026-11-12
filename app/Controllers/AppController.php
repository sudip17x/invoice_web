<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Model;

/**
 * Base for all signed-in controllers.
 */
abstract class AppController extends BaseController
{
    protected $helpers = ['form', 'url', 'app'];

    protected function uid(): int
    {
        return (int) session()->get('user_id');
    }

    /**
     * Load a row only if it belongs to the signed-in user, otherwise 404.
     * This is what stops "change the id in the URL" attacks.
     *
     * @return array<string, mixed>
     */
    protected function ownedOr404(Model $model, int $id): array
    {
        $row = $model->where('user_id', $this->uid())->find($id);

        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $row;
    }
}
