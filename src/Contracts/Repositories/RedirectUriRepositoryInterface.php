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
use Whoa\Passport\Contracts\Entities\RedirectUriInterface;

/**
 * @package Whoa\Passport
 */
interface RedirectUriRepositoryInterface
{
    /**
     * @param Closure $closure
     *
     * @return void
     */
    public function inTransaction(Closure $closure): void;

    /**
     * @param string $clientIdentifier
     *
     * @return RedirectUriInterface[]
     */
    public function indexClientUris(string $clientIdentifier): array;

    /**
     * @param RedirectUriInterface $redirectUri
     *
     * @return RedirectUriInterface
     */
    public function create(RedirectUriInterface $redirectUri): RedirectUriInterface;

    /**
     * @param int $identity
     *
     * @return RedirectUriInterface|null
     */
    public function read(int $identity): ?RedirectUriInterface;

    /**
     * @param RedirectUriInterface $redirectUri
     *
     * @return void
     */
    public function update(RedirectUriInterface $redirectUri): void;

    /**
     * @param int $identifier
     *
     * @return void
     */
    public function delete(int $identifier): void;
}
