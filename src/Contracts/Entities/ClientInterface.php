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
interface ClientInterface extends \Whoa\OAuthServer\Contracts\ClientInterface
{
    /**
     * @return int
     */
    public function getIdentity(): int;

    /**
     * @param int $identity
     * @return ClientInterface
     */
    public function setIdentity(int $identity): ClientInterface;

    /**
     * @param string $identifier
     * @return ClientInterface
     */
    public function setIdentifier(string $identifier): ClientInterface;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface;

    /**
     * @param UuidInterface|string|null $uuid
     * @return ClientInterface
     */
    public function setUuid($uuid = null): ClientInterface;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     * @return ClientInterface
     */
    public function setName(?string $name = null): ClientInterface;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     * @return ClientInterface
     */
    public function setDescription(?string $description = null): ClientInterface;

    /**
     * @return string|null
     */
    public function getCredentials(): ?string;

    /**
     * @param string|null $credentials
     * @return ClientInterface
     */
    public function setCredentials(?string $credentials = null): ClientInterface;

    /**
     * @param string[] $redirectUriStrings
     * @return ClientInterface
     */
    public function setRedirectUriStrings(array $redirectUriStrings): ClientInterface;

    /**
     * @param string[] $scopeIdentifiers
     * @return ClientInterface
     */
    public function setScopeIdentifiers(array $scopeIdentifiers): ClientInterface;

    /**
     * @return bool
     */
    public function isPublic(): bool;

    /**
     * @return ClientInterface
     */
    public function setPublic(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function setConfidential(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function useDefaultScopesOnEmptyRequest(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function doNotUseDefaultScopesOnEmptyRequest(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function enableScopeExcess(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function disableScopeExcess(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function enableCodeGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function disableCodeGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function enableImplicitGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function disableImplicitGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function enablePasswordGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function disablePasswordGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function enableClientGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function disableClientGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function enableRefreshGrant(): ClientInterface;

    /**
     * @return ClientInterface
     */
    public function disableRefreshGrant(): ClientInterface;

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface;

    /**
     * @param DateTimeInterface|null $createdAt
     * @return ClientInterface
     */
    public function setCreatedAt(?DateTimeInterface $createdAt = null): ClientInterface;

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface|null $createdAt
     * @return ClientInterface
     */
    public function setUpdatedAt(?DateTimeInterface $createdAt = null): ClientInterface;
}
