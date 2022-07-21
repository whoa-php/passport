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
use Doctrine\DBAL\Types\Type;
use Exception;
use Whoa\Doctrine\Types\UuidType as WhoaUuidType;
use Whoa\Passport\Adaptors\Generic\Client;
use Whoa\Passport\Adaptors\Generic\ClientRepository;
use Whoa\Passport\Adaptors\Generic\Token;
use Whoa\Passport\Adaptors\Generic\TokenRepository;
use Whoa\Passport\Adaptors\Generic\Scope;
use Whoa\Passport\Adaptors\Generic\ScopeRepository;
use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Traits\DatabaseSchemaMigrationTrait;
use Whoa\Tests\Passport\PassportServerTest;
use Whoa\Tests\Passport\TestCase;

/**
 * @package Whoa\Tests\Passport
 */
class TokenRepositoryTest extends TestCase
{
    use DatabaseSchemaMigrationTrait;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Type::hasType(WhoaUuidType::NAME) === true ?: Type::addType(WhoaUuidType::NAME, WhoaUuidType::class);

        $this->initDatabase();
    }

    /**
     * Test basic CRUD.
     * @throws Exception
     */
    public function testCrud()
    {
        /** @var TokenRepositoryInterface $tokenRepo */
        /** @var ScopeRepositoryInterface $scopeRepo */
        /** @var ClientRepositoryInterface $clientRepo */
        [$tokenRepo, $scopeRepo, $clientRepo] = $this->createRepositories();

        $newCode = (new Token())
            ->setUserIdentifier(PassportServerTest::TEST_USER_ID)
            ->setClientIdentifier('default_client_1')
            ->setCode('some-secret-code');
        $tokenRepo->inTransaction(function () use (
            $tokenRepo,
            $scopeRepo,
            $clientRepo,
            &$newCode
        ) {
            $clientRepo->create(
                $client = (new Client())->setIdentifier('default_client_1')
                    ->setName('Default Client 1')
                    ->setDescription('Description for default client 1')
            );

            $newCode = $tokenRepo->createCode($newCode);

            $scopeRepo->create(
                $scope1 = (new Scope())->setIdentifier('default_scope_1')
                    ->setName('Default scope 1')
                    ->setDescription('Description for default scope 1')
            );
            $scopeRepo->create(
                $scope2 = (new Scope())->setIdentifier('default_scope_2')
                    ->setName('Default scope 2')
                    ->setDescription('Description for default scope 2')
            );

            $tokenRepo->bindScopes($newCode->getIdentity(), [$scope1, $scope2]);
        });
        $this->assertNotNull($newCode);

        $this->assertNotNull($tokenRepo->read($newCode->getIdentity()));
        $this->assertNotNull($token = $tokenRepo->readByCode($newCode->getCode(), 10));
        $this->assertEquals(PassportServerTest::TEST_USER_ID, $token->getUserIdentifier());
        $this->assertEquals($newCode->getCode(), $token->getCode());
        $this->assertNull($tokenRepo->readByCode($newCode->getCode(), 0));
        $this->assertNull($tokenRepo->readByRefresh($newCode->getCode(), 10));
        $this->assertTrue($token instanceof TokenInterface);
        $this->assertEquals($newCode->getIdentity(), $token->getIdentity());
        $this->assertEquals($newCode->getClientIdentifier(), $token->getClientIdentifier());
        $this->assertEquals($newCode->getUserIdentifier(), $token->getUserIdentifier());
        $this->assertTrue($newCode->getCodeCreatedAt() instanceof DateTimeImmutable);
        $this->assertNull($token->getValue());
        $this->assertNull($token->getRefreshValue());
        $this->assertCount(2, $token->getScopeIdentifiers());
        $this->assertTrue($token->getCodeCreatedAt() instanceof DateTimeImmutable);
        $this->assertNull($token->getValueCreatedAt());
        $this->assertNull($token->getRefreshCreatedAt());

        $newToken = (new Token())
            ->setCode($newCode->getCode())
            ->setValue('some-token-value')
            ->setType('bearer')
            ->setRefreshValue('some-refresh-value');
        $tokenRepo->assignValuesToCode($newToken, 10);

        $sameToken = $tokenRepo->read($token->getIdentity());
        $this->assertEquals($newCode->getIdentity(), $sameToken->getIdentity());
        $this->assertEquals($newToken->getValue(), $sameToken->getValue());
        $this->assertEquals($newToken->getType(), $sameToken->getType());
        $this->assertEquals($newToken->getRefreshValue(), $sameToken->getRefreshValue());
        $this->assertTrue($sameToken->getCodeCreatedAt() instanceof DateTimeImmutable);
        $this->assertTrue($sameToken->getValueCreatedAt() instanceof DateTimeImmutable);
        $this->assertTrue($sameToken->getRefreshCreatedAt() instanceof DateTimeImmutable);

        /** @var TokenInterface[] $tokensByUser */
        $this->assertCount(1, $tokensByUser = $tokenRepo->readByUser(PassportServerTest::TEST_USER_ID, 10));
        $scopeIdentifiers = array_shift($tokensByUser)->getScopeIdentifiers();
        sort($scopeIdentifiers);
        $this->assertEquals(['default_scope_1', 'default_scope_2'], $scopeIdentifiers);

        $this->assertNotEmpty($tokenRepo->readPassport($sameToken->getValue(), 10));

        $tokenRepo->unbindScopes($sameToken->getIdentity());
        $sameToken = $tokenRepo->read($token->getIdentity());
        $this->assertCount(0, $sameToken->getScopeIdentifiers());

        $tokenRepo->disable($newCode->getIdentity());
        $this->assertNull($tokenRepo->readByCode($newCode->getCode(), 10));

        $tokenRepo->delete($newCode->getIdentity());

        $this->assertEmpty($tokenRepo->read($newCode->getIdentity()));
    }

    /**
     * Test create token (Resource Owner Credentials case).
     * @throws Exception
     */
    public function testCreateTokenWithRefresh()
    {
        /** @var TokenRepositoryInterface $tokenRepo */
        /** @var ClientRepositoryInterface $clientRepo */
        [$tokenRepo, , $clientRepo] = $this->createRepositories();

        $clientRepo->create(
            $client = (new Client())->setIdentifier('default_client_1')
                ->setName('Default Client 1')
                ->setDescription('Description for default client 1')
        );

        $unsavedToken = (new Token())
            ->setClientIdentifier($client->getIdentifier())
            ->setUserIdentifier(PassportServerTest::TEST_USER_ID)
            ->setValue('some-token')
            ->setType('bearer')
            ->setRefreshValue('refresh-token');
        $this->assertNotNull($newToken = $tokenRepo->createToken($unsavedToken));
        $this->assertGreaterThan(0, $tokenId = $newToken->getIdentity());

        $this->assertEquals($tokenId, $tokenRepo->readByValue('some-token', 10)->getIdentity());
        $this->assertEquals($tokenId, $tokenRepo->readByRefresh('refresh-token', 10)->getIdentity());
    }

    /**
     * Test disable token.
     *
     * @throws Exception
     */
    public function testDisableToken()
    {
        /** @var TokenRepositoryInterface $tokenRepo */
        /** @var ClientRepositoryInterface $clientRepo */
        [$tokenRepo, , $clientRepo] = $this->createRepositories();

        $clientRepo->create(
            $client = (new Client())->setIdentifier('default_client_1')
                ->setName('Default Client 1')
                ->setDescription('Description for default client 1')
        );
        $unsavedToken = (new Token())
            ->setClientIdentifier($client->getIdentifier())
            ->setUserIdentifier(PassportServerTest::TEST_USER_ID)
            ->setValue('some-token')
            ->setType('bearer')
            ->setRefreshValue('refresh-token');
        $this->assertNotNull($newToken = $tokenRepo->createToken($unsavedToken));
        $this->assertGreaterThan(0, $tokenId = $newToken->getIdentity());

        // re-read
        $this->assertTrue($tokenRepo->read($tokenId)->isEnabled());

        $tokenRepo->disable($tokenId);

        // re-read
        $this->assertFalse($tokenRepo->read($tokenId)->isEnabled());
    }

    /**
     * Test create disabled token.
     * @throws Exception
     */
    public function testCreateDisabledToken()
    {
        /** @var TokenRepositoryInterface $tokenRepo */
        /** @var ClientRepositoryInterface $clientRepo */
        [$tokenRepo, , $clientRepo] = $this->createRepositories();

        $clientRepo->create(
            $client = (new Client())->setIdentifier('default_client_1')
                ->setName('Default Client 1')
                ->setDescription('Description for default client 1')
        );
        $unsavedToken = (new Token())
            ->setClientIdentifier($client->getIdentifier())
            ->setUserIdentifier(PassportServerTest::TEST_USER_ID)
            ->setValue('some-token')
            ->setType('bearer')
            ->setRefreshValue('refresh-token')
            ->setDisabled();
        $this->assertNotNull($newToken = $tokenRepo->createToken($unsavedToken));
        $this->assertGreaterThan(0, $tokenId = $newToken->getIdentity());

        // re-read
        $this->assertFalse($tokenRepo->read($tokenId)->isEnabled());
    }

    /**
     * Test create token (Resource Owner Credentials case).
     *
     * @throws Exception
     */
    public function testCreateTokenWithoutRefresh()
    {
        /** @var TokenRepositoryInterface $tokenRepo */
        /** @var ClientRepositoryInterface $clientRepo */
        [$tokenRepo, , $clientRepo] = $this->createRepositories();

        $clientRepo->create(
            $client = (new Client())->setIdentifier('default_client_1')
                ->setName('Default Client 1')
                ->setDescription('Description for default client 1')
        );
        $unsavedToken = (new Token())
            ->setClientIdentifier($client->getIdentifier())
            ->setUserIdentifier(PassportServerTest::TEST_USER_ID)
            ->setValue('some-token')
            ->setType('bearer');
        $this->assertNotNull($newToken = $tokenRepo->createToken($unsavedToken));
        $this->assertGreaterThan(0, $tokenId = $newToken->getIdentity());

        $this->assertEquals($tokenId, $tokenRepo->readByValue('some-token', 10)->getIdentity());
    }

    /**
     * @return array
     */
    private function createRepositories(): array
    {
        $tokenRepo = new TokenRepository($this->getConnection(), $this->getDatabaseSchema());
        $scopeRepo = new ScopeRepository($this->getConnection(), $this->getDatabaseSchema());
        $clientRepo = new ClientRepository($this->getConnection(), $this->getDatabaseSchema());

        return [$tokenRepo, $scopeRepo, $clientRepo];
    }
}
