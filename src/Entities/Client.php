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

namespace Whoa\Passport\Entities;

use DateTimeInterface;
use Whoa\Passport\Contracts\Entities\ClientInterface as Entity;
use Whoa\Passport\Contracts\Models\ClientModelInterface as Model;

/**
 * @package Whoa\Passport
 */
abstract class Client extends DatabaseItem implements Entity
{
    /** @var string Field name */
    public const FIELD_ID = Model::FIELD_ID;

    /** @var string Field name */
    public const FIELD_IDENTIFIER = Model::FIELD_IDENTIFIER;

    /** @var string Field name */
    public const FIELD_NAME = Model::FIELD_NAME;

    /** @var string Field name */
    public const FIELD_DESCRIPTION = Model::FIELD_DESCRIPTION;

    /** @var string Field name */
    public const FIELD_CREDENTIALS = Model::FIELD_CREDENTIALS;

    /** @var string Field name */
    public const FIELD_REDIRECT_URIS = Model::REL_REDIRECT_URIS;

    /** @var string Field name */
    public const FIELD_SCOPES = Model::REL_SCOPES;

    /** @var string Field name */
    public const FIELD_IS_CONFIDENTIAL = Model::FIELD_IS_CONFIDENTIAL;

    /** @var string Field name */
    public const FIELD_IS_USE_DEFAULT_SCOPE = Model::FIELD_IS_USE_DEFAULT_SCOPE;

    /** @var string Field name */
    public const FIELD_IS_SCOPE_EXCESS_ALLOWED = Model::FIELD_IS_SCOPE_EXCESS_ALLOWED;

    /** @var string Field name */
    public const FIELD_IS_CODE_GRANT_ENABLED = Model::FIELD_IS_CODE_GRANT_ENABLED;

    /** @var string Field name */
    public const FIELD_IS_IMPLICIT_GRANT_ENABLED = Model::FIELD_IS_IMPLICIT_GRANT_ENABLED;

    /** @var string Field name */
    public const FIELD_IS_PASSWORD_GRANT_ENABLED = Model::FIELD_IS_PASSWORD_GRANT_ENABLED;

    /** @var string Field name */
    public const FIELD_IS_CLIENT_GRANT_ENABLED = Model::FIELD_IS_CLIENT_GRANT_ENABLED;

    /** @var string Field name */
    public const FIELD_IS_REFRESH_GRANT_ENABLED = Model::FIELD_IS_REFRESH_GRANT_ENABLED;

    /**
     * @var int
     */
    private int $identityField = 0;

    /**
     * @var string
     */
    private string $identifierField = '';

    /**
     * @var string|null
     */
    private ?string $nameField = null;

    /**
     * @var string|null
     */
    private ?string $descriptionField = null;

    /**
     * @var string|null
     */
    private ?string $credentialsField = null;

    /**
     * @var string[]
     */
    private array $redirectUriStrings;

    /**
     * @var string[]
     */
    private array $scopeIdentifiers;

    /**
     * @var bool
     */
    private bool $isConfidentialField = false;

    /**
     * @var bool
     */
    private bool $isUseDefaultScopeField = false;

    /**
     * @var bool
     */
    private bool $isScopeExcessAllowedField = false;

    /**
     * @var bool
     */
    private bool $isCodeAuthEnabledField = false;

    /**
     * @var bool
     */
    private bool $isImplicitAuthEnabledField = false;

    /**
     * @var bool
     */
    private bool $isPasswordGrantEnabledField = false;

    /**
     * @var bool
     */
    private bool $isClientGrantEnabledField = false;

