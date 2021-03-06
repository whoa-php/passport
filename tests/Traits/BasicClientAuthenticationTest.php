<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Tests\Passport\Traits;

use Laminas\Diactoros\ServerRequest;
use Whoa\OAuthServer\Exceptions\OAuthTokenBodyException;
use Whoa\Passport\Adaptors\MySql\Client;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Traits\BasicClientAuthenticationTrait;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package Whoa\Tests\Passport
 */
class BasicClientAuthenticationTest extends TestCase
{
    use BasicClientAuthenticationTrait;

    /**
     * @var PassportServerIntegrationInterface
     */
    private $integration;

    /**
     * @var Mock
     */
    private $integrationMock;

    /**
     * @var Mock
     */
    private $clientRepoMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = $this->integrationMock = Mockery::mock(PassportServerIntegrationInterface::class);
        $this->clientRepoMock = Mockery::mock(ClientRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * Test empty authorization header.
     */
    public function testNoAuthorizationHeader()
    {
        $this->expectException(OAuthTokenBodyException::class);

        $this->determineClient(
            $this->integration,
            $this->createEmptyAuthHeaderRequest(),
            $this->createNoClientIdParams()
        );
    }

    /**
     * Test invalid authorization header.
     */
    public function testUnknownClientLogin()
    {
        $this->expectException(OAuthTokenBodyException::class);

        $this->integrationMock
            ->shouldReceive('getClientRepository')->once()->withNoArgs()->andReturn($this->clientRepoMock);
        $this->clientRepoMock
            ->shouldReceive('read')->once()->withAnyArgs()->andReturn(null);

        $this->determineClient(
            $this->integration,
            $this->createUnknownClientLoginRequest(),
            $this->createNoClientIdParams()
        );
    }

    /**
     * Test not matching client identifiers.
     */
    public function testNotMatchingClientIdentifiers()
    {
        $this->expectException(OAuthTokenBodyException::class);

        $this->determineClient(
            $this->integration,
            $this->createClientAuthRequest('clientId1', 'some_password'),
            $this->createClientIdParam('clientId2')
        );
    }

    /**
     * Test invalid client credentials.
     */
    public function testInvalidClientCredentials()
    {
        $this->expectException(OAuthTokenBodyException::class);

        $credentials = 'whatever';
        $password = 'some_password';

        $client = (new Client())
            ->setCredentials($credentials);

        $this->integrationMock
            ->shouldReceive('getClientRepository')->once()->withNoArgs()->andReturn($this->clientRepoMock);
        $this->clientRepoMock
            ->shouldReceive('read')->once()->withAnyArgs()->andReturn($client);
        $this->integrationMock
            ->shouldReceive('verifyClientCredentials')->once()->with($client, $password)->andReturn(false);

        $this->determineClient(
            $this->integration,
            $this->createClientAuthRequest('clientId1', $password),
            $this->createNoClientIdParams()
        );
    }

    /**
     * Test no client password.
     */
    public function testNoClientPassword()
    {
        $this->expectException(OAuthTokenBodyException::class);

        $credentials = 'whatever';

        $client = (new Client())
            ->setCredentials($credentials);

        $this->integrationMock
            ->shouldReceive('getClientRepository')->once()->withNoArgs()->andReturn($this->clientRepoMock);
        $this->clientRepoMock
            ->shouldReceive('read')->once()->withAnyArgs()->andReturn($client);

        $this->determineClient(
            $this->integration,
            $this->createClientAuthRequest('clientId1'),
            $this->createNoClientIdParams()
        );
    }

    /**
     * @return ServerRequestInterface
     */
    private function createEmptyAuthHeaderRequest(): ServerRequestInterface
    {
        return (new ServerRequest())->withAddedHeader('Authorization', '');
    }

    /**
     * @return ServerRequestInterface
     */
    private function createUnknownClientLoginRequest(): ServerRequestInterface
    {
        return (new ServerRequest())
            ->withAddedHeader('Authorization', 'Basic ' . base64_encode('unknown_client_login'));
    }

    /**
     * @param string $login
     * @param string|null $password
     * @return ServerRequestInterface
     */
    private function createClientAuthRequest(string $login, string $password = null): ServerRequestInterface
    {
        $encoded = $password !== null ? base64_encode("$login:$password") : base64_encode("$login");

        return (new ServerRequest())
            ->withAddedHeader('Authorization', 'Basic ' . $encoded);
    }

    /**
     * @return array
     */
    private function createNoClientIdParams(): array
    {
        return [];
    }

    /**
     * @param string $clientId
     * @return array
     */
    private function createClientIdParam(string $clientId): array
    {
        return ['client_id' => $clientId];
    }
}
