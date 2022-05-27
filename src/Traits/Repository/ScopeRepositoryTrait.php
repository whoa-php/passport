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
use Whoa\Passport\Contracts\Entities\ScopeInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;

/**
 * @package Whoa\Passport
 */
trait ScopeRepositoryTrait
{
    /**
     * @var ScopeRepositoryInterface $scopeRepo
     */
    private ScopeRepositoryInterface $scopeRepo;

    /**
     * @param ScopeRepositoryInterface $scopeRepo
     * @return void
     */
    public function setScopeRepository(ScopeRepositoryInterface $scopeRepo)
    {
        $this->scopeRepo = $scopeRepo;
    }

    /**
     * @return ScopeRepositoryInterface
     */
    public function getScopeRepository(): ScopeRepositoryInterface
    {
        return $this->scopeRepo;
    }

    /**
     * @param iterable|ScopeInterface[]|string[]|int[] $scopes
     * @return iterable|ScopeInterface[]
     */
    protected function queryScopeIdentities(iterable $scopes): Traversable
    {
        foreach ($scopes as $scope) {
            if (($identity = $this->getScopeRepository()->queryIdentity($scope)) !== null) {
                yield $identity;
            }
        }
    }

    /**
     * @param iterable|ScopeInterface[]|string[]|int[] $clients
     * @return iterable|ScopeInterface[]
     */
    protected function queryScopeIdentifiers(iterable $clients): Traversable
    {
        foreach ($clients as $scope) {
            if (($identifier = $this->getScopeRepository()->queryIdentifier($scope)) !== null) {
                yield $identifier;
            }
        }
    }
}
