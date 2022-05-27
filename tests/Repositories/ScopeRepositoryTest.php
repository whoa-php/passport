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
use Whoa\Passport\Adaptors\Generic\Scope;
use Whoa\Passport\Adaptors\Generic\ScopeRepository;
use Whoa\Passport\Contracts\Entities\ScopeInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Traits\DatabaseSchemaMigrationTrait;
use Whoa\Tests\Passport\TestCase;

/**
 * @package Whoa\Tests\Passport
 */
class ScopeRepositoryTest extends TestCase
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
        $repo = $this->createRepository();

        $this->assertEmpty($repo->index());

        $repo->create(
            (new Scope())
                ->setIdentifier('default_scope_1')
                ->setName('Default scope 1')
                ->setDescription('Description for default scope 1')
        );

        $this->assertNotEmpty($scopes = $repo->index());
        $this->assertCount(1, $scopes);
        /** @var Scope $scope */
        $scope = $scopes[0];
        $this->assertTrue($scope instanceof ScopeInterface);
        $this->assertEquals(1, $scope->getIdentity());
        $this->assertEquals('default_scope_1', $scope->getIdentifier());
        $this->assertEquals('Default scope 1', $scope->getName());
        $this->assertEquals('Description for default scope 1', $scope->getDescription());
        $this->assertTrue($scope->getCreatedAt() instanceof DateTimeImmutable);
        $this->assertNull($scope->getUpdatedAt());

        $scope->setDescription(null);
        $repo->update($scope);
        $sameScope = $repo->read($scope->getIdentifier());
        $this->assertNull($sameScope->getDescription());
        $this->assertTrue($sameScope->getCreatedAt() instanceof DateTimeImmutable);
        $this->assertTrue($sameScope->getUpdatedAt() instanceof DateTimeImmutable);

        $repo->delete($sameScope->getIdentifier());

        $this->assertEmpty($repo->index());
    }

    /**
     * @return ScopeRepositoryInterface
     */
    private function createRepository(): ScopeRepositoryInterface
    {
        return new ScopeRepository($this->getConnection(), $this->getDatabaseSchema());
    }
}
