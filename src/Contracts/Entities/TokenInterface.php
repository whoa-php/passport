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
use Whoa\OAuthServer\Contracts\AuthorizationCodeInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @package Whoa\Passport
 */
interface TokenInterface extends AuthorizationCodeInterface, \Whoa\OAuthServer\Contracts\TokenInterface
{
    /**
     * @return int
     */
    public function getIdentity(): int;

    /**
     * @param int $identity
     * @return TokenInterface
     */
    public function setIdentity(int $identity): TokenInterface;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface;

    /**
     * @param UuidInterface|string|null $uuid
     * @return TokenInterface
     */
    public function setUuid($uuid = null): TokenInterface;

    /**
     * @return int
     */
    public function getClientIdentity(): int;

    /**
     * @param int $clientIdentity
     * @return TokenInterface
     */
    public function setClientIdentity(int $clientIdentity): TokenInterface;

    /**
     * @param string $identifier
     * @return TokenInterface
     */
    public function setClientIdentifier(string $identifier): TokenInterface;

    /**
     * @param int|string $identifier
     * @return TokenInterface
     */
    public function setUserIdentifier($identifier): TokenInterface;

    /**
     * @param string[] $identifiers
     * @return TokenInterface
     */
    public function setScopeIdentifiers(array $identifiers): TokenInterface;

    /**
     * @return string
     */
    public function getScopeList(): string;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string|null $uri
     * @return TokenInterface
     */
    public function setRedirectUriString(?string $uri): TokenInterface;

    /**
     * @return TokenInterface
     */
    public function setScopeModified(): TokenInterface;

    /**
     * @return TokenInterface
     */
    public function setScopeUnmodified(): TokenInterface;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return TokenInterface
     */
    public function setEnabled(): TokenInterface;

    /**
     * @return TokenInterface
     */
    public function setDisabled(): TokenInterface;

    /**
     * @param string|null $code
     * @return TokenInterface
     */
    public function setCode(?string $code): TokenInterface;

    /**
     * @param string|null $value
     * @return TokenInterface
     */
    public function setValue(?string $value): TokenInterface;

    /**
     * @param string|null $type
     * @return TokenInterface
     */
    public function setType(?string $type): TokenInterface;

    /**
     * @param string|null $refreshValue
     * @return TokenInterface
     */
    public function setRefreshValue(?string $refreshValue): TokenInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getCodeCreatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface $codeCreatedAt
     * @return TokenInterface
     */
    public function setCodeCreatedAt(DateTimeInterface $codeCreatedAt): TokenInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getValueCreatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface $valueCreatedAt
     * @return TokenInterface
     */
    public function setValueCreatedAt(DateTimeInterface $valueCreatedAt): TokenInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getRefreshCreatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface $refreshCreatedAt
     * @return TokenInterface
     */
    public function setRefreshCreatedAt(DateTimeInterface $refreshCreatedAt): TokenInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface $createdAt
     * @return TokenInterface
     */
    public function setCreatedAt(DateTimeInterface $createdAt): TokenInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface $updatedAt
     * @return TokenInterface
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): TokenInterface;
}
