<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * @param list<string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please sign in to continue.');
        }

        return null;
    }

    /**
     * @param list<string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
