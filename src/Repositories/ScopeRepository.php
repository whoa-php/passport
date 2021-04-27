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
     *
     * @throws RepositoryException
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
     *
     * @throws RepositoryException
     */
    public function create(ScopeInterface $scope): ScopeInterface
    {
        try {
            $now    = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $this->createResource([
                $schema->getScopesIdentityColumn()    => $scope->getIdentifier(),
                $schema->getScopesUuidColumn()        => $scope->getUuid(),
                $schema->getScopesDescriptionColumn() => $scope->getDescription(),
                $schema->getScopesCreatedAtColumn()   => $now,
            ]);

            $scope->setUuid()->setCreatedAt($now);

            return $scope;
        } catch (RepositoryException $exception) {
            $message = 'Scope creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function read(string $identifier): ScopeInterface
    {
        try {
            return $this->readResource($identifier);
        } catch (RepositoryException $exception) {
            $message = 'Scope reading failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function update(ScopeInterface $scope): void
    {
        try {
            $now    = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $this->updateResource($scope->getIdentifier(), [
                $schema->getScopesDescriptionColumn() => $scope->getDescription(),
                $schema->getScopesUpdatedAtColumn()   => $now,
            ]);
            $scope->setUpdatedAt($now);
        } catch (RepositoryException $exception) {
            $message = 'Scope update failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function delete(string $identifier): void
    {
        try {
            $this->deleteResource($identifier);
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
}
