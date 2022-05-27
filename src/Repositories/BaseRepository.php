<?php

/**
 * Copyright 2015-2019 info@neomerx.com
 * Copyright 2021 info@whoaphp.com
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

use Closure;
use DateTimeInterface;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\ConnectionException as DBALConnectionException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Exception;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use PDO;

use function assert;
use function call_user_func;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_string;

/**
 * @package Whoa\Passport
 */
abstract class BaseRepository
{
    /**
     * @return string
     */
    abstract protected function getTableNameForReading(): string;

    /**
     * @return string
     */
    abstract protected function getTableNameForWriting(): string;

    /**
     * @return string
     */
    abstract protected function getClassName(): string;

    /**
     * @return string
     */
    abstract protected function getPrimaryKeyName(): string;

    /**
     * @var DBALConnection
     */
    private DBALConnection $connection;

    /**
     * @var DatabaseSchemaInterface
     */
    private DatabaseSchemaInterface $databaseSchema;

    /**
     * @param Closure $closure
     * @return void
     * @throws RepositoryException
     */
    public function inTransaction(Closure $closure): void
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $isOk = ($closure() === false ? null : true);
        } finally {
            $isCommitting = (isset($isOk) === true);
            try {
                $isCommitting === true ? $connection->commit() : $connection->rollBack();
            } catch (DBALConnectionException|DBALException $exception) {
                throw new RepositoryException(
                    $isCommitting === true ? 'Failed to commit a transaction.' : 'Failed to rollback a transaction.',
                    0,
                    $exception
                );
            }
        }
    }

    /**
     * @return DBALConnection
     */
    protected function getConnection(): DBALConnection
    {
        return $this->connection;
    }

    /**
     * @param DBALConnection $connection
     * @return self
     */
    protected function setConnection(DBALConnection $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @param array $columns
     * @return array
     * @throws RepositoryException
     */
    protected function indexResources(array $columns = ['*']): array
    {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $statement = $query
                ->select($columns)
                ->from($this->getTableNameForReading())
                ->execute();

            $statement->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());
            return $statement->fetchAll();
        } catch (DBALException $exception) {
            $message = 'Resource reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param iterable $values
     * @return void
     * @throws RepositoryException
     */
    protected function createResource(iterable $values): void
    {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $query->insert($this->getTableNameForWriting());
            foreach ($values as $key => $value) {
                $query->setValue($key, $this->createTypedParameter($query, $value));
            }

            $numberOfAdded = $query->execute();
            assert(is_int($numberOfAdded) === true);
        } catch (DBALException $exception) {
            $message = 'Resource creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @return int
     */
    protected function getLastInsertId(): int
    {
        $lastInsertId = $this->getConnection()->lastInsertId();

        assert(is_numeric($lastInsertId));

        return (int)$lastInsertId;
    }

    /**
     * @param string|int $index
     * @param string|null $column
     * @param string|null $table
     * @param array $columns
     * @return mixed
     * @throws RepositoryException
     */
    protected function readResource(
        $index,
        ?string $column = null,
        ?string $table = null,
        array $columns = ['*']
    ) {
        return $this->readResourceByColumn(
            $index,
            $column === null ? $this->getPrimaryKeyName() : $column,
            $table === null ? $this->getTableNameForReading() : $table,
            $columns
        );
    }

    /**
     * @param string|int $index
     * @param string $column
     * @param string $table
     * @param array $columns
     * @return mixed
     * @throws RepositoryException
     */
    protected function readResourceByColumn(
        $index,
        string $column,
        string $table,
        array $columns
    ) {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $statement = $query
                ->select($columns)
                ->from($table)
                ->where($column . '=' . $this->createTypedParameter($query, $index))
                ->execute();

            $statement->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());
            $result = $statement->fetch();

            return $result === false ? null : $result;
        } catch (DBALException $exception) {
            $message = 'Resource reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param string|int $index
     * @param array $values
     * @param string|null $column
     * @return int
     * @throws RepositoryException
     */
    protected function updateResource($index, array $values, ?string $column = null): int
    {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $query
                ->update($this->getTableNameForWriting())
                ->where(
                    $column === null ? $this->getPrimaryKeyName() : $column . '=' . $this->createTypedParameter(
                            $query,
                            $index
                        )
                );
            foreach ($values as $key => $value) {
                $query->set($key, $this->createTypedParameter($query, $value));
            }

            $numberOfUpdated = $query->execute();
            assert(is_int($numberOfUpdated) === true);

            return $numberOfUpdated;
        } catch (DBALException $exception) {
            $message = 'Resource update failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param $index
     * @param string|null $column
     * @return int
     */
    protected function deleteResource($index, ?string $column = null): int
    {
        try {
            $query = $this->getConnection()->createQueryBuilder();

            $query
                ->delete($this->getTableNameForWriting())
                ->where(
                    $column === null ? $this->getPrimaryKeyName() : $column . '=' . $this->createTypedParameter(
                            $query,
                            $index
                        )
                );

            $numberOfDeleted = $query->execute();
            assert(is_int($numberOfDeleted) === true);

            return $numberOfDeleted;
        } catch (DBALException $exception) {
            $message = 'Resource deletion failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param string|int $primaryKey
     * @param iterable $foreignKeys
     * @param string $intTableName
     * @param string $intPrimaryKeyName
     * @param string $intForeignKeyName
     * @return void
     * @throws DBALDriverException
     */
    protected function createBelongsToManyRelationship(
        $primaryKey,
        iterable $foreignKeys,
        string $intTableName,
        string $intPrimaryKeyName,
        string $intForeignKeyName
    ): void {
        assert(is_string($primaryKey) === true || is_int($primaryKey) === true);

        try {
            $this->inTransaction(function () use (
                $intTableName,
                $intPrimaryKeyName,
                $intForeignKeyName,
                $primaryKey,
                $foreignKeys
            ): void {
                $connection = $this->getConnection();
                $query = $connection->createQueryBuilder();

                $query->insert($intTableName)->values([$intPrimaryKeyName => '?', $intForeignKeyName => '?']);
                $statement = $connection->prepare($query->getSQL());

                foreach ($foreignKeys as $value) {
                    assert(is_string($value) === true || is_int($value) === true);
                    $statement->bindValue(1, $primaryKey);
                    $statement->bindValue(2, $value);
                    $statement->execute();
                }
            });
        } catch (DBALException $exception) {
            $message = 'Belongs-to-Many relationship creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param string|int $identifier
     * @param string $intTableName
     * @param string $intPrimaryKeyName
     * @param string $intForeignKeyName
     * @return string[]
     * @throws RepositoryException
     */
    protected function readBelongsToManyRelationshipIdentifiers(
        $identifier,
        string $intTableName,
        string $intPrimaryKeyName,
        string $intForeignKeyName
    ): array {
        try {
            $connection = $this->getConnection();
            $query = $connection->createQueryBuilder();

            $query
                ->select($intForeignKeyName)
                ->from($intTableName)
                ->where($intPrimaryKeyName . '=' . $this->createTypedParameter($query, $identifier));

            $statement = $query->execute();
            $statement->setFetchMode(PDO::FETCH_NUM);
            return array_column($statement->fetchAll(), 0);
        } catch (DBALException $exception) {
            $message = 'Belongs-to-Many relationship reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param $identifier
     * @param string $priTableAlias
     * @param string $intTableAlias
     * @param string $priTableName
     * @param string $intTableName
     * @param string $priTablePrimaryKeyName
     * @param string $intTablePrimaryKeyName
     * @param string $intTableForeignKeyName
     * @param array $columns
     * @return array
     */
    protected function readBelongsToManyRelationshipColumns(
        $identifier,
        string $priTableAlias,
        string $intTableAlias,
        string $priTableName,
        string $intTableName,
        string $priTablePrimaryKeyName,
        string $intTablePrimaryKeyName,
        string $intTableForeignKeyName,
        array $columns = ['*']
    ): array {
        try {
            $connection = $this->getConnection();
            $query = $connection->createQueryBuilder();

            $query
                ->select($columns)
                ->from($priTableName, $priTableAlias)
                ->leftJoin(
                    $priTableAlias,
                    $intTableName,
                    $intTableAlias,
                    "$priTableAlias.$priTablePrimaryKeyName = $intTableAlias.$intTableForeignKeyName"
                )
                ->where(
                    "$intTableAlias.$intTablePrimaryKeyName = {$this->createTypedParameter($query, $identifier)}"
                );

            $statement = $query->execute();
            $statement->setFetchMode(PDO::FETCH_NUM);
            return array_column($statement->fetchAll(), 0);
        } catch
        (DBALException $exception) {
            $message = 'Belongs-to-Many relationship reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param string $intTableName
     * @param string $intPrimaryKeyName
     * @param int $identity
     * @return int
     * @throws RepositoryException
     */
    protected function deleteBelongsToManyRelationshipIdentities(
        string $intTableName,
        string $intPrimaryKeyName,
        int $identity
    ): int {
        try {
            $connection = $this->getConnection();
            $query = $connection->createQueryBuilder();

            $query
                ->delete($intTableName)
                ->where($intPrimaryKeyName . '=' . $this->createTypedParameter($query, $identity));

            $numberOfDeleted = $query->execute();
            assert(is_int($numberOfDeleted) === true);

            return $numberOfDeleted;
        } catch (DBALException $exception) {
            $message = 'Belongs-to-Many relationship deletion failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param string|int $identifier
     * @param string $hasManyTableName
     * @param string $hasManyColumn
     * @param string $hasManyFkName
     * @return string[]
     * @throws RepositoryException
     */
    protected function readHasManyRelationshipColumn(
        $identifier,
        string $hasManyTableName,
        string $hasManyColumn,
        string $hasManyFkName
    ): array {
        try {
            $connection = $this->getConnection();
            $query = $connection->createQueryBuilder();

            $query
                ->select($hasManyColumn)
                ->from($hasManyTableName)
                ->where($hasManyFkName . '=' . $this->createTypedParameter($query, $identifier));

            $statement = $query->execute();
            $statement->setFetchMode(PDO::FETCH_NUM);
            return array_column($statement->fetchAll(), 0);
        } catch (DBALException $exception) {
            $message = 'Has-Many relationship reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @param DateTimeInterface $dateTime
     * @return string
     * @throws RepositoryException
     */
    protected function getDateTimeForDb(DateTimeInterface $dateTime): string
    {
        try {
            return Type::getType(Types::DATETIME_IMMUTABLE)
                ->convertToDatabaseValue($dateTime, $this->getConnection()->getDatabasePlatform());
        } catch (DBALException $exception) {
            $message = 'DateTime conversion to database format failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @return DatabaseSchemaInterface
     */
    protected function getDatabaseSchema(): DatabaseSchemaInterface
    {
        return $this->databaseSchema;
    }

    /**
     * @param DatabaseSchemaInterface $databaseSchema
     * @return self
     */
    protected function setDatabaseSchema(DatabaseSchemaInterface $databaseSchema): self
    {
        $this->databaseSchema = $databaseSchema;

        return $this;
    }

    /**
     * @param QueryBuilder $query
     * @param mixed $value
     * @return string
     * @throws RepositoryException
     */
    protected function createTypedParameter(QueryBuilder $query, $value): string
    {
        if (is_bool($value) === true) {
            $type = PDO::PARAM_BOOL;
        } elseif (is_int($value) === true) {
            $type = PDO::PARAM_INT;
        } elseif ($value === null) {
            $type = PDO::PARAM_NULL;
        } elseif ($value instanceof DateTimeInterface) {
            $value = $this->getDateTimeForDb($value);
            $type = PDO::PARAM_STR;
        } else {
            $type = PDO::PARAM_STR;
        }

        return $query->createNamedParameter($value, $type);
    }

    /**
     * Helps to ignore exception handling for cases when they do not arise (e.g. having current date and time).
     * @param Closure $closure
     * @param mixed $defaultValue
     * @return mixed|null
     */
    protected function ignoreException(Closure $closure, $defaultValue = null)
    {
        try {
            $defaultValue = call_user_func($closure);
        } catch (Exception $exception) {
        }

        return $defaultValue;
    }
}
