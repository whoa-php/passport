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

namespace Whoa\Tests\Passport\Repositories;

use DateTimeImmutable;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\ConnectionException as DBALConnectionException;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Whoa\Passport\Adaptors\Generic\Client;
use Whoa\Passport\Adaptors\Generic\ClientRepository;
use Whoa\Passport\Adaptors\Generic\RedirectUri;
use Whoa\Passport\Adaptors\Generic\RedirectUriRepository;
use Whoa\Passport\Adaptors\Generic\Scope;
use Whoa\Passport\Adaptors\Generic\ScopeRepository;
use Whoa\Passport\Adaptors\Generic\Token;
use Whoa\Passport\Adaptors\Generic\TokenRepository;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\RedirectUriRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use Whoa\Passport\Traits\DatabaseSchemaMigrationTrait;
use Whoa\Tests\Passport\TestCase;
use Mockery;
use ReflectionException;
use ReflectionMethod;

/**
 * @package Whoa\Tests\Passport
 */
class BadDatabaseTest extends TestCase
{
    use DatabaseSchemaMigrationTrait;

    /**
     * Test repository error handling.
     */
    public function testClientIndex(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->index();
    }

    /**
     * Test repository error handling.
     */
    public function testClientCreate(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->create(new Client());
    }

    /**
     * Test repository error handling.
     */
    public function testClientBindScopeIdentifiers(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->bindScopeIdentifiers('fakeClientId1', [1]);
    }

    /**
     * Test repository error handling.
     */
    public function testClientUnbindScopeIdentifiers(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->unbindScopes('fakeClientId1');
    }

    /**
     * Test repository error handling.
     */
    public function testClientRead(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->read('fakeClientId1');
    }

    /**
     * Test repository error handling.
     */
    public function testClientReadScopeIdentifiers(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->readScopeIdentifiers('fakeClientId1');
    }

    /**
     * Test repository error handling.
     */
    public function testClientReadRedirectUriStrings(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->readRedirectUriStrings('fakeClientId1');
    }

    /**
     * Test repository error handling.
     */
    public function testClientUpdate(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->update(new Client());
    }

    /**
     * Test repository error handling.
     */
    public function testClientDelete(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createClientRepository()->delete('fakeClientId1');
    }

    /**
     * Test repository error handling.
     */
    public function testRedirectUriIndexClientUris(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createRedirectUriRepository()->indexClientUris('fakeClientId1');
    }

    /**
     * Test repository error handling.
     */
    public function testRedirectUriCreate(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createRedirectUriRepository()->create(new RedirectUri());
    }

    /**
     * Test repository error handling.
     */
    public function testRedirectUriRead(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createRedirectUriRepository()->read(1);
    }

    /**
     * Test repository error handling.
     */
    public function testRedirectUriUpdate(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createRedirectUriRepository()->update(new RedirectUri());
    }

    /**
     * Test repository error handling.
     */
    public function testRedirectUriDelete(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createRedirectUriRepository()->delete(1);
    }

    /**
     * Test repository error handling.
     */
    public function testScopeIndex(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createScopeRepository()->index();
    }

    /**
     * Test repository error handling.
     */
    public function testScopeCreate(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createScopeRepository()->create(new Scope());
    }

    /**
     * Test repository error handling.
     */
    public function testScopeRead(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createScopeRepository()->read(1);
    }

    /**
     * Test repository error handling.
     */
    public function testScopeUpdate(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createScopeRepository()->update(new Scope());
    }

    /**
     * Test repository error handling.
     */
    public function testScopeDelete(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createScopeRepository()->delete(1);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenCreateCode(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->createCode((new Token())->setCode('fakeCode'));
    }

    /**
     * Test repository error handling.
     */
    public function testTokenAssignValuesToCode(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->assignValuesToCode((new Token())->setCode('fakeCode'), 123);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenCreateToken(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->createToken(new Token());
    }

    /**
     * Test repository error handling.
     */
    public function testTokenBindScopeIdentifiers(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->bindScopeIdentifiers(1, ['fakeToken1']);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenUnbindScopes(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->unbindScopes(1);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenRead(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->read(1);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenReadByCode(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->readByCode('fakeCode', 123);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenReadByUser(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->readByUser(1, 123);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenReadPassport(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->readPassport('fakeToken', 123);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenReadScopeIdentifiers(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->readScopeIdentifiers(1);
    }

    /**
     * Test repository error handling.
     */
    public function testTokenUpdateValues(): void
    {
        $this->expectException(RepositoryException::class);

        $this->createTokenRepository()->updateValues(new Token());
    }

    /**
     * Add test coverage to internal method.
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testInTransactionAddCoverage(): void
    {
        $this->expectException(RepositoryException::class);

        $connection = Mockery::mock(DBALConnection::class);
        $connection->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnUndefined();
        $connection->shouldReceive('commit')->once()->withNoArgs()->andThrow(new DBALConnectionException());

        /** @var DBALConnection $connection */

        $repo = new ClientRepository($connection, $this->initDefaultDatabaseSchema());
        $method = new ReflectionMethod(ClientRepository::class, 'inTransaction');

        $method->setAccessible(true);
        // the exception thrown in the closure will be ignored
        $method->invoke($repo, function () {
        });
    }

    /**
     * Add test coverage to internal method.
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testGetDateTimeForDbAddCoverage(): void
    {
        $this->expectException(RepositoryException::class);

        $connection = Mockery::mock(DBALConnection::class);
        $connection->shouldReceive('getDatabasePlatform')->once()->withNoArgs()->andThrow(new DBALException());

        /** @var DBALConnection $connection */

        $repo = new ClientRepository($connection, $this->initDefaultDatabaseSchema());
        $method = new ReflectionMethod(ClientRepository::class, 'getDateTimeForDb');

        $method->setAccessible(true);
        // the exception thrown in the closure will be ignored
        $method->invoke($repo, new DateTimeImmutable());
    }

    /**
     * Add test coverage to internal method.
     *
     * @throws ReflectionException
     */
    public function testIgnoreExceptionAddCoverage(): void
    {
        $repo = $this->createClientRepository();
        $method = new ReflectionMethod(ClientRepository::class, 'ignoreException');
        $closure = function () {
            throw new Exception();
        };

        $method->setAccessible(true);
        // the exception thrown in the closure will be ignored
        $method->invoke($repo, $closure);

        $this->assertTrue(true);
    }

    /**
     * @return ClientRepositoryInterface
     */
    private function createClientRepository(): ClientRepositoryInterface
    {
        return new ClientRepository($this->initDummyConnection(), $this->initDefaultDatabaseSchema());
    }

    /**
     * @return RedirectUriRepositoryInterface
     */
    private function createRedirectUriRepository(): RedirectUriRepositoryInterface
    {
        return new RedirectUriRepository($this->initDummyConnection(), $this->initDefaultDatabaseSchema());
    }

    /**
     * @return ScopeRepositoryInterface
     */
    private function createScopeRepository(): ScopeRepositoryInterface
    {
        return new ScopeRepository($this->initDummyConnection(), $this->initDefaultDatabaseSchema());
    }

    /**
     * @return TokenRepositoryInterface
     */
    private function createTokenRepository(): TokenRepositoryInterface
    {
        return new TokenRepository($this->initDummyConnection(), $this->initDefaultDatabaseSchema());
    }

    /**
     * @return DBALConnection
     */
    private function initDummyConnection(): ?DBALConnection
    {
        try {
            $this->setConnection($connection = static::createConnection());

            return $connection;
        } catch (Exception $exception) {
            $this->assertTrue(false, 'There is a problem with test database connection.');
        }

        return null;
    }
}
