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
use Whoa\Passport\Adaptors\MySql\Token;
use Whoa\Passport\Entities\DatabaseSchema;
use PDO;

/**
 * Class TokenTest
 *
 * @package Whoa\Tests\Passport
 */
class TokenTest extends TestCase
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
            Token::FIELD_ID                => Types::INTEGER,
            Token::FIELD_ID_CLIENT         => Types::STRING,
            Token::FIELD_ID_USER           => Types::INTEGER,
            Token::FIELD_REDIRECT_URI      => Types::STRING,
            Token::FIELD_CODE              => Types::STRING,
            Token::FIELD_TYPE              => Types::STRING,
            Token::FIELD_VALUE             => Types::STRING,
            Token::FIELD_REFRESH           => Types::STRING,
            Token::FIELD_IS_SCOPE_MODIFIED => Types::BOOLEAN,
            Token::FIELD_IS_ENABLED        => Types::BOOLEAN,

            Token::FIELD_SCOPES => Types::STRING,
        ];
        $columns    = [
            Token::FIELD_ID                => 123,
            Token::FIELD_ID_CLIENT         => 'some_client_id',
            Token::FIELD_ID_USER           => 321,
            Token::FIELD_REDIRECT_URI      => 'https://acme.foo/redirect',
            Token::FIELD_CODE              => 'some_code',
            Token::FIELD_TYPE              => 'code',
            Token::FIELD_VALUE             => 'some_value',
            Token::FIELD_REFRESH           => 'some_value',
            Token::FIELD_IS_SCOPE_MODIFIED => false,
            Token::FIELD_IS_ENABLED        => true,
            Token::FIELD_SCOPES            => 'one two three',
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
        $statement->setFetchMode(PDO::FETCH_CLASS, Token::class);
        $this->assertCount(1, $clients = $statement->fetchAll());

        /** @var Token $client */
        $client = $clients[0];
        $this->assertEquals(['one', 'two', 'three'], $client->getScopeIdentifiers());
    }
}
