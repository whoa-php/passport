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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Exception;
use Mockery;

/**
 * Class ClientTest
 *
 * @package Whoa\Tests\Passport
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * @return Connection
     *
     * @throws Exception
     */
    protected function createConnection(): Connection
    {
        return \Whoa\Tests\Passport\TestCase::createConnection();
    }

    /**
     * @param Connection $connection
     * @param string     $name
     * @param array      $columns
     *
     * @return void
     *
     * @throws Exception
     */
    protected function createTable(Connection $connection, string $name, array $columns): void
    {
        $manager         = $connection->getSchemaManager();
        $doctrineColumns = [];
        foreach ($columns as $columnName => $typeName) {
            $doctrineColumns[] = new Column($columnName, Type::getType($typeName));
        }
        $manager->createTable(new Table($name, $doctrineColumns));
    }

    /**
     * @param Connection $connection
     * @param string     $name
     *
     * @return void
     */
    protected function dropTable(Connection $connection, string $name): void
    {
        $connection->getSchemaManager()->dropTable($name);
    }
}
