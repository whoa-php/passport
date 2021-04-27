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
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @package Whoa\Passport
 */
interface RedirectUriInterface
{
    /**
     * @return int|null
     */
    public function getIdentifier(): ?int;

    /**
     * @param int $identifier
     *
     * @return RedirectUriInterface
     */
    public function setIdentifier(int $identifier): RedirectUriInterface;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface;

    /**
     * @param UuidInterface|string|null $uuid
     *
     * @return RedirectUriInterface
     */
    public function setUuid($uuid = null): RedirectUriInterface;

    /**
     * @return string|null
     */
    public function getClientIdentifier(): ?string;

    /**
     * @param string $identifier
     *
     * @return RedirectUriInterface
     */
    public function setClientIdentifier(string $identifier): RedirectUriInterface;

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface;

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string $uri
     *
     * @return RedirectUriInterface
     */
    public function setValue(string $uri): RedirectUriInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return RedirectUriInterface
     */
    public function setCreatedAt(DateTimeInterface $createdAt): RedirectUriInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return RedirectUriInterface
     */
    public function setUpdatedAt(DateTimeInterface $createdAt): RedirectUriInterface;
}
