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
use Whoa\Passport\Contracts\Entities\ClientInterface;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Exceptions\RepositoryException;

/**
 * @package Whoa\Passport
 */
class ClientRepository extends \Whoa\Passport\Repositories\ClientRepository
{
    /**
     * @var string
     */
    private $modelClass;

    /**
     * @param Connection              $connection
     * @param DatabaseSchemaInterface $databaseSchema
     * @param string                  $modelClass
     */
    public function __construct(
        Connection $connection,
        DatabaseSchemaInterface $databaseSchema,
        string $modelClass = Client::class
    )
    {
        $this->setConnection($connection)->setDatabaseSchema($databaseSchema);
        $this->modelClass = $modelClass;
    }

    /**
     * @inheritdoc
     *
     * @throws RepositoryException
     */
    public function index(): array
    {
        /** @var Client[] $clients */
        $clients = parent::index();
        foreach ($clients as $client) {
            $this->addScopeAndRedirectUris($client);
        }

        return $clients;
    }

    /**
     * @inheritdoc
     */
    public function read(string $identifier): ?ClientInterface
    {
        /** @var Client|null $client */
        $client = parent::read($identifier);

        if ($client !== null) {
            $this->addScopeAndRedirectUris($client);
        }

        return $client;
    }

    /**
     * @inheritdoc
     */
    protected function getClassName(): string
    {
        return $this->modelClass;
    }

    /**
     * @param Client $client
     *
     * @return void
     *
     * @throws RepositoryException
     */
    private function addScopeAndRedirectUris(Client $client): void
    {
        $client->setScopeIdentifiers($this->readScopeIdentifiers($client->getIdentifier()));
        $client->setRedirectUriStrings($this->readRedirectUriStrings($client->getIdentifier()));
    }

    /**
     * @inheritdoc
     */
    protected function getTableNameForReading(): string
    {
        return $this->getTableNameForWriting();
    }
}
