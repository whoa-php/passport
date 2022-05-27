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
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Whoa\Passport\Contracts\Entities\ClientInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Exceptions\RepositoryException;
use Whoa\Passport\Traits\Repository\ScopeRepositoryTrait;

use function assert;

/**
 * @package Whoa\Passport
 */
abstract class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    use ScopeRepositoryTrait;

    /**
     * Constructor
     */
    public function __construct(ScopeRepositoryInterface $scopeRepo)
    {
        $this->setScopeRepository($scopeRepo);
    }

    /**
     * @inheritdoc
     */
    public function index(): array
    {
        return parent::indexResources();
    }

    /**
     * @inheritdoc
     * @param ClientInterface $client
     * @return ClientInterface
     * @throws DBALDriverException
     */
    public function create(ClientInterface $client): ClientInterface
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $values = [
                $schema->getClientsIdentifierColumn() => $client->getIdentifier(),
                $schema->getClientsUuidColumn() => $client->getUuid(),
                $schema->getClientsNameColumn() => $client->getName(),
                $schema->getClientsDescriptionColumn() => $client->getDescription(),
                $schema->getClientsCredentialsColumn() => $client->getCredentials(),
                $schema->getClientsIsConfidentialColumn() => $client->isConfidential(),
                $schema->getClientsIsScopeExcessAllowedColumn() => $client->isScopeExcessAllowed(),
                $schema->getClientsIsUseDefaultScopeColumn() => $client->isUseDefaultScopesOnEmptyRequest(),
                $schema->getClientsIsCodeGrantEnabledColumn() => $client->isCodeGrantEnabled(),
                $schema->getClientsIsImplicitGrantEnabledColumn() => $client->isImplicitGrantEnabled(),
                $schema->getClientsIsPasswordGrantEnabledColumn() => $client->isPasswordGrantEnabled(),
                $schema->getClientsIsClientGrantEnabledColumn() => $client->isClientGrantEnabled(),
                $schema->getClientsIsRefreshGrantEnabledColumn() => $client->isRefreshGrantEnabled(),
                $schema->getClientsCreatedAtColumn() => $now,
            ];

            if (empty($scopeIdentifiers = $client->getScopeIdentifiers()) === true) {
                $this->createResource($values);
                $client->setIdentity($this->getLastInsertId());
            } else {
                $this->inTransaction(function () use ($client, $values, $scopeIdentifiers) {
                    $this->createResource($values);
                    $client->setIdentity($identity = $this->getLastInsertId());
                    $this->bindScopeIdentifiers($identity, $scopeIdentifiers);
                });
            }
            $client->setUuid()->setCreatedAt($now);

            return $client;
        } catch (RepositoryException $exception) {
            $message = 'Client creation failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritDoc
     * @throws DBALDriverException
     */
    public function bindScopes($client, iterable $scopes): void
    {
        assert(($identity = $this->queryIdentity($client)) !== null);
        $this->bindScopeIdentities($identity, $this->queryScopeIdentities($scopes));
    }

    /**
     * @inheritDoc
     * @throws DBALDriverException
     */
    public function bindScopeIdentifiers($client, iterable $scopeIdentifiers): void
    {
        assert(($identity = $this->queryIdentity($client)) !== null);
        $this->bindScopeIdentities($identity, $this->queryScopeIdentities($scopeIdentifiers));
    }

    /**
     * @inheritDoc
     */
    public function unbindScopes($client): void
    {
        try {
            assert(($identity = $this->queryIdentity($client)) != null);
            $schema = $this->getDatabaseSchema();
            $this->deleteBelongsToManyRelationshipIdentities(
                $schema->getClientsScopesTable(),
                $schema->getClientsScopesClientIdentityColumn(),
                $identity
            );
        } catch (RepositoryException $exception) {
            $message = 'Unbinding client scopes failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function read($index): ?ClientInterface
    {
        try {
            assert($index instanceof ClientInterface || is_string($index) === true || is_int($index) === true);
            if ($index instanceof ClientInterface) {
                return $index;
            } elseif (is_int($index) === true) {
                return $this->readResource($index);
            } elseif (is_string($index) === true) {
                return $this->readResource($index, $this->getIdentifierKeyName());
            }

            return null;
        } catch (RepositoryException $exception) {
            $message = 'Reading client failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function readScopeIdentifiers($client): array
    {
        try {
            assert($client !== null);
            $schema = $this->getDatabaseSchema();
            $scopeIdentities = $this->readBelongsToManyRelationshipIdentifiers(
                $client instanceof ClientInterface ? $client->getIdentity() : $this->queryIdentity($client),
                $schema->getClientsScopesTable(),
                $schema->getClientsScopesClientIdentityColumn(),
                $schema->getClientsScopesScopeIdentityColumn()
            );

            return iterator_to_array(
                $this->queryScopeIdentifiers(
                    array_map('intval', $scopeIdentities)
                )
            );
        } catch (RepositoryException $exception) {
            $message = 'Reading client scope identifiers failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function readRedirectUriStrings(string $identifier): array
    {
        try {
            $schema = $this->getDatabaseSchema();
            return $this->readHasManyRelationshipColumn(
                $identifier,
                $schema->getRedirectUrisTable(),
                $schema->getRedirectUrisValueColumn(),
                $schema->getRedirectUrisClientIdentityColumn()
            );
        } catch (RepositoryException $exception) {
            $message = 'Reading client redirect URIs failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function update(ClientInterface $client): void
    {
        try {
            $now = $this->ignoreException(function (): DateTimeImmutable {
                return new DateTimeImmutable();
            });
            $schema = $this->getDatabaseSchema();
            $this->updateResource($client->getIdentity(), [
                $schema->getClientsNameColumn() => $client->getName(),
                $schema->getClientsDescriptionColumn() => $client->getDescription(),
                $schema->getClientsCredentialsColumn() => $client->getCredentials(),
                $schema->getClientsIsConfidentialColumn() => $client->isConfidential(),
                $schema->getClientsIsScopeExcessAllowedColumn() => $client->isScopeExcessAllowed(),
                $schema->getClientsIsUseDefaultScopeColumn() => $client->isUseDefaultScopesOnEmptyRequest(),
                $schema->getClientsIsCodeGrantEnabledColumn() => $client->isCodeGrantEnabled(),
                $schema->getClientsIsImplicitGrantEnabledColumn() => $client->isImplicitGrantEnabled(),
                $schema->getClientsIsPasswordGrantEnabledColumn() => $client->isPasswordGrantEnabled(),
                $schema->getClientsIsClientGrantEnabledColumn() => $client->isClientGrantEnabled(),
                $schema->getClientsUpdatedAtColumn() => $now,
            ]);
            $client->setUpdatedAt($now);
        } catch (RepositoryException $exception) {
            $message = 'Client update failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(string $index): void
    {
        try {
            $this->deleteResource($index);
        } catch (RepositoryException $exception) {
            $message = 'Client deletion failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTableNameForWriting(): string
    {
        return $this->getDatabaseSchema()->getClientsTable();
    }

    /**
     * @inheritdoc
     */
    protected function getPrimaryKeyName(): string
    {
        return $this->getDatabaseSchema()->getClientsIdentityColumn();
    }

    /**
     * @return string
     */
    protected function getIdentifierKeyName(): string
    {
        return $this->getDatabaseSchema()->getClientsIdentifierColumn();
    }

    /**
     * @param int $identity
     * @param iterable $scopeIdentities
     * @return void
     * @throws DBALDriverException
     */
    private function bindScopeIdentities(int $identity, iterable $scopeIdentities)
    {
        try {
            $schema = $this->getDatabaseSchema();
            $this->createBelongsToManyRelationship(
                $identity,
                $scopeIdentities,
                $schema->getClientsScopesTable(),
                $schema->getClientsScopesClientIdentityColumn(),
                $schema->getClientsScopesScopeIdentityColumn()
            );
        } catch (RepositoryException $exception) {
            $message = 'Binding client scope identities failed.';
            throw new RepositoryException($message, 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function queryIdentity($index): ?int
    {
        if (($client = $this->read($index)) !== null) {
            return $client->getIdentity();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function queryIdentifier($index): ?string
    {
        if (($client = $this->read($index)) !== null) {
            return $client->getIdentifier();
        }
        return null;
    }
}
