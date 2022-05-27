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

namespace Whoa\Passport\Contracts\Repositories;

use Closure;
use Whoa\Passport\Contracts\Entities\ScopeInterface;

/**
 * @package Whoa\Passport
 */
interface ScopeRepositoryInterface
{
    /**
     * @param Closure $closure
     * @return void
     */
    public function inTransaction(Closure $closure): void;

    /**
     * @return ScopeInterface[]
     */
    public function index(): array;

    /**
     * @param ScopeInterface $scope
     * @return ScopeInterface
     */
    public function create(ScopeInterface $scope): ScopeInterface;

    /**
     * @param ScopeInterface|string|int $index
     * @return ScopeInterface|null
     */
    public function read($index): ?ScopeInterface;

    /**
     * @param ScopeInterface $scope
     * @return void
     */
    public function update(ScopeInterface $scope): void;

    /**
     * @param string|int $index
     * @return void
     */
    public function delete($index);

    /**
     * @return string
     */
    public function getIdentifierKeyName(): string;

    /**
     * @param ScopeInterface|string|int $index
     * @return int|null
     */
    public function queryIdentity($index): ?int;

    /**
     * @param ScopeInterface|string|int $index
     * @return string|null
     */
    public function queryIdentifier($index): ?string;
}
