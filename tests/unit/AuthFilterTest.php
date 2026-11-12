<?php

declare(strict_types=1);

use App\Filters\AuthFilter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuthFilterTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testGuestIsRedirectedToLogin(): void
    {
        $result = (new AuthFilter())->before(service('request'));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringContainsString('login', $result->getHeaderLine('Location'));
    }

    public function testSignedInUserPassesThrough(): void
    {
        $_SESSION['user_id'] = 1;

        $this->assertNull((new AuthFilter())->before(service('request')));
    }
}
