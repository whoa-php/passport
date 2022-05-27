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

use DateTimeImmutable;
use Whoa\Passport\Contracts\Entities\ScopeInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Exceptions\RepositoryException;

/**
 * @package Whoa\Passport
 */
abstract class ScopeRepository extends BaseRepository implements ScopeRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function index(): array
    {
        try {
            return parent::indexResources();
        } catch (RepositoryException $exception) {
            $message = 'Reading scopes failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function create(ScopeInterface $scope): ScopeInterface
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $values = [
                $schema->getScopesIdentifierColumn() => $scope->getIdentifier(),
                $schema->getScopesUuidColumn() => $scope->getUuid(),
                $schema->getScopesNameColumn() => $scope->getName(),
                $schema->getScopesDescriptionColumn() => $scope->getDescription(),
                $schema->getScopesCreatedAtColumn() => $now,
            ];
            $this->createResource($values);
            $identity = $this->getLastInsertId();

            $scope->setIdentity($identity)->setUuid()->setCreatedAt($now);

            return $scope;
        } catch (RepositoryException $exception) {
            $message = 'Scope creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function read($index): ?ScopeInterface
    {
        try {
            assert($index instanceof ScopeInterface || is_string($index) === true || is_int($index) === true);
            if ($index instanceof ScopeInterface) {
                return $index;
            } elseif (is_int($index) === true) {
                return $this->readResource($index);
            } elseif (is_string($index) === true) {
                return $this->readResource($index, $this->getIdentifierKeyName());
            }

            return null;
        } catch (RepositoryException $exception) {
            $message = 'Scope reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function update(ScopeInterface $scope): void
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $this->updateResource($scope->getIdentity(), [
                $schema->getScopesIdentifierColumn() => $scope->getIdentifier(),
                $schema->getScopesNameColumn() => $scope->getName(),
                $schema->getScopesDescriptionColumn() => $scope->getDescription(),
                $schema->getScopesUpdatedAtColumn() => $now,
            ]);
            $scope->setUpdatedAt($now);
        } catch (RepositoryException $exception) {
            $message = 'Scope update failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($index)
    {
        try {
            $this->deleteResource($index);
        } catch (RepositoryException $exception) {
            $message = 'Scope deletion failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTableNameForWriting(): string
    {
        return $this->getDatabaseSchema()->getScopesTable();
    }

    /**
     * @inheritdoc
     */
    protected function getPrimaryKeyName(): string
    {
        return $this->getDatabaseSchema()->getScopesIdentityColumn();
    }

    /**
     * @inheritDoc
     */
    public function getIdentifierKeyName(): string
    {
        return $this->getDatabaseSchema()->getScopesIdentifierColumn();
    }

    /**
     * @inheritDoc
     */
    public function queryIdentity($index): ?int
    {
        if (($scope = $this->read($index)) !== null) {
            return $scope->getIdentity();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function queryIdentifier($index): ?string
    {
        if (($scope = $this->read($index)) !== null) {
            return $scope->getIdentifier();
        }
        return null;
    }
}
