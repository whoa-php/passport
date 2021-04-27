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

use Doctrine\DBAL\Types\Types;
use Exception;
use Whoa\Passport\Adaptors\MySql\Client;
use Whoa\Passport\Entities\DatabaseSchema;
use PDO;

/**
 * Class ClientTest
 *
 * @package Whoa\Tests\Passport
 */
class ClientTest extends TestCase
{
    /**
     * Test client's constructor.
     *
     * @throws Exception
     */
    public function testConstructor()
    {
        $connection = $this->createConnection();
        $types      = [
            Client::FIELD_ID                        => Types::STRING,
            Client::FIELD_NAME                      => Types::STRING,
            Client::FIELD_DESCRIPTION               => Types::STRING,
            Client::FIELD_CREDENTIALS               => Types::STRING,
            Client::FIELD_IS_CONFIDENTIAL           => Types::BOOLEAN,
            Client::FIELD_IS_USE_DEFAULT_SCOPE      => Types::BOOLEAN,
            Client::FIELD_IS_SCOPE_EXCESS_ALLOWED   => Types::BOOLEAN,
            Client::FIELD_IS_CODE_GRANT_ENABLED     => Types::BOOLEAN,
            Client::FIELD_IS_IMPLICIT_GRANT_ENABLED => Types::BOOLEAN,
            Client::FIELD_IS_PASSWORD_GRANT_ENABLED => Types::BOOLEAN,
            Client::FIELD_IS_CLIENT_GRANT_ENABLED   => Types::BOOLEAN,
            Client::FIELD_IS_REFRESH_GRANT_ENABLED  => Types::BOOLEAN,

            Client::FIELD_SCOPES        => Types::STRING,
            Client::FIELD_REDIRECT_URIS => Types::STRING,
        ];
        $columns    = [
            Client::FIELD_ID                        => 'some_id',
            Client::FIELD_NAME                      => 'some_name',
            Client::FIELD_DESCRIPTION               => 'description',
            Client::FIELD_CREDENTIALS               => 'secret',
            Client::FIELD_IS_CONFIDENTIAL           => false,
            Client::FIELD_IS_USE_DEFAULT_SCOPE      => false,
            Client::FIELD_IS_SCOPE_EXCESS_ALLOWED   => false,
            Client::FIELD_IS_CODE_GRANT_ENABLED     => false,
            Client::FIELD_IS_IMPLICIT_GRANT_ENABLED => false,
            Client::FIELD_IS_PASSWORD_GRANT_ENABLED => false,
            Client::FIELD_IS_CLIENT_GRANT_ENABLED   => false,
            Client::FIELD_IS_REFRESH_GRANT_ENABLED  => false,
            Client::FIELD_SCOPES                    => 'one two three',
            Client::FIELD_REDIRECT_URIS             => 'https://acme.foo/redirect',
        ];

        $this->createTable($connection, DatabaseSchema::TABLE_CLIENTS, $types);
        $connection->insert(DatabaseSchema::TABLE_CLIENTS, $columns, $types);

        // now read from SqLite table as it was MySql view or table
        $query     = $connection->createQueryBuilder();
        $statement = $query
            ->select(['*'])
            ->from(DatabaseSchema::TABLE_CLIENTS)
            ->setMaxResults(1)
            ->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Client::class);
        $clients = $statement->fetchAll();

        $this->dropTable($connection, DatabaseSchema::TABLE_CLIENTS);

        $this->assertCount(1, $clients);

        /** @var Client $client */
        $client = $clients[0];
        $this->assertEquals(['one', 'two', 'three'], $client->getScopeIdentifiers());
    }
}
