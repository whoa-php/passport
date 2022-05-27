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

namespace Whoa\Passport\Traits;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Whoa\Doctrine\Types\UuidType;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;

/**
 * @package Whoa\Passport
 */
trait DatabaseSchemaMigrationTrait
{
    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     *
     */
    protected function createDatabaseSchema(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        try {
            $this->createScopesTable($connection, $schema);
            $this->createClientsTable($connection, $schema);
            $this->createRedirectUrisTable($connection, $schema);
            $this->createTokensTable($connection, $schema);
            $this->createClientsScopesTable($connection, $schema);
            $this->createTokensScopesTable($connection, $schema);
        } catch (DBALException $exception) {
            if ($connection->isConnected() === true) {
                $this->removeDatabaseSchema($connection, $schema);
            }

            throw $exception;
        }
    }

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     */
    protected function removeDatabaseSchema(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        $manager = $connection->getSchemaManager();

        if ($manager->tablesExist([$schema->getTokensScopesTable()]) === true) {
            $manager->dropTable($schema->getTokensScopesTable());
        }
        if ($manager->tablesExist([$schema->getClientsScopesTable()]) === true) {
            $manager->dropTable($schema->getClientsScopesTable());
        }
        if ($manager->tablesExist([$schema->getTokensTable()]) === true) {
            $manager->dropTable($schema->getTokensTable());
        }
        if ($manager->tablesExist([$schema->getRedirectUrisTable()]) === true) {
            $manager->dropTable($schema->getRedirectUrisTable());
        }
        if ($manager->tablesExist([$schema->getClientsTable()]) === true) {
            $manager->dropTable($schema->getClientsTable());
        }
        if ($manager->tablesExist([$schema->getScopesTable()]) === true) {
            $manager->dropTable($schema->getScopesTable());
        }
    }

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     */
    protected function createScopesTable(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        $manager = $connection->getSchemaManager();

        $table = new Table($schema->getScopesTable());
        $table->addColumn($schema->getScopesIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setAutoincrement(true)->setUnsigned(true);
        $table->addColumn($schema->getScopesIdentifierColumn(), Types::STRING)->setNotnull(true);
        $table->addColumn($schema->getScopesUuidColumn(), UuidType::NAME)->setNotnull(true);
        $table->addColumn($schema->getScopesNameColumn(), Types::STRING)->setNotNull(true);
        $table->addColumn($schema->getScopesDescriptionColumn(), Types::TEXT)->setNotnull(false);
        $table->addColumn($schema->getScopesCreatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(true);
        $table->addColumn($schema->getScopesUpdatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->setPrimaryKey([$schema->getScopesIdentityColumn()]);
        $table->addUniqueIndex([$schema->getScopesIdentifierColumn()]);
        $table->addUniqueIndex([$schema->getScopesUuidColumn()]);
        $table->addUniqueIndex([$schema->getScopesNameColumn()]);
        $table->addUniqueIndex([
            $schema->getScopesIdentityColumn(),
            $schema->getScopesIdentifierColumn(),
            $schema->getScopesNameColumn(),
            $schema->getScopesUuidColumn(),
        ]);
        $manager->dropAndCreateTable($table);
    }

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     */
    protected function createClientsTable(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        $manager = $connection->getSchemaManager();

        $table = new Table($schema->getClientsTable());
        $table->addColumn($schema->getClientsIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setAutoincrement(true)->setUnsigned(true);
        $table->addColumn($schema->getClientsIdentifierColumn(), Types::STRING)->setNotnull(true);
        $table->addColumn($schema->getClientsUuidColumn(), UuidType::NAME)->setNotnull(true);
        $table->addColumn($schema->getClientsNameColumn(), Types::STRING)->setNotnull(true);
        $table->addColumn($schema->getClientsDescriptionColumn(), Types::STRING)->setNotnull(false);
        $table->addColumn($schema->getClientsCredentialsColumn(), Types::STRING)->setNotnull(false);
        $table->addColumn($schema->getClientsIsConfidentialColumn(), Types::BOOLEAN)->setDefault(true);
        $table->addColumn($schema->getClientsIsScopeExcessAllowedColumn(), Types::BOOLEAN)->setDefault(false);
        $table->addColumn($schema->getClientsIsUseDefaultScopeColumn(), Types::BOOLEAN)->setDefault(true);
        $table->addColumn($schema->getClientsIsCodeGrantEnabledColumn(), Types::BOOLEAN)->setDefault(false);
        $table->addColumn($schema->getClientsIsImplicitGrantEnabledColumn(), Types::BOOLEAN)->setDefault(false);
        $table->addColumn($schema->getClientsIsPasswordGrantEnabledColumn(), Types::BOOLEAN)->setDefault(false);
        $table->addColumn($schema->getClientsIsClientGrantEnabledColumn(), Types::BOOLEAN)->setDefault(false);
        $table->addColumn($schema->getClientsIsRefreshGrantEnabledColumn(), Types::BOOLEAN)->setDefault(false);
        $table->addColumn($schema->getClientsCreatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(true);
        $table->addColumn($schema->getClientsUpdatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->setPrimaryKey([$schema->getClientsIdentityColumn()]);
        $table->addUniqueIndex([$schema->getClientsIdentifierColumn()]);
        $table->addUniqueIndex([$schema->getClientsNameColumn()]);
        $table->addUniqueIndex([$schema->getClientsUuidColumn()]);
        $table->addUniqueIndex([
            $schema->getClientsIdentityColumn(),
            $schema->getClientsIdentifierColumn(),
            $schema->getClientsNameColumn(),
            $schema->getClientsUuidColumn(),
        ]);
        $manager->dropAndCreateTable($table);
    }

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     */
    protected function createRedirectUrisTable(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        $manager = $connection->getSchemaManager();

        $table = new Table($schema->getRedirectUrisTable());
        $table->addColumn($schema->getRedirectUrisIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setAutoincrement(true)->setUnsigned(true);
        $table->addColumn($schema->getRedirectUrisUuidColumn(), UuidType::NAME)->setNotnull(true);
        $table->addColumn($schema->getRedirectUrisClientIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setUnsigned(true);
        $table->addColumn($schema->getRedirectUrisValueColumn(), Types::STRING)->setNotnull(true);
        $table->addColumn($schema->getRedirectUrisCreatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(true);
        $table->addColumn($schema->getRedirectUrisUpdatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->setPrimaryKey([$schema->getRedirectUrisIdentityColumn()]);
        $table->addUniqueIndex([$schema->getRedirectUrisUuidColumn()]);
        $table->addUniqueIndex([
            $schema->getRedirectUrisIdentityColumn(),
            $schema->getRedirectUrisUuidColumn(),
        ]);

        $table->addForeignKeyConstraint(
            $schema->getClientsTable(),
            [$schema->getRedirectUrisClientIdentityColumn()],
            [$schema->getClientsIdentityColumn()],
            $this->getOnDeleteCascadeConstraint()
        );

        $manager->dropAndCreateTable($table);
    }

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     */
    protected function createTokensTable(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        $manager = $connection->getSchemaManager();

        $table = new Table($schema->getTokensTable());
        $table->addColumn($schema->getTokensIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setAutoincrement(true)->setUnsigned(true);
        $table->addColumn($schema->getTokensUuidColumn(), UuidType::NAME)->setNotnull(true);
        $table->addColumn($schema->getTokensIsEnabledColumn(), Types::BOOLEAN)->setNotnull(true)->setDefault(true);
        $table->addColumn($schema->getTokensIsScopeModified(), Types::BOOLEAN)->setNotnull(true)->setDefault(false);
        $table->addColumn($schema->getTokensClientIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setUnsigned(true);
        $table->addColumn($schema->getTokensUserIdentityColumn(), Types::INTEGER)->setNotnull(false)->setUnsigned(true);
        $table->addColumn($schema->getTokensRedirectUriColumn(), Types::STRING)->setNotnull(false);
        $table->addColumn($schema->getTokensCodeColumn(), Types::STRING)->setNotnull(false);
        $table->addColumn($schema->getTokensTypeColumn(), Types::STRING)->setNotnull(false);
        $table->addColumn($schema->getTokensValueColumn(), Types::STRING)->setNotnull(false);
        $table->addColumn($schema->getTokensRefreshColumn(), Types::STRING)->setNotnull(false);
        $table->addColumn($schema->getTokensCodeCreatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn($schema->getTokensValueCreatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn($schema->getTokensRefreshCreatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn($schema->getTokensCreatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(true);
        $table->addColumn($schema->getTokensUpdatedAtColumn(), Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->setPrimaryKey([$schema->getTokensIdentityColumn()]);
        $table->addUniqueIndex([$schema->getTokensUuidColumn()]);
        $table->addUniqueIndex([
            $schema->getTokensIdentityColumn(),
            $schema->getTokensUuidColumn(),
        ]);

        $table->addForeignKeyConstraint(
            $schema->getClientsTable(),
            [$schema->getTokensClientIdentityColumn()],
            [$schema->getClientsIdentityColumn()],
            $this->getOnDeleteCascadeConstraint()
        );

        $usersTable = $schema->getUsersTable();
        $usersIdentityColumn = $schema->getUsersIdentityColumn();
        if ($usersTable !== null && $usersIdentityColumn !== null) {
            $table->addForeignKeyConstraint(
                $usersTable,
                [$schema->getTokensUserIdentityColumn()],
                [$usersIdentityColumn],
                $this->getOnDeleteCascadeConstraint()
            );
        }

        $manager->dropAndCreateTable($table);
    }

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     */
    protected function createClientsScopesTable(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        $manager = $connection->getSchemaManager();

        $table = new Table($schema->getClientsScopesTable());
        $table->addColumn($schema->getClientsScopesIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setAutoincrement(true)->setUnsigned(true);
        $table->addColumn($schema->getClientsScopesClientIdentityColumn(), Types::INTEGER)->setNotnull(true)
            ->setUnsigned(true);
        $table->addColumn($schema->getClientsScopesScopeIdentityColumn(), Types::INTEGER)->setNotnull(true)
            ->setUnsigned(true);
        $table->setPrimaryKey([$schema->getClientsScopesIdentityColumn()]);
        $table->addUniqueIndex([
            $schema->getClientsScopesClientIdentityColumn(),
            $schema->getClientsScopesScopeIdentityColumn()
        ]);

        $table->addForeignKeyConstraint(
            $schema->getClientsTable(),
            [$schema->getClientsScopesClientIdentityColumn()],
            [$schema->getClientsIdentityColumn()],
            $this->getOnDeleteCascadeConstraint()
        );

        $table->addForeignKeyConstraint(
            $schema->getScopesTable(),
            [$schema->getClientsScopesScopeIdentityColumn()],
            [$schema->getScopesIdentityColumn()],
            $this->getOnDeleteCascadeConstraint()
        );

        $manager->dropAndCreateTable($table);
    }

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     */
    protected function createTokensScopesTable(Connection $connection, DatabaseSchemaInterface $schema): void
    {
        $manager = $connection->getSchemaManager();

        $table = new Table($schema->getTokensScopesTable());
        $table->addColumn($schema->getTokensScopesIdentityColumn(), Types::INTEGER)
            ->setNotnull(true)->setAutoincrement(true)->setUnsigned(true);
        $table->addColumn($schema->getTokensScopesTokenIdentityColumn(), Types::INTEGER)->setNotnull(true)
            ->setUnsigned(true);
        $table->addColumn($schema->getTokensScopesScopeIdentityColumn(), Types::INTEGER)->setNotnull(true)
            ->setUnsigned(true);
        $table->setPrimaryKey([$schema->getTokensScopesIdentityColumn()]);
        $table->addUniqueIndex([
            $schema->getTokensScopesTokenIdentityColumn(),
            $schema->getTokensScopesScopeIdentityColumn()
        ]);

        $table->addForeignKeyConstraint(
            $schema->getTokensTable(),
            [$schema->getTokensScopesTokenIdentityColumn()],
            [$schema->getTokensIdentityColumn()],
            $this->getOnDeleteCascadeConstraint()
        );

        $table->addForeignKeyConstraint(
            $schema->getScopesTable(),
            [$schema->getTokensScopesScopeIdentityColumn()],
            [$schema->getScopesIdentityColumn()],
            $this->getOnDeleteCascadeConstraint()
        );

        $manager->dropAndCreateTable($table);
    }

    /**
     * @return array
     */
    protected function getOnDeleteCascadeConstraint(): array
    {
        return ['onDelete' => 'CASCADE'];
    }
}
