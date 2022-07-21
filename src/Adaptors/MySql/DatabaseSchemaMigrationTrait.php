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

namespace Whoa\Passport\Adaptors\MySql;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Exception as DBALException;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Traits\DatabaseSchemaMigrationTrait as BaseDatabaseSchemaMigrationTrait;

/**
 * @package Whoa\Passport
 */
trait DatabaseSchemaMigrationTrait
{
    use BaseDatabaseSchemaMigrationTrait {
        BaseDatabaseSchemaMigrationTrait::createDatabaseSchema as createDatabaseTables;
        BaseDatabaseSchemaMigrationTrait::removeDatabaseSchema as removeDatabaseTables;
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     *
     */
    protected function createDatabaseSchema(DBALConnection $connection, DatabaseSchemaInterface $schema): void
    {
        try {
            $this->createDatabaseTables($connection, $schema);
            $this->createDatabaseViews($connection, $schema);
        } catch (DBALException $exception) {
            if ($connection->isConnected() === true) {
                $this->removeDatabaseSchema($connection, $schema);
            }

            throw $exception;
        }
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     *
     * @throws DBALException
     */
    protected function removeDatabaseSchema(DBALConnection $connection, DatabaseSchemaInterface $schema): void
    {
        $this->removeDatabaseViews($connection, $schema);
        $this->removeDatabaseTables($connection, $schema);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     *
     * @throws DBALException
     */
    protected function createDatabaseViews(DBALConnection $connection, DatabaseSchemaInterface $schema): void
    {
        $this->createClientsView($connection, $schema);
        $this->createTokensView($connection, $schema);
        $this->createUsersView($connection, $schema);
        $this->createPassportView($connection, $schema);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     *
     * @throws DBALException
     */
    protected function removeDatabaseViews(DBALConnection $connection, DatabaseSchemaInterface $schema): void
    {
        $this->removePassportView($connection, $schema);
        $this->removeUsersView($connection, $schema);
        $this->removeTokensView($connection, $schema);
        $this->removeClientsView($connection, $schema);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     * @return void
     * @throws DBALException
     *
     */
    private function createTokensView(DBALConnection $connection, DatabaseSchemaInterface $schema): void
    {
        $view = $schema->getTokensView();
        $tokens = $schema->getTokensTable();
        $scopes = $schema->getScopesTable();
        $intermediate = $schema->getTokensScopesTable();
        $tokensTokenId = $schema->getTokensIdentityColumn();
        $scopesScopeId = $schema->getScopesIdentityColumn();
        $scopesScopeIdentifier = $schema->getScopesIdentifierColumn();
        $intermediateTokenId = $schema->getTokensScopesTokenIdentityColumn();
        $intermediateScopeId = $schema->getTokensScopesScopeIdentityColumn();
        $scopesColumn = $schema->getTokensViewScopesColumn();

        $sql = <<< EOT
CREATE OR REPLACE VIEW {$view} AS
    SELECT
      t.*,
q      GROUP_CONCAT(DISTINCT s.{$scopesScopeIdentifier} ORDER BY s.{$scopesScopeIdentifier} ASC SEPARATOR ' ') AS {$scopesColumn}
    FROM {$tokens} AS t
      LEFT JOIN {$intermediate} AS ts ON t.{$tokensTokenId} = ts.{$intermediateTokenId}
      LEFT JOIN {$scopes} AS s ON ts.{$intermediateScopeId} = s.{$scopesScopeId}
    GROUP BY t.{$tokensTokenId};
EOT;
        $connection->executeStatement($sql);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     * @throws DBALException
     *
     */
    private function removeTokensView(DBALConnection $connection, DatabaseSchemaInterface $schema)
    {
        $view = $schema->getTokensView();
        $sql = "DROP VIEW IF EXISTS {$view}";
        $connection->executeStatement($sql);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     * @throws DBALException
     *
     */
    private function createPassportView(DBALConnection $connection, DatabaseSchemaInterface $schema): void
    {
        $tokensView = $schema->getTokensView();
        $view = $schema->getPassportView();
        $users = $schema->getUsersTable();
        $tokensUserFk = $schema->getTokensUserIdentityColumn();

        $sql = <<< EOT
CREATE OR REPLACE VIEW {$view} AS
    SELECT *
    FROM $tokensView
      LEFT JOIN $users USING ($tokensUserFk);
EOT;
        $connection->executeStatement($sql);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     * @throws DBALException
     *
     */
    private function removePassportView(DBALConnection $connection, DatabaseSchemaInterface $schema): void
    {
        $view = $schema->getPassportView();
        $sql = "DROP VIEW IF EXISTS {$view}";
        $connection->executeStatement($sql);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     * @throws DBALException
     *
     */
    private function createClientsView(DBALConnection $connection, DatabaseSchemaInterface $schema)
    {
        $view = $schema->getClientsView();
        $scopes = $schema->getClientsViewScopesColumn();
        $redirectUris = $schema->getClientsViewRedirectUrisColumn();
        $clientsScopes = $schema->getClientsScopesTable();
        $clientsUris = $schema->getRedirectUrisTable();
        $clients = $schema->getClientsTable();
        $clientsClientId = $schema->getClientsIdentityColumn();
        $clScopesClientId = $schema->getClientsScopesClientIdentityColumn();
        $clUrisClientId = $schema->getRedirectUrisClientIdentityColumn();
        $urisValue = $schema->getRedirectUrisValueColumn();
        $scopesScopeId = $schema->getScopesIdentityColumn();
        $sql = <<< EOT
CREATE VIEW {$view} AS
    SELECT
      c.*,
      GROUP_CONCAT(DISTINCT s.{$scopesScopeId} ORDER BY s.{$scopesScopeId} ASC SEPARATOR ' ') AS {$scopes},
      GROUP_CONCAT(DISTINCT u.{$urisValue}     ORDER BY u.{$urisValue} ASC SEPARATOR ' ')     AS {$redirectUris}
    FROM {$clients} AS c
      LEFT JOIN {$clientsScopes} AS s ON c.{$clientsClientId} = s.{$clScopesClientId}
      LEFT JOIN {$clientsUris}   AS u ON c.{$clientsClientId} = u.{$clUrisClientId}
    GROUP BY c.{$clientsClientId};
EOT;
        $connection->executeStatement($sql);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     * @throws DBALException
     *
     */
    private function removeClientsView(DBALConnection $connection, DatabaseSchemaInterface $schema)
    {
        $view = $schema->getClientsView();
        $sql = "DROP VIEW IF EXISTS {$view}";
        $connection->executeStatement($sql);
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     * @throws DBALException
     *
     */
    private function createUsersView(DBALConnection $connection, DatabaseSchemaInterface $schema)
    {
        $users = $schema->getUsersTable();
        if ($users !== null) {
            $view = $schema->getUsersView();
            $tokensValue = $schema->getTokensValueColumn();
            $tokensValueAt = $schema->getTokensValueCreatedAtColumn();
            $tokensScopes = $schema->getTokensViewScopesColumn();
            $tokensView = $schema->getTokensView();
            $tokensUserId = $schema->getTokensUserIdentityColumn();
            $usersUserId = $schema->getUsersIdentityColumn();
            $tokensIsEnabled = $schema->getTokensIsEnabledColumn();

            $sql = <<< EOT
CREATE OR REPLACE VIEW {$view} AS
    SELECT
        t.$tokensValue, t.$tokensValueAt, t.$tokensScopes, u.*
    FROM {$tokensView} AS t
      LEFT JOIN {$users} AS u ON t.{$tokensUserId} = u.{$usersUserId}
    WHERE $tokensIsEnabled IS TRUE;
EOT;
            $connection->executeStatement($sql);
        }
    }

    /**
     * @param DBALConnection $connection
     * @param DatabaseSchemaInterface $schema
     *
     * @return void
     * @throws DBALException
     *
     */
    private function removeUsersView(DBALConnection $connection, DatabaseSchemaInterface $schema)
    {
        $view = $schema->getUsersView();
        $sql = "DROP VIEW IF EXISTS {$view}";
        $connection->executeStatement($sql);
    }
}
