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

namespace Whoa\Passport\Package;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Data\MigrationInterface;
use Whoa\Passport\Adaptors\MySql\DatabaseSchemaMigrationTrait;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Psr\Container\ContainerInterface;

/**
 * @package Whoa\Passport
 */
class MySqlPassportMigration implements MigrationInterface
{
    use DatabaseSchemaMigrationTrait;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @inheritdoc
     */
    public function init(ContainerInterface $container): MigrationInterface
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function migrate(): void
    {
        $this->createDatabaseSchema($this->getConnection(), $this->getDatabaseSchema());
    }

    /**
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DBALException
     */
    public function rollback(): void
    {
        $this->removeDatabaseSchema($this->getConnection(), $this->getDatabaseSchema());
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return DBALConnection
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getConnection(): DBALConnection
    {
        return $this->getContainer()->get(DBALConnection::class);
    }

    /**
     * @return DatabaseSchemaInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getDatabaseSchema(): DatabaseSchemaInterface
    {
        return $this->getContainer()->get(DatabaseSchemaInterface::class);
    }
}
