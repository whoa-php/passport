<?php

/**
 * Copyright 2021-2022 info@whoaphp.com
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

namespace Whoa\Passport\Traits\Repository;

use Traversable;
use Whoa\Passport\Contracts\Entities\ClientInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;

/**
 * @package Whoa\Passport
 */
trait ClientRepositoryTrait
{
    /**
     * @var ClientRepositoryInterface $clientRepo
     */
    private ClientRepositoryInterface $clientRepo;

    /**
     * @param ClientRepositoryInterface $clientRepo
     * @return void
     */
    public function setClientRepository(ClientRepositoryInterface $clientRepo)
    {
        $this->clientRepo = $clientRepo;
    }

    /**
     * @return ClientRepositoryInterface
     */
    public function getClientRepository(): ClientRepositoryInterface
    {
        return $this->clientRepo;
    }

    /**
     * @param iterable|ClientInterface[]|string[]|int[] $clients
     * @return iterable|ClientInterface[]
     */
    protected function queryClientIdentities(iterable $clients): Traversable
    {
        foreach ($clients as $client) {
            if (($identity = $this->getClientRepository()->queryIdentity($client)) !== null) {
                yield $identity;
            }
        }
    }

    /**
     * @param iterable|ClientInterface[]|string[]|int[] $clients
     * @return iterable|ClientInterface[]
     */
    protected function queryClientIdentifiers(
        iterable $clients
    ): Traversable {
        foreach ($clients as $client) {
            if (($identity = $this->getClientRepository()->queryIdentifier($client)) !== null) {
                yield $identity;
            }
        }
    }
}
