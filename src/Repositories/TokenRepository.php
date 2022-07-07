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

namespace Whoa\Passport\Repositories;

use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Throwable;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use PDO;
use Whoa\Passport\Traits\Repository\ClientRepositoryTrait;
use Whoa\Passport\Traits\Repository\ScopeRepositoryTrait;

use function assert;
use function is_int;

/**
 * @package Whoa\Passport
 */
abstract class TokenRepository extends BaseRepository implements TokenRepositoryInterface
{
    use ClientRepositoryTrait;
    use ScopeRepositoryTrait;

    /**
     * Constructor
     */
    public function __construct(
        ClientRepositoryInterface $clientRepo,
        ScopeRepositoryInterface $scopeRepo
    ) {
        $this->setClientRepository($clientRepo);
        $this->setScopeRepository($scopeRepo);
    }

    /**
     * @inheritdoc
     * @param TokenInterface $code
     * @return TokenInterface
     * @throws DBALDriverException
     */
    public function createCode(TokenInterface $code): TokenInterface
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $values = [
                $schema->getTokensUuidColumn() => $code->getUuid(),
                $schema->getTokensUserIdentityColumn() => $code->getUserIdentifier(),
                $schema->getTokensCodeColumn() => $code->getCode(),
                $schema->getTokensIsScopeModified() => $code->isScopeModified(),
                $schema->getTokensCodeCreatedAtColumn() => $now,
                $schema->getTokensCreatedAtColumn() => $now,
            ];

            if (empty($value = $this->associateClientIdentity($code, $schema)) === false) {
                $values += $value;
            }

            if (empty($scopeIdentifiers = $code->getScopeIdentifiers()) === false) {
                $this->inTransaction(function () use ($code, $values, $scopeIdentifiers) {
                    $this->createResource($values);
                    $code->setIdentity($identity = $this->getLastInsertId());
                    $this->bindScopeIdentifiers($identity, $scopeIdentifiers);
                });
            } else {
                $this->createResource($values);
                $code->setIdentity($this->getLastInsertId());
            }

            $code->setUuid()->setCodeCreatedAt($now)->setCreatedAt($now);

