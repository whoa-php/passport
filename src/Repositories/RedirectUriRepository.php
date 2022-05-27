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
use Doctrine\DBAL\Exception as DBALException;
use Throwable;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\Entities\RedirectUriInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\RedirectUriRepositoryInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use PDO;
use Whoa\Passport\Traits\Repository\ClientRepositoryTrait;

/**
 * @package Whoa\Passport
 */
abstract class RedirectUriRepository extends BaseRepository implements RedirectUriRepositoryInterface
{
    use ClientRepositoryTrait;

    /**
     * Constructor
     */
    public function __construct(ClientRepositoryInterface $clientRepo)
    {
        $this->setClientRepository($clientRepo);
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function indexClientUris(string $clientIdentifier): array
    {
        try {
            if (($clientIdValue = $this->getClientRepository()->queryIdentity($clientIdentifier)) !== null) {
                $query = $this->getConnection()->createQueryBuilder();

                $clientIdColumn = $this->getDatabaseSchema()->getRedirectUrisClientIdentityColumn();
                $statement = $query
                    ->select(['*'])
                    ->from($this->getTableNameForWriting())
                    ->where($clientIdColumn . '=' . $this->createTypedParameter($query, (int)$clientIdValue))
                    ->execute();


                $statement->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());
                return $statement->fetchAll();
            }

            return [];
        } catch (DBALException $exception) {
            $message = 'Reading client redirect URIs failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function create(RedirectUriInterface $redirectUri): RedirectUriInterface
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $values = [
                $schema->getRedirectUrisUuidColumn() => $redirectUri->getUuid(),
                $schema->getRedirectUrisValueColumn() => $redirectUri->getValue(),
                $schema->getRedirectUrisCreatedAtColumn() => $now,
            ];

            if (empty($value = $this->associateClientIdentity($redirectUri, $schema)) === false) {
                $values += $value;
            }

            $this->createResource($values);
            $identity = $this->getLastInsertId();

            $redirectUri->setIdentity($identity)->setUuid()->setCreatedAt($now);

            return $redirectUri;
        } catch (RepositoryException $exception) {
            $message = 'Client redirect URI creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @throws RepositoryException
     */
    public function read(int $identity): ?RedirectUriInterface
    {
        try {
            return $this->readResource($identity);
        } catch (RepositoryException $exception) {
            $message = 'Reading client redirect URIs failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function update(RedirectUriInterface $redirectUri): void
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $values = [
                $schema->getRedirectUrisValueColumn() => $redirectUri->getValue(),
                $schema->getRedirectUrisUpdatedAtColumn() => $now,
            ];

            if (empty($value = $this->associateClientIdentity($redirectUri, $schema)) === false) {
                $values += $value;
            }

            $this->updateResource($redirectUri->getIdentity(), $values);

            $redirectUri->setUpdatedAt($now);
        } catch (RepositoryException $exception) {
            $message = 'Client redirect URI update failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function delete(int $identifier): void
    {
        try {
            $this->deleteResource($identifier);
        } catch (RepositoryException $exception) {
            $message = 'Client redirect URI deletion failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTableNameForWriting(): string
    {
        return $this->getDatabaseSchema()->getRedirectUrisTable();
    }

    /**
     * @inheritdoc
     */
    protected function getPrimaryKeyName(): string
    {
        return $this->getDatabaseSchema()->getRedirectUrisIdentityColumn();
    }

    /**
     * @param RedirectUriInterface $redirectUri
     * @param DatabaseSchemaInterface $databaseSchema
     * @return array
     */
    protected function associateClientIdentity(
        RedirectUriInterface $redirectUri,
        DatabaseSchemaInterface $databaseSchema
    ): array {
        try {
            assert(($clientIdentifier = $redirectUri->getClientIdentifier()) != null);
            if (($clientIdentity = $this->getClientRepository()->queryIdentity($clientIdentifier)) !== null) {
                $redirectUri->setClientIdentity($clientIdentity);
                return [$databaseSchema->getRedirectUrisClientIdentityColumn() => $redirectUri->getClientIdentity()];
            }

            return [];
        } catch (Throwable $throwable) {
            $message = 'Associate client identity failed.';
            throw new RepositoryException($message, 0, $throwable);
        }
    }

    /**
     * @param RedirectUriInterface $redirectUri
     * @return void
     */
    protected function associateClientIdentifier(RedirectUriInterface $redirectUri): void
    {
        try {
            assert(($clientIdentity = $redirectUri->getClientIdentity()) !== 0);
            if (($clientIdentifier = $this->getClientRepository()->queryIdentifier(
                    $clientIdentity
                )) !== null) {
                $redirectUri->setClientIdentifier($clientIdentifier);
            }
        } catch (Throwable $throwable) {
            $message = 'Associate client identifier failed.';
            throw new RepositoryException($message, 0, $throwable);
        }
    }
}
