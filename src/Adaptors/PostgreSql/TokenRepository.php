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

namespace Whoa\Passport\Adaptors\PostgreSql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use PDO;

/**
 * @package Whoa\Passport
 */
class TokenRepository extends \Whoa\Passport\Repositories\TokenRepository
{
    use ArrayParserTrait;

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
        $this->setConnection($connection)->setDatabaseSchema($databaseSchema);
        $this->modelClass = $modelClass;
    }

    /**
     * @inheritdoc
     *
     * @param string $tokenValue
     * @param int $expirationInSeconds
     * @return array|null
     * @throws DBALException
     */
    public function readPassport(string $tokenValue, int $expirationInSeconds): ?array
    {
        try {
            $schema = $this->getDatabaseSchema();
            $query = $this->getConnection()->createQueryBuilder();
            $query = $this->addExpirationCondition(
                $query->select(['*'])
                    ->from($schema->getPassportView())
                    ->where($schema->getTokensValueColumn() . '=' . $this->createTypedParameter($query, $tokenValue))
                    ->andWhere($query->expr()->eq($this->getDatabaseSchema()->getTokensIsEnabledColumn(), "'1'")),
                $expirationInSeconds,
                $schema->getTokensValueCreatedAtColumn()
            );

            $statement = $query->execute();
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $data = $statement->fetch();

            $result = null;
            if ($data !== false) {
                $scopesColumn = $schema->getTokensViewScopesColumn();
                $scopeList = $data[$scopesColumn];
                $data[$scopesColumn] = $this->parseArray($scopeList);
                $result = $data;
            }

            return $result;
        } catch (DBALException $exception) {
            $message = 'Passport reading failed.';
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
        return $this->getDatabaseSchema()->getTokensView();
    }
}