            return $code;
        } catch (RepositoryException $exception) {
            $message = 'Token code creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function assignValuesToCode(TokenInterface $token, int $expirationInSeconds): void
    {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $dbNow = $this->createTypedParameter($query, $now);

            $earliestExpired = $this->ignoreException(function () use ($now, $expirationInSeconds): DateTimeImmutable {
                /** @var DateTimeImmutable $now */
                return $now->sub(new DateInterval("PT{$expirationInSeconds}S"));
            });

            $schema = $this->getDatabaseSchema();
            $query
                ->update($this->getTableNameForWriting())
                ->where($schema->getTokensCodeColumn() . '=' . $this->createTypedParameter($query, $token->getCode()))
                ->andWhere(
                    $schema->getTokensCodeCreatedAtColumn() . '>' .
                    $this->createTypedParameter($query, $earliestExpired)
                )
                ->set($schema->getTokensValueColumn(), $this->createTypedParameter($query, $token->getValue()))
                ->set($schema->getTokensTypeColumn(), $this->createTypedParameter($query, $token->getType()))
                ->set($schema->getTokensValueCreatedAtColumn(), $dbNow);

            if ($token->getRefreshValue() !== null) {
                $query
                    ->set(
                        $schema->getTokensRefreshColumn(),
                        $this->createTypedParameter($query, $token->getRefreshValue())
                    )->set($schema->getTokensRefreshCreatedAtColumn(), $dbNow);
            }

            $numberOfUpdated = $query->execute();
            assert(is_int($numberOfUpdated) === true);
        } catch (DBALException $exception) {
            $message = 'Assigning token values by code failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @param TokenInterface $token
     * @return TokenInterface
     * @throws DBALDriverException
     */
    public function createToken(TokenInterface $token): TokenInterface
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $hasRefresh = $token->getRefreshValue() !== null;
            $values = [
                $schema->getTokensUuidColumn() => $token->getUuid(),
                $schema->getTokensUserIdentityColumn() => $token->getUserIdentifier(),
                $schema->getTokensValueColumn() => $token->getValue(),
                $schema->getTokensTypeColumn() => $token->getType(),
                $schema->getTokensIsScopeModified() => $token->isScopeModified(),
                $schema->getTokensIsEnabledColumn() => $token->isEnabled(),
                $schema->getTokensCreatedAtColumn() => $now,
                $schema->getTokensCreatedAtColumn() => $now
            ];
            $values += $hasRefresh === false ? [
                $schema->getTokensValueCreatedAtColumn() => $now,
            ] : [
                $schema->getTokensValueCreatedAtColumn() => $now,
                $schema->getTokensRefreshColumn() => $token->getRefreshValue(),
                $schema->getTokensRefreshCreatedAtColumn() => $now,
            ];

            if (empty($value = $this->associateClientIdentity($token, $schema, $values)) === false) {
                $values += $value;
            }

            if (empty($scopeIdentifiers = $token->getScopeIdentifiers()) === false) {
                $this->inTransaction(function () use ($token, $values, $scopeIdentifiers) {
                    $this->createResource($values);
                    $token->setIdentity($identity = $this->getLastInsertId());
                    $this->bindScopeIdentifiers($identity, $scopeIdentifiers);
                });
            } else {
                $this->createResource($values);
                $token->setIdentity($this->getLastInsertId());
            }

            $token->setUuid()->setValueCreatedAt($now);
            if ($hasRefresh === true) {
                $token->setRefreshCreatedAt($now);
            }

            return $token;
        } catch (RepositoryException $exception) {
            $message = 'Token creation failed';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritDoc
     * @throws DBALDriverException
     */
    public function bindScopes(int $identity, iterable $scopes): void
    {
        $this->bindScopeIdentities($identity, $this->queryScopeIdentities($scopes));
    }

    /**
     * @inheritDoc
     * @throws DBALDriverException
     */
    public function bindScopeIdentifiers(int $identity, iterable $scopeIdentifiers): void
    {
        $this->bindScopeIdentities($identity, $this->queryScopeIdentities($scopeIdentifiers));
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function unbindScopes(int $identifier): void
    {
        try {
            $schema = $this->getDatabaseSchema();
            $this->deleteBelongsToManyRelationshipIdentities(
                $schema->getTokensScopesTable(),
                $schema->getTokensScopesTokenIdentityColumn(),
                $identifier
            );
        } catch (RepositoryException $exception) {
            $message = 'Unbinding token scopes failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function read(int $identity): ?TokenInterface
    {
        try {
            return $this->readResource($identity);
        } catch (RepositoryException $exception) {
            $message = 'Token reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function readByCode(string $code, int $expirationInSeconds): ?TokenInterface
    {
        $schema = $this->getDatabaseSchema();
        return $this->readEnabledTokenByColumnWithExpirationCheck(
            $code,
            $schema->getTokensCodeColumn(),
            $expirationInSeconds,
            $schema->getTokensCodeCreatedAtColumn()
        );
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function readByValue(string $tokenValue, int $expirationInSeconds): ?TokenInterface
    {
        $schema = $this->getDatabaseSchema();
        return $this->readEnabledTokenByColumnWithExpirationCheck(
            $tokenValue,
            $schema->getTokensValueColumn(),
            $expirationInSeconds,
            $schema->getTokensValueCreatedAtColumn()
        );
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function readByRefresh(string $refreshValue, int $expirationInSeconds): ?TokenInterface
    {
        $schema = $this->getDatabaseSchema();
        return $this->readEnabledTokenByColumnWithExpirationCheck(
            $refreshValue,
            $schema->getTokensRefreshColumn(),
            $expirationInSeconds,
            $schema->getTokensRefreshCreatedAtColumn()
        );
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function readByUser(int $userId, int $expirationInSeconds, int $limit = null): array
    {
        $schema = $this->getDatabaseSchema();
        /** @var TokenInterface[] $tokens */
        return $this->readEnabledTokensByColumnWithExpirationCheck(
            (string)$userId,
            $schema->getTokensUserIdentityColumn(),
            $expirationInSeconds,
            $schema->getTokensValueCreatedAtColumn(),
            ['*'],
            $limit
        );
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function readScopeIdentifiers(int $identity): array
    {
        try {
            $schema = $this->getDatabaseSchema();
            $scopeIdentities = $this->readBelongsToManyRelationshipIdentities(
                $identity,
                $schema->getTokensScopesTable(),
                $schema->getTokensScopesTokenIdentityColumn(),
                $schema->getTokensScopesScopeIdentityColumn()
            );

            return iterator_to_array(
                $this->queryScopeIdentifiers(
                    array_map('intval', $scopeIdentities)
                )
            );
        } catch (RepositoryException $exception) {
            $message = 'Reading scopes for a token failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function readScopeColumns(int $identity): array
    {
        try {
            $schema = $this->getDatabaseSchema();
            $priTableAlias = 's';
            $intTableAlias = 'ts';
            $columns = ["$priTableAlias.{$schema->getScopesIdentifierColumn()}"];
            return $this->readBelongsToManyRelationshipColumns(
                $identity,
                $priTableAlias,
                $intTableAlias,
                $schema->getScopesTable(),
                $schema->getTokensScopesTable(),
                $schema->getScopesIdentityColumn(),
                $schema->getTokensScopesTokenIdentityColumn(),
                $schema->getTokensScopesScopeIdentityColumn(),
                $columns
            );
        } catch (RepositoryException $exception) {
            $message = 'Reading scopes for a token failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function updateValues(TokenInterface $token): void
    {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $schema = $this->getDatabaseSchema();
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $dbNow = $this->createTypedParameter($query, $now);
            $query
                ->update($this->getTableNameForWriting())
                ->where($this->getPrimaryKeyName() . '=' . $this->createTypedParameter($query, $token->getIdentity()))
                ->set($schema->getTokensValueColumn(), $this->createTypedParameter($query, $token->getValue()))
                ->set($schema->getTokensValueCreatedAtColumn(), $dbNow);
            if ($token->getRefreshValue() !== null) {
                $query
                    ->set(
                        $schema->getTokensRefreshColumn(),
                        $this->createTypedParameter($query, $token->getRefreshValue())
                    )->set($schema->getTokensRefreshCreatedAtColumn(), $dbNow);
            }

            $numberOfUpdated = $query->execute();
            assert(is_int($numberOfUpdated) === true);
            if ($numberOfUpdated > 0) {
                $token->setValueCreatedAt($now);
                if ($token->getRefreshValue() !== null) {
                    $token->setRefreshCreatedAt($now);
                }

                $token->setUpdatedAt($now);
            }
        } catch (DBALException $exception) {
            $message = 'Token update failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function delete(int $identifier): void
    {
        $this->deleteResource($identifier);
    }

    /**
     * @inheritdoc
     * @param int $identifier
     * @throws DBALException
     */
    public function disable(int $identifier): void
    {
        $query = $this->getConnection()->createQueryBuilder();

        $schema = $this->getDatabaseSchema();
        $query
            ->update($this->getTableNameForWriting())
            ->where($this->getPrimaryKeyName() . '=' . $this->createTypedParameter($query, $identifier))
            ->set($schema->getTokensIsEnabledColumn(), $this->createTypedParameter($query, false));

        $numberOfUpdated = $query->execute();
        assert(is_int($numberOfUpdated) === true);
    }

    /**
     * @inheritdoc
     */
    protected function getTableNameForWriting(): string
    {
        return $this->getDatabaseSchema()->getTokensTable();
    }

    /**
     * @inheritdoc
     */
    protected function getPrimaryKeyName(): string
    {
        return $this->getDatabaseSchema()->getTokensIdentityColumn();
    }

    /**
     * @param string $identifier
     * @param string $column
     * @param int $expirationInSeconds
     * @param string $createdAtColumn
     * @param array $columns
     *
     * @return TokenInterface|null
     *
     * @throws RepositoryException
     */
    protected function readEnabledTokenByColumnWithExpirationCheck(
        string $identifier,
        string $column,
        int $expirationInSeconds,
        string $createdAtColumn,
        array $columns = ['*']
    ): ?TokenInterface {
        try {
            $query = $this->createEnabledTokenByColumnWithExpirationCheckQuery(
                $identifier,
                $column,
                $expirationInSeconds,
                $createdAtColumn,
                $columns
            );

            $statement = $query->execute();
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());
            $result = $statement->fetch();

            return $result === false ? null : $result;
        } catch (DBALException $exception) {
            $message = 'Reading token failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param string $identifier
     * @param string $column
     * @param int $expirationInSeconds
     * @param string $createdAtColumn
     * @param array $columns
     * @param int|null $limit
     * @return array
     * @throws RepositoryException
     */
    protected function readEnabledTokensByColumnWithExpirationCheck(
        string $identifier,
        string $column,
        int $expirationInSeconds,
        string $createdAtColumn,
        array $columns = ['*'],
        int $limit = null
    ): array {
        try {
            $query = $this->createEnabledTokenByColumnWithExpirationCheckQuery(
                $identifier,
                $column,
                $expirationInSeconds,
                $createdAtColumn,
                $columns
            );
            $limit === null ?: $query->setMaxResults($limit);

            $statement = $query->execute();
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());

            $result = [];
            while (($token = $statement->fetch()) !== false) {
                /** @var TokenInterface $token */
                $result[$token->getIdentity()] = $token;
            }

            return $result;
        } catch (DBALException $exception) {
            $message = 'Reading tokens failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param string $identifier
     * @param string $column
     * @param int $expirationInSeconds
     * @param string $createdAtColumn
     * @param array $columns
     * @return QueryBuilder
     */
    protected function createEnabledTokenByColumnWithExpirationCheckQuery(
        string $identifier,
        string $column,
        int $expirationInSeconds,
        string $createdAtColumn,
        array $columns = ['*']
    ): QueryBuilder {
        $query = $this->getConnection()->createQueryBuilder();
        return $this->addExpirationCondition(
            $query->select($columns)
                ->from($this->getTableNameForReading())
                ->where($column . '=' . $this->createTypedParameter($query, $identifier))
                // SQLite and MySQL work fine with just 1 but PostgreSQL wants it to be a string '1'
                ->andWhere($query->expr()->eq($this->getDatabaseSchema()->getTokensIsEnabledColumn(), "'1'")),
            $expirationInSeconds,
            $createdAtColumn
        );
    }

    /**
     * @param QueryBuilder $query
     * @param int $expirationInSeconds
     * @param string $createdAtColumn
     * @return QueryBuilder
     */
    protected function addExpirationCondition(
        QueryBuilder $query,
        int $expirationInSeconds,
        string $createdAtColumn
    ): QueryBuilder {
        $earliestExpired = $this->ignoreException(function () use ($expirationInSeconds): DateTimeImmutable {
            return (new DateTimeImmutable())->sub(new DateInterval("PT{$expirationInSeconds}S"));
        });

        $query->andWhere($createdAtColumn . '>' . $this->createTypedParameter($query, $earliestExpired));

        return $query;
    }

    /**
     * @param int $identity
     * @param iterable $scopeIdentities
     * @return void
     * @throws DBALDriverException
     */
    private function bindScopeIdentities(int $identity, iterable $scopeIdentities): void
    {
        try {
            $schema = $this->getDatabaseSchema();
            $this->createBelongsToManyRelationship(
                $identity,
                $scopeIdentities,
                $schema->getTokensScopesTable(),
                $schema->getTokensScopesTokenIdentityColumn(),
                $schema->getTokensScopesScopeIdentityColumn()
            );
        } catch (RepositoryException $exception) {
            $message = 'Binding token scopes identities failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param TokenInterface $token
     * @param DatabaseSchemaInterface $databaseSchema
     * @return array
     */
    protected function associateClientIdentity(
        TokenInterface $token,
        DatabaseSchemaInterface $databaseSchema
    ): array {
        try {
            assert(($clientIdentifier = $token->getClientIdentifier()) != null);
            if (($clientIdentity = $this->getClientRepository()->queryIdentity($clientIdentifier)) !== null) {
                $token->setClientIdentity($clientIdentity);
                return [$databaseSchema->getRedirectUrisClientIdentityColumn() => $token->getClientIdentity()];
            }

            return [];
        } catch (Throwable $throwable) {
            $message = 'Associate client identity failed.';
            throw new RepositoryException($message, 0, $throwable);
        }
    }

    /**
     * @param TokenInterface $token
     * @return void
     */
    protected function associateClientIdentifier(TokenInterface $token): void
    {
        try {
            assert(($clientIdentity = $token->getClientIdentity()) !== 0);
            if (($clientIdentifier = $this->getClientRepository()->queryIdentifier(
                    $clientIdentity
                )) !== null) {
                $token->setClientIdentifier($clientIdentifier);
            }
        } catch (Throwable $throwable) {
            $message = 'Associate client identifier failed.';
            throw new RepositoryException($message, 0, $throwable);
        }
    }
}
