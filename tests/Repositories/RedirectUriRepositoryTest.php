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
use Whoa\Passport\Adaptors\Generic\RedirectUri;
use Whoa\Passport\Adaptors\Generic\RedirectUriRepository;
use Whoa\Passport\Contracts\Entities\RedirectUriInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\RedirectUriRepositoryInterface;
use Whoa\Passport\Exceptions\InvalidArgumentException;
use Whoa\Passport\Traits\DatabaseSchemaMigrationTrait;
use Whoa\Tests\Passport\TestCase;

/**
 * @package Whoa\Tests\Passport
 */
class RedirectUriRepositoryTest extends TestCase
{
    use DatabaseSchemaMigrationTrait;

    /**
     * @inheritdoc
     * @throws Exception
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
        /** @var ClientRepositoryInterface $clientRepo */
        /** @var RedirectUriRepositoryInterface $uriRepo */
        [$clientRepo, $uriRepo] = $this->createRepositories();

        $clientRepo->create(
            $client = (new Client())->setIdentifier('default_client_1')
                ->setName('Default Client 1')
                ->setDescription('Description for default client 1')
        );

        $clientIdentifier = $client->getIdentifier();
        $this->assertEmpty($uriRepo->indexClientUris($clientIdentifier));

        $uriId = $uriRepo->create(
            (new RedirectUri())
                ->setClientIdentifier($clientIdentifier)
                ->setValue('https://example.foo/boo')
        )->getIdentity();

        $this->assertNotEmpty($uris = $uriRepo->indexClientUris($clientIdentifier));
        $this->assertCount(1, $uris);
        /** @var RedirectUri $uri */
        $uri = $uris[0];
        $this->assertTrue($uri instanceof RedirectUriInterface);
        $this->assertEquals($uriId, $uri->getIdentity());
        $this->assertEquals($clientIdentifier, $uri->getClientIdentifier());
        $this->assertEquals('https://example.foo/boo', $uri->getValue());
        $this->assertTrue($uri->getCreatedAt() instanceof DateTimeImmutable);
        $this->assertNull($uri->getUpdatedAt());

        $uriRepo->update($uri);
        $sameRedirectUri = $uriRepo->read($uri->getIdentity());
        $this->assertEquals($uriId, $sameRedirectUri->getIdentity());
        $this->assertTrue($sameRedirectUri->getCreatedAt() instanceof DateTimeImmutable);
        $this->assertTrue($sameRedirectUri->getUpdatedAt() instanceof DateTimeImmutable);

        $uriRepo->delete($sameRedirectUri->getIdentity());

        $this->assertEmpty($uriRepo->indexClientUris($clientIdentifier));
    }

    /**
     * Test entities get/set methods.
     * @throws Exception
     */
    public function testEntities()
    {
        $uri = (new RedirectUri())->setValue('http://host.foo/path?param=value');
        $this->assertNotNull($uri->getUri());

        try {
            $uri->setValue('/no/host/value');
        } catch (InvalidArgumentException $exception) {
        }
        $this->assertTrue(isset($exception));
    }

    /**
     * @return array
     */
    private function createRepositories(): array
    {
        $clientRepository = new ClientRepository($this->getConnection(), $this->getDatabaseSchema());
        $uriRepository = new RedirectUriRepository($this->getConnection(), $this->getDatabaseSchema());

        return [$clientRepository, $uriRepository];
    }
}
