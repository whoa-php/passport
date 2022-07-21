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

namespace Whoa\Passport\Adaptors\Generic;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use PDO;

use function assert;
use function is_numeric;

/**
 * @package Whoa\Passport
 */
class TokenRepository extends \Whoa\Passport\Repositories\TokenRepository
{
    /**
     * @var string
     */
    private string $modelClass;

    /**
     * @param Connection $connection
     * @param DatabaseSchemaInterface $databaseSchema
     * @param string $modelClass
     */
    public function __construct(
        Connection $connection,
        DatabaseSchemaInterface $databaseSchema,
        string $modelClass = Token::class
    ) {
        parent::__construct(
            new ClientRepository($connection, $databaseSchema),
            new ScopeRepository($connection, $databaseSchema)
        );
        $this->setConnection($connection)->setDatabaseSchema($databaseSchema);
        $this->modelClass = $modelClass;
    }

    /**
     * @inheritdoc
     */
    public function read(int $identity): ?TokenInterface
    {
        $token = parent::read($identity);

        if ($token !== null) {
            $this->addScope($token);
            $this->associateClientIdentifier($token);
        }

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function readByCode(string $code, int $expirationInSeconds): ?TokenInterface
    {
        $token = parent::readByCode($code, $expirationInSeconds);
        if ($token !== null) {
            $this->addScope($token);
            $this->associateClientIdentifier($token);
        }

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function readByValue(string $tokenValue, int $expirationInSeconds): ?TokenInterface
    {
        $token = parent::readByValue($tokenValue, $expirationInSeconds);
        if ($token !== null) {
            $this->addScope($token);
            $this->associateClientIdentifier($token);
        }

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function readByRefresh(string $refreshValue, int $expirationInSeconds): ?TokenInterface
    {
        $token = parent::readByRefresh($refreshValue, $expirationInSeconds);
        if ($token !== null) {
            $this->addScope($token);
            $this->associateClientIdentifier($token);
        }

        return $token;
    }

    /**
     * @inheritdoc
     * @param int $userId
     * @param int $expirationInSeconds
     * @param int|null $limit
     * @return array
     */
    public function readByUser(int $userId, int $expirationInSeconds, int $limit = null): array
    {
        try {
            /** @var TokenInterface[] $tokens */
            $tokens = parent::readByUser($userId, $expirationInSeconds, $limit);
            array_walk($tokens, [$this, 'associateClientIdentifier']);

            // select scope identifiers for tokens
            if (empty($tokens) === false) {
                $schema = $this->getDatabaseSchema();
                $tokenIdColumn = $schema->getTokensScopesTokenIdentityColumn();
                $scopeIdColumn = $schema->getTokensScopesScopeIdentityColumn();

                $connection = $this->getConnection();
                $query = $connection->createQueryBuilder();

                $tokenIds = array_keys($tokens);
                $query
                    ->select([$tokenIdColumn, $scopeIdColumn])
                    ->from($schema->getTokensScopesTable())
                    ->where($query->expr()->in($tokenIdColumn, $tokenIds))
                    ->orderBy($tokenIdColumn);

                $statement = $query->execute();
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $tokenScopePairs = $statement->fetchAll();

                $curTokenId = null;
                $curScopes = null;
                // set selected scopes to tokens
                foreach ($tokenScopePairs as $pair) {
                    $tokenId = $pair[$tokenIdColumn];
                    $scopeId = $this->getScopeRepository()->queryIdentifier((int)$pair[$scopeIdColumn]);

                    if ($curTokenId !== $tokenId) {
                        $assignScopes = $curTokenId !== null && empty($curScopes) === false;
                        $assignScopes ? $tokens[$curTokenId]->setScopeIdentifiers($curScopes) : null;
                        $curTokenId = $tokenId;
                        $curScopes = [$scopeId];

                        continue;
                    }

                    $curScopes[] = $scopeId;
                }
                $curTokenId === null || empty($curScopes) === true ?:
                    $tokens[$curTokenId]->setScopeIdentifiers($curScopes);
            }

            return $tokens;
        } catch (RepositoryException|DBALException $exception) {
            $message = 'Token reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @param string $tokenValue
     * @param int $expirationInSeconds
     * @return array|null
     */
    public function readPassport(string $tokenValue, int $expirationInSeconds): ?array
    {
        try {
            $statement = $this->createPassportDataQuery($tokenValue, $expirationInSeconds)->execute();
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $data = $statement->fetch();
            $result = null;
            if ($data !== false) {
                $schema = $this->getDatabaseSchema();
                $tokenId = $data[$schema->getTokensIdentityColumn()];
                assert(is_numeric($tokenId));

                // $scopes = $this->readScopeIdentifiers((int)$tokenId);
                $scopes = $this->readScopeColumns((int)$tokenId);
                $data[$schema->getTokensViewScopesColumn()] = $scopes;
                $result = $data;
            }

            return $result;
        } catch (DBALException $exception) {
            $message = 'Passport reading failed';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getClassName(): string
    {
        return $this->modelClass;
    }

    /**
     * @inheritdoc
     */
    protected function getTableNameForReading(): string
    {
        return $this->getTableNameForWriting();
    }

    /**
     * @param string $tokenValue
     * @param int $expirationInSeconds
     * @return QueryBuilder
     */
    private function createPassportDataQuery(
        string $tokenValue,
        int $expirationInSeconds
    ): QueryBuilder {
        $schema = $this->getDatabaseSchema();
        $query = $this->createEnabledTokenByColumnWithExpirationCheckQuery(
            $tokenValue,
            $schema->getTokensValueColumn(),
            $expirationInSeconds,
            $schema->getTokensValueCreatedAtColumn()
        );

        $connection = $query->getConnection();
        $tokensTableAlias = $this->getTableNameForReading();
        $usersTable = $connection->quoteIdentifier($usersTableAlias = $schema->getUsersTable());
        $usersFk = $connection->quoteIdentifier($schema->getTokensUserIdentityColumn());
        $usersPk = $connection->quoteIdentifier($schema->getUsersIdentityColumn());
        $query->innerJoin(
            $tokensTableAlias,
            $usersTable,
            $usersTableAlias,
            "$tokensTableAlias.$usersFk = $usersTableAlias.$usersPk"
        );

        return $query;
    }

    /**
     * @param TokenInterface $token
     * @return void
     * @throws RepositoryException
     */
    private function addScope(TokenInterface $token)
    {
        $token->setScopeIdentifiers($this->readScopeIdentifiers($token->getIdentity()));
    }
}
