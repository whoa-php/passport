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

namespace Whoa\Passport\Contracts\Entities;

use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @package Whoa\Passport
 */
interface ScopeInterface
{
    /**
     * @return int
     */
    public function getIdentity(): int;

    /**
     * @param int $identity
     * @return ScopeInterface
     */
    public function setIdentity(int $identity): ScopeInterface;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param string $identifier
     * @return ScopeInterface
     */
    public function setIdentifier(string $identifier): ScopeInterface;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface;

    /**
     * @param UuidInterface|string|null $uuid
     * @return ScopeInterface
     */
    public function setUuid($uuid = null): ScopeInterface;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     * @return ScopeInterface
     */
    public function setName(?string $name = null): ScopeInterface;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     * @return ScopeInterface
     */
    public function setDescription(?string $description = null): ScopeInterface;

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface;

    /**
     * @param DateTimeInterface|null $createdAt
     * @return ScopeInterface
     */
    public function setCreatedAt(?DateTimeInterface $createdAt = null): ScopeInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface|null $createdAt
     * @return ScopeInterface
     */
    public function setUpdatedAt(?DateTimeInterface $createdAt = null): ScopeInterface;
}
