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

use Exception;
use Whoa\Passport\Adaptors\Generic\Client;
use Whoa\Passport\Contracts\Entities\RedirectUriInterface;
use Whoa\Passport\Contracts\Entities\ScopeInterface;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\RedirectUriRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Traits\PassportSeedTrait;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

/**
 * @package Whoa\Tests\Passport
 */
class PassportSeedTraitTest extends TestCase
{
    use PassportSeedTrait;

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * Test seed client.
     *
     * @return void
     * @throws Exception
     */
    public function testSeedClient()
    {
        $client = (new Client())->setScopeIdentifiers(['scope_1']);
        $extraScopes = [
            'scope_2' => 'Description for scope_2',
            'scope_3' => 'Description for scope_3',
        ];
        $redirectUris = [
            'http://some-uri.foo',
        ];

        /** @var Mock $clientRepoMock */
        /** @var Mock $scopeRepoMock */
        /** @var Mock $uriRepoMock */
        $clientRepoMock = Mockery::mock(ClientRepositoryInterface::class);
        $scopeRepoMock = Mockery::mock(ScopeRepositoryInterface::class);
        $uriRepoMock = Mockery::mock(RedirectUriRepositoryInterface::class);

        $scopeRepoMock
            ->shouldReceive('create')
            ->times(2)
            ->withAnyArgs()
            ->andReturn($scopeMock = Mockery::mock(ScopeInterface::class));

        $scopeMock
            ->shouldReceive('getIdentifier')
            ->times(2)
            ->withAnyArgs()
            ->andReturnValues(['scope_2', 'scope_3']);

        $clientRepoMock
            ->shouldReceive('create')
            ->once()
            ->with($client)
            ->andReturn($client);

        $uriRepoMock
            ->shouldReceive('create')
            ->times(1)
            ->withAnyArgs()
            ->andReturn(Mockery::mock(RedirectUriInterface::class));

        /** @var Mock $intMock */
        $intMock = Mockery::mock(PassportServerIntegrationInterface::class);
        $intMock->shouldReceive('getScopeRepository')->once()->withNoArgs()->andReturn($scopeRepoMock);
        $intMock->shouldReceive('getClientRepository')->once()->withNoArgs()->andReturn($clientRepoMock);
        $intMock->shouldReceive('getRedirectUriRepository')->once()->withNoArgs()->andReturn($uriRepoMock);
        /** @var PassportServerIntegrationInterface $intMock */

        $this->seedClient($intMock, $client, $extraScopes, $redirectUris);

        // assert it executed exactly as described above, and we need at lease 1 assert to avoid PHP unit warning.
        $this->assertTrue(true);
    }
}
