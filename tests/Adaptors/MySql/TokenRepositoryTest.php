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

namespace Whoa\Tests\Passport\Adaptors\MySql;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Exception;
use Whoa\Passport\Adaptors\MySql\TokenRepository;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Entities\DatabaseSchema;

/**
 * @package Whoa\Tests\Passport
 */
class TokenRepositoryTest extends TestCase
{
    const TEST_TOKEN_VALUE = 'some_token';

    /**
     * Test read passport.
     *
     * @throws Exception
     */
    public function testReadPassport()
    {
        $connection = $this->createConnection();
        $schema     = new DatabaseSchema('users_table', 'id_user');
        $this->preparePassportTable($connection, $schema);


        /** @var Connection $connection */
        /** @var DatabaseSchemaInterface $schema */

        $repository = new TokenRepository($connection, $schema);
        $this->assertNotEmpty($repository->readPassport(self::TEST_TOKEN_VALUE, 3600));
    }

    /**
     * Emulate database problems.
     *
     * @throws Exception
     */
    public function testReadPassportFromBadDatabase()
    {
        $this->expectException(\Whoa\Passport\Exceptions\RepositoryException::class);

        $connection = $this->createConnection();
        $schema     = new DatabaseSchema('users_table', 'id_user');

        $repository = new TokenRepository($connection, $schema);
        $this->assertNotEmpty($repository->readPassport(self::TEST_TOKEN_VALUE, 3600));
    }

    /**
     * @param Connection     $connection
     * @param DatabaseSchema $schema
     *
     * @return void
     *
     * @throws Exception
     */
    private function preparePassportTable(Connection $connection, DatabaseSchema $schema)
    {
        // emulate view with table
        $types = [
            $schema->getTokensIdentityColumn()       => Types::INTEGER,
            $schema->getTokensValueColumn()          => Types::STRING,
            $schema->getTokensViewScopesColumn()     => Types::STRING,
            $schema->getTokensIsEnabledColumn()      => Types::BOOLEAN,
            $schema->getTokensValueCreatedAtColumn() => Types::DATETIME_IMMUTABLE,
        ];
        $data  = [
            $schema->getTokensIdentityColumn()       => 1,
            $schema->getTokensValueColumn()          => self::TEST_TOKEN_VALUE,
            $schema->getTokensViewScopesColumn()     => 'one two three',
            $schema->getTokensIsEnabledColumn()      => true,
            $schema->getTokensValueCreatedAtColumn() => new DateTimeImmutable(),
        ];

        $this->createTable($connection, $schema->getPassportView(), $types);
        $connection->insert($schema->getPassportView(), $data, $types);
    }
}
