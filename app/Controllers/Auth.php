<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 600; // 10 minutes
    private const GENERIC_ERROR = 'Invalid credentials, or the account is temporarily locked.';

    public function showLogin(): string|RedirectResponse
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attempt(): RedirectResponse
    {
        $rules = ['email' => 'required|valid_email', 'password' => 'required'];

        if (! $this->validate($rules)) {
            return $this->fail();
        }

        $users = new UserModel();
        $user  = $users->where('email', (string) $this->request->getPost('email'))->first();

        if ($user !== null && $user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
            return $this->fail();
        }

        $password = (string) $this->request->getPost('password');

        if ($user === null || ! (int) $user['is_active'] || ! password_verify($password, $user['password_hash'])) {
            if ($user !== null) {
                $attempts = (int) $user['failed_attempts'] + 1;
                $users->update($user['id'], $attempts >= self::MAX_ATTEMPTS
                    ? ['failed_attempts' => 0, 'locked_until' => date('Y-m-d H:i:s', time() + self::LOCK_SECONDS)]
                    : ['failed_attempts' => $attempts]);
            }

            return $this->fail();
        }

        $users->update($user['id'], ['failed_attempts' => 0, 'locked_until' => null]);

        $session = session();
        $session->regenerate(true); // new session ID on login (prevents fixation)
        $session->set([
            'user_id'   => (int) $user['id'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to('/login');
    }

    private function fail(): RedirectResponse
    {
        return redirect()->to('/login')->withInput()->with('error', self::GENERIC_ERROR);
    }
}
