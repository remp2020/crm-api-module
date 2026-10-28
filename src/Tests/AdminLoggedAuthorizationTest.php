<?php
declare(strict_types=1);

namespace Crm\ApiModule\Tests;

use Crm\AdminModule\Helpers\SecuredAdminAccess;
use Crm\ApiModule\Models\Authorization\AdminLoggedAuthorization;
use Crm\ApplicationModule\Tests\DatabaseTestCase;
use Crm\UsersModule\Repositories\UsersRepository;
use Nette\Http\Helpers;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;
use Nette\Security\UserStorage;
use PHPUnit\Framework\Attributes\DataProvider;

class AdminLoggedAuthorizationTest extends DatabaseTestCase
{
    private const CRM_URL = 'https://crm.example.com/api/v1/segments/show';

    private UsersRepository $usersRepository;

    protected function requiredRepositories(): array
    {
        return [
            UsersRepository::class,
        ];
    }

    protected function requiredSeeders(): array
    {
        return [];
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->usersRepository = $this->getRepository(UsersRepository::class);
    }

    public function testNotLoggedIn(): void
    {
        $authorization = $this->createAuthorization(loggedIn: false, admin: true, secure: true);
        $this->assertFalse($authorization->authorized());
        $this->assertSame('User not logged in', $authorization->getErrorMessage());
    }

    public function testNotAdmin(): void
    {
        $authorization = $this->createAuthorization(loggedIn: true, admin: false, secure: true);
        $this->assertFalse($authorization->authorized());
        $this->assertSame('User not admin', $authorization->getErrorMessage());
    }

    public function testInsecureAdminLogin(): void
    {
        $authorization = $this->createAuthorization(loggedIn: true, admin: true, secure: false);
        $this->assertFalse($authorization->authorized());
        $this->assertSame('Insecure admin login', $authorization->getErrorMessage());
    }

    public static function requestOriginDataProvider(): array
    {
        $strictCookie = [Helpers::StrictCookieName => '1'];

        return [
            'fetch-site same-origin' => [['Sec-Fetch-Site' => 'same-origin'], [], true],
            'fetch-site none (typed URL)' => [['Sec-Fetch-Site' => 'none'], [], true],
            'fetch-site same-site' => [['Sec-Fetch-Site' => 'same-site'], [], false],
            'fetch-site cross-site' => [['Sec-Fetch-Site' => 'cross-site'], [], false],
            'fetch-site unknown value' => [['Sec-Fetch-Site' => 'bogus'], [], false],
            'fetch-site wins over strict cookie' => [['Sec-Fetch-Site' => 'cross-site'], $strictCookie, false],
            'no fetch-site, strict cookie' => [[], $strictCookie, true],
            'no fetch-site, no strict cookie' => [[], [], false],
            'no fetch-site, matching origin ignored' => [['Origin' => 'https://crm.example.com'], [], false],
        ];
    }

    #[DataProvider('requestOriginDataProvider')]
    public function testRequestOrigin(array $headers, array $cookies, bool $expected): void
    {
        $authorization = $this->createAuthorization(loggedIn: true, admin: true, secure: true, headers: $headers, cookies: $cookies);
        $this->assertSame($expected, $authorization->authorized());
        if (!$expected) {
            $this->assertSame('Cross-origin request', $authorization->getErrorMessage());
        }
    }

    private function createAuthorization(
        bool $loggedIn,
        bool $admin,
        bool $secure,
        array $headers = ['Sec-Fetch-Site' => 'same-origin'],
        array $cookies = [],
    ): AdminLoggedAuthorization {
        $userRow = $this->usersRepository->add(
            'admin-logged-auth@example.com',
            'password',
            $admin ? UsersRepository::ROLE_ADMIN : UsersRepository::ROLE_USER,
        );

        $storage = new class ($loggedIn, new SimpleIdentity($userRow->id)) implements UserStorage {
            public function __construct(private bool $loggedIn, private IIdentity $identity)
            {
            }

            public function saveAuthentication(IIdentity $identity): void
            {
            }

            public function clearAuthentication(bool $clearIdentity): void
            {
            }

            public function getState(): array
            {
                return [$this->loggedIn, $this->identity, null];
            }

            public function setExpiration(?string $expire, bool $clearIdentity): void
            {
            }
        };

        $securedAdminAccess = $this->createStub(SecuredAdminAccess::class);
        $securedAdminAccess->method('isSecure')->willReturn($secure);

        $httpRequest = new Request(new UrlScript(self::CRM_URL), cookies: $cookies, headers: $headers);

        return new AdminLoggedAuthorization(new User($storage), $this->usersRepository, $securedAdminAccess, $httpRequest);
    }
}
