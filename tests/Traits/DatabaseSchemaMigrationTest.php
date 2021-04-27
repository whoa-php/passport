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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Whoa\Doctrine\Types\UuidType as WhoaUuidType;
use Whoa\Passport\Entities\DatabaseSchema;
use Whoa\Passport\Traits\DatabaseSchemaMigrationTrait;
use Whoa\Tests\Passport\Data\User;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

/**
 * @package Whoa\Tests\Passport
 */
class DatabaseSchemaMigrationTest extends TestCase
{
    use DatabaseSchemaMigrationTrait;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Type::hasType(WhoaUuidType::NAME) === true ?: Type::addType(WhoaUuidType::NAME, WhoaUuidType::class);
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
     * Test migration fail.
     */
    public function testMigrationFail()
    {
        $this->expectException(DBALException::class);

        /** @var Mock $connection */
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getSchemaManager')->zeroOrMoreTimes()->withNoArgs()->andReturnSelf();
        $connection->shouldReceive('dropAndCreateTable')->once()->withAnyArgs()
            ->andThrow(DBALException::invalidTableName('whatever'));
        $connection->shouldReceive('isConnected')->once()->withNoArgs()->andReturn(true);
        $connection->shouldReceive('tablesExist')->zeroOrMoreTimes()->withAnyArgs()->andReturn(false);

        /** @var Connection $connection */

        $this->createDatabaseSchema($connection, new DatabaseSchema(User::TABLE_NAME, User::FIELD_ID));
    }
}
