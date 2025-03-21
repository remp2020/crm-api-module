<?php
declare(strict_types=1);

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Models\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Repositories\ApiAccessTokensRepository;
use Crm\ApiModule\Repositories\ApiTokensRepository;
use Crm\ApplicationModule\Tests\DatabaseTestCase;

class BearerTokenAuthorizationTest extends DatabaseTestCase
{
    private BearerTokenAuthorization $bearerTokenAuthorization;

    private ApiTokensRepository $apiTokensRepository;

    protected function requiredRepositories(): array
    {
        return [
            ApiTokensRepository::class,
            ApiAccessTokensRepository::class,
        ];
    }

    protected function requiredSeeders(): array
    {
        return [];
    }

    public function setUp(): void
    {
        parent::setUp();

        unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['REMOTE_ADDR']);

        $this->bearerTokenAuthorization = $this->inject(BearerTokenAuthorization::class);
        $this->apiTokensRepository = $this->getRepository(ApiTokensRepository::class);
    }

    public function testWithWrongBearerReturnsFalse()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer weohewg0983h4touihweg980h34ty';
        $this->assertFalse($this->bearerTokenAuthorization->authorized());
    }

    public function testAuthorizedWithTokenForAllIp()
    {
        $tokenRepository = $this->container->getByType(ApiTokensRepository::class);
        $tokenId = $tokenRepository->generate('test', '*');
        $token = $tokenRepository->find($tokenId);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token->token;
        $this->assertTrue($this->bearerTokenAuthorization->authorized());
    }

    public function testCannotAccessWithInactiveToken()
    {
        $tokenRepository = $this->container->getByType(ApiTokensRepository::class);
        $tokenId = $tokenRepository->generate('test', '*', false);
        $token = $tokenRepository->find($tokenId);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token->token;
        $this->assertFalse($this->bearerTokenAuthorization->authorized());
    }

    public function testCannotAccessWithoutAuthorizationHeader()
    {
        $this->assertFalse($this->bearerTokenAuthorization->authorized());
    }

    public function testSimpleIpRestrictions()
    {
        $_SERVER['REMOTE_ADDR'] = '43.24.132.64';

        $tokenId = $this->apiTokensRepository->generate('test', '43.24.132.64');
        $token = $this->apiTokensRepository->find($tokenId);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token->token;
        $this->assertTrue($this->bearerTokenAuthorization->authorized());
    }

    public function testSimpleIpRestrictionsWithWrongIp()
    {
        $_SERVER['REMOTE_ADDR'] = '123.32.36.15';

        $tokenId = $this->apiTokensRepository->generate('test', '43.24.132.64');
        $token = $this->apiTokensRepository->find($tokenId);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token->token;
        $this->assertFalse($this->bearerTokenAuthorization->authorized());
    }

    public function testSimpleIpRestrictionsWithMultipleIps()
    {
        $_SERVER['REMOTE_ADDR'] = '123.32.36.15';

        $tokenId = $this->apiTokensRepository->generate('test', '43.24.132.64,123.32.36.15');
        $token = $this->apiTokensRepository->find($tokenId);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token->token;
        $this->assertTrue($this->bearerTokenAuthorization->authorized());
    }

    public function testRangeIpRestring()
    {
        $_SERVER['REMOTE_ADDR'] = '123.32.36.15';

        $tokenId = $this->apiTokensRepository->generate('test', '43.24.132.64,123.32.36.0/24');
        $token = $this->apiTokensRepository->find($tokenId);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token->token;
        $this->assertTrue($this->bearerTokenAuthorization->authorized());

        $_SERVER['REMOTE_ADDR'] = '123.32.37.15';
        $this->assertFalse($this->bearerTokenAuthorization->authorized());
    }
}
