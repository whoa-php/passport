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

namespace Whoa\Tests\Passport\Adaptors\PostgreSql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Whoa\Doctrine\Types\UuidType as WhoaUuidType;
use Whoa\Passport\Adaptors\PostgreSql\DatabaseSchemaMigrationTrait;
use Whoa\Passport\Adaptors\PostgreSql\DbDateFormatTrait;
use Whoa\Passport\Entities\DatabaseSchema;
use Mockery;
use Mockery\Mock;

/**
 * Class ClientTest
 *
 * @package Whoa\Tests\Passport
 */
class MigrationTraitTest extends TestCase
{
    const MY_EX_MARKER = 'my_ex_marker';

    use DbDateFormatTrait, DatabaseSchemaMigrationTrait {
        DatabaseSchemaMigrationTrait::createDatabaseViews as parentCreateDatabaseViews;
        DatabaseSchemaMigrationTrait::removeDatabaseViews as parentRemoveDatabaseViews;
    }

    /**
     * @var bool
     */
    private $isThrowInDummyCreate = false;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Type::hasType(WhoaUuidType::NAME) === true ?: Type::addType(WhoaUuidType::NAME, WhoaUuidType::class);
    }

    /**
     * We'll build a dummy test for covering some very basics of migration script and
     * then will test the actual methods separately.
     *
     * @throws Exception
     */
    public function testDummyCreateAndDeleteSchema()
    {
        $schema     = new DatabaseSchema();
        $connection = $this->createConnection();

        $this->createDatabaseSchema($connection, $schema);

        $this->isThrowInDummyCreate = true;
        $gotException               = false;
        try {
            $this->createDatabaseSchema($connection, $schema);
        } catch (DBALException $exception) {
            $this->assertEquals(static::MY_EX_MARKER, $exception->getMessage());
            $gotException = true;
        }
        $this->assertTrue($gotException);
    }

    /**
     * Test create views.
     *
     * @throws Exception
     */
    public function testCreateViews()
    {
        /** @var Mock $connection */
        $connection = Mockery::mock(Connection::class);
        $schema     = new DatabaseSchema('users_table');

        // Should we set expectations with specific SQL values?
        // I have a feeling it's better to have more generic expectations.

        $connection->shouldReceive('executeStatement')->times(4)->withAnyArgs()->andReturnUndefined();

        /** @var Connection $connection */

        $this->parentCreateDatabaseViews($connection, $schema);

        // mocks will do the actual checks
        $this->assertTrue(true);
    }

    /**
     * Test create views.
     *
     * @throws Exception
     */
    public function testRemoveViews()
    {
        /** @var Mock $connection */
        $connection = Mockery::mock(Connection::class);
        $schema     = new DatabaseSchema('users_table');

        // Should we set expectations with specific SQL values?
        // I have a feeling it's better to have more generic expectations.

        $connection->shouldReceive('executeStatement')->times(4)->withAnyArgs()->andReturnUndefined();

        /** @var Connection $connection */

        $this->parentRemoveDatabaseViews($connection, $schema);

        // mocks will do the actual checks
        $this->assertTrue(true);
    }

    /**
     * Test database format.
     *
     * @throws Exception
     */
    public function testDbFormat()
    {
        $this->assertNotEmpty($this->getDbDateFormat());
    }

    /**
     * @param Connection     $connection
     * @param DatabaseSchema $schema
     *
     * @return void
     *
     * @throws DBALException
     */
    protected function createDatabaseViews(Connection $connection, DatabaseSchema $schema)
    {
        assert($connection || $schema);

        // do nothing as here and test this functionality separately.
        if ($this->isThrowInDummyCreate === true) {
            throw new DBALException(self::MY_EX_MARKER);
        }
    }

    /**
     * @param Connection     $connection
     * @param DatabaseSchema $schema
     *
     * @return void
     */
    protected function removeDatabaseViews(Connection $connection, DatabaseSchema $schema)
    {
        assert($connection || $schema);

        // do nothing as here and test this functionality separately.
    }
}