    /**
     * @var bool
     */
    private bool $isRefreshGrantEnabledField = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this->setIdentity((int)$this->{static::FIELD_ID})
                ->setIdentifier($this->{static::FIELD_IDENTIFIER})
                ->setName($this->{static::FIELD_NAME})
                ->setDescription($this->{static::FIELD_DESCRIPTION})
                ->setCredentials($this->{static::FIELD_CREDENTIALS})
                ->parseIsConfidential($this->{static::FIELD_IS_CONFIDENTIAL})
                ->parseIsUseDefaultScope($this->{static::FIELD_IS_USE_DEFAULT_SCOPE})
                ->parseIsScopeExcessAllowed($this->{static::FIELD_IS_SCOPE_EXCESS_ALLOWED})
                ->parseIsCodeAuthEnabled($this->{static::FIELD_IS_CODE_GRANT_ENABLED})
                ->parseIsImplicitAuthEnabled($this->{static::FIELD_IS_IMPLICIT_GRANT_ENABLED})
                ->parseIsPasswordGrantEnabled($this->{static::FIELD_IS_PASSWORD_GRANT_ENABLED})
                ->parseIsClientGrantEnabled($this->{static::FIELD_IS_CLIENT_GRANT_ENABLED})
                ->parseIsRefreshGrantEnabled($this->{static::FIELD_IS_REFRESH_GRANT_ENABLED});
        } else {
            $this->setScopeIdentifiers([])->setRedirectUriStrings([]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getIdentity(): int
    {
        return $this->identityField;
    }

    /**
     * @inheritDoc
     */
    public function setIdentity(int $identity): Entity
    {
        $this->identityField = $identity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->identifierField;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(string $identifier): Entity
    {
        $this->identifierField = $identifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUuid($uuid = null): Entity
    {
        /** @var Entity $self */
        $self = $this->setUuidImpl($uuid);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->nameField;
    }

    /**
     * @inheritdoc
     */
    public function setName(?string $name = null): Entity
    {
        $this->nameField = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->descriptionField;
    }

    /**
     * @inheritdoc
     */
    public function setDescription(?string $description = null): Entity
    {
        $this->descriptionField = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCredentials(): ?string
    {
        return $this->credentialsField;
    }

    /**
     * @inheritdoc
     */
    public function setCredentials(?string $credentials = null): Entity
    {
        $this->credentialsField = $credentials;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasCredentials(): bool
    {
        return empty($this->getCredentials()) === false;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUriStrings(): array
    {
        return $this->redirectUriStrings;
    }

    /**
     * @inheritdoc
     */
    public function setRedirectUriStrings(array $redirectUriStrings): Entity
    {
        $this->redirectUriStrings = $redirectUriStrings;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScopeIdentifiers(): array
    {
        return $this->scopeIdentifiers;
    }

    /**
     * @inheritDoc
     */
    public function setScopeIdentifiers(iterable $scopeIdentifiers): Entity
    {
        $this->scopeIdentifiers = $scopeIdentifiers;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConfidential(): bool
    {
        return $this->isConfidentialField;
    }

    /**
     * @inheritdoc
     */
    public function isPublic(): bool
    {
        return $this->isConfidential() === false;
    }

    /**
     * @inheritdoc
     */
    public function setConfidential(): Entity
    {
        $this->isConfidentialField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPublic(): Entity
    {
        $this->isConfidentialField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseDefaultScopesOnEmptyRequest(): bool
    {
        return $this->isUseDefaultScopeField;
    }

    /**
     * @inheritdoc
     */
    public function useDefaultScopesOnEmptyRequest(): Entity
    {
        $this->isUseDefaultScopeField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function doNotUseDefaultScopesOnEmptyRequest(): Entity
    {
        $this->isUseDefaultScopeField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isScopeExcessAllowed(): bool
    {
        return $this->isScopeExcessAllowedField;
    }

    /**
     * @inheritdoc
     */
    public function enableScopeExcess(): Entity
    {
        $this->isScopeExcessAllowedField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disableScopeExcess(): Entity
    {
        $this->isScopeExcessAllowedField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isCodeGrantEnabled(): bool
    {
        return $this->isCodeAuthEnabledField;
    }

    /**
     * @inheritdoc
     */
    public function enableCodeGrant(): Entity
    {
        $this->isCodeAuthEnabledField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disableCodeGrant(): Entity
    {
        $this->isCodeAuthEnabledField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isImplicitGrantEnabled(): bool
    {
        return $this->isImplicitAuthEnabledField;
    }

    /**
     * @inheritdoc
     */
    public function enableImplicitGrant(): Entity
    {
        $this->isImplicitAuthEnabledField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disableImplicitGrant(): Entity
    {
        $this->isImplicitAuthEnabledField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isPasswordGrantEnabled(): bool
    {
        return $this->isPasswordGrantEnabledField;
    }

    /**
     * @inheritdoc
     */
    public function enablePasswordGrant(): Entity
    {
        $this->isPasswordGrantEnabledField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disablePasswordGrant(): Entity
    {
        $this->isPasswordGrantEnabledField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isClientGrantEnabled(): bool
    {
        return $this->isClientGrantEnabledField;
    }

    /**
     * @inheritdoc
     */
    public function enableClientGrant(): Entity
    {
        $this->isClientGrantEnabledField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disableClientGrant(): Entity
    {
        $this->isClientGrantEnabledField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRefreshGrantEnabled(): bool
    {
        return $this->isRefreshGrantEnabledField;
    }

    /**
     * @inheritdoc
     */
    public function enableRefreshGrant(): Entity
    {
        $this->isRefreshGrantEnabledField = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disableRefreshGrant(): Entity
    {
        $this->isRefreshGrantEnabledField = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(?DateTimeInterface $createdAt = null): Entity
    {
        return $this->setCreatedAtImpl($createdAt);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(?DateTimeInterface $createdAt = null): Entity
    {
        return $this->setUpdatedAtImpl($createdAt);
    }

    /**
     * @param string $value
     * @return Client
     */
    protected function parseIsConfidential(string $value): Client
    {
        $value === '1' ? $this->setConfidential() : $this->setPublic();

        return $this;
    }

    /**
     * @param string $value
     * @return Client
     */
    protected function parseIsUseDefaultScope(string $value): Client
    {
        $value === '1' ? $this->useDefaultScopesOnEmptyRequest() : $this->doNotUseDefaultScopesOnEmptyRequest();

        return $this;
    }

    /**
     * @param string $value
     * @return Client
     */
    protected function parseIsScopeExcessAllowed(string $value): Client
    {
        $value === '1' ? $this->enableScopeExcess() : $this->disableScopeExcess();

        return $this;
    }

    /**
     * @param string $value
     *
     * @return Client
     */
    protected function parseIsCodeAuthEnabled(string $value): Client
    {
        $value === '1' ? $this->enableCodeGrant() : $this->disableCodeGrant();

        return $this;
    }

    /**
     * @param string $value
     * @return Client
     */
    protected function parseIsImplicitAuthEnabled(string $value): Client
    {
        $value === '1' ? $this->enableImplicitGrant() : $this->disableImplicitGrant();

        return $this;
    }

    /**
     * @param string $value
     * @return Client
     */
    protected function parseIsPasswordGrantEnabled(string $value): Client
    {
        $value === '1' ? $this->enablePasswordGrant() : $this->disablePasswordGrant();

        return $this;
    }

    /**
     * @param string $value
     * @return Client
     */
    protected function parseIsClientGrantEnabled(string $value): Client
    {
        $value === '1' ? $this->enableClientGrant() : $this->disableClientGrant();

        return $this;
    }

    /**
     * @param string $value
     * @return Client
     */
    protected function parseIsRefreshGrantEnabled(string $value): Client
    {
        $value === '1' ? $this->enableRefreshGrant() : $this->disableRefreshGrant();

        return $this;
    }
}
