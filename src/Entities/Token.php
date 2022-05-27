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
use Whoa\Passport\Contracts\Entities\TokenInterface as Entity;
use Whoa\Passport\Contracts\Models\TokenModelInterface as Model;

use function assert;
use function implode;
use function is_int;
use function is_string;

/**
 * @package Whoa\Passport
 */
abstract class Token extends DatabaseItem implements Entity
{
    /** @var string Field name */
    public const FIELD_ID = Model::FIELD_ID;

    /** @var string Field name */
    public const FIELD_ID_CLIENT = Model::FIELD_ID_CLIENT;

    /** @var string Field name */
    public const FIELD_ID_USER = 'id_user';

    /** @var string Field name */
    public const FIELD_SCOPES = Model::REL_SCOPES;

    /** @var string Field name */
    public const FIELD_IS_SCOPE_MODIFIED = Model::FIELD_IS_SCOPE_MODIFIED;

    /** @var string Field name */
    public const FIELD_IS_ENABLED = Model::FIELD_IS_ENABLED;

    /** @var string Field name */
    public const FIELD_REDIRECT_URI = Model::FIELD_REDIRECT_URI;

    /** @var string Field name */
    public const FIELD_CODE = Model::FIELD_CODE;

    /** @var string Field name */
    public const FIELD_VALUE = Model::FIELD_VALUE;

    /** @var string Field name */
    public const FIELD_TYPE = Model::FIELD_TYPE;

    /** @var string Field name */
    public const FIELD_REFRESH = Model::FIELD_REFRESH;

    /** @var string Field name */
    public const FIELD_CODE_CREATED_AT = Model::FIELD_CODE_CREATED_AT;

    /** @var string Field name */
    public const FIELD_VALUE_CREATED_AT = Model::FIELD_VALUE_CREATED_AT;

    /** @var string Field name */
    public const FIELD_REFRESH_CREATED_AT = Model::FIELD_REFRESH_CREATED_AT;

    /**
     * @var int
     */
    private int $identityField = 0;

    /**
     * @var int
     */
    private int $clientIdentityField = 0;

    /**
     * @var string
     */
    private string $clientIdentifierField = '';

    /**
     * @var int|string|null
     */
    private $userIdentifierField = null;

    /**
     * @var string[]
     */
    private array $scopeIdentifiers = [];

    /**
     * @var string|null
     */
    private ?string $scopeList = null;

    /**
     * @var bool
     */
    private bool $isScopeModified = false;

    /**
     * @var bool
     */
    private bool $isEnabled = true;

    /**
     * @var string|null
     */
    private ?string $redirectUriString = null;

    /**
     * @var string|null
     */
    private ?string $codeField = null;

    /**
     * @var string|null
     */
    private ?string $valueField = null;

    /**
     * @var string|null
     */
    private ?string $typeField = null;

    /**
     * @var string|null
     */
    private ?string $refreshValueField = null;

    /**
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $codeCreatedAtField = null;

    /**
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $valueCreatedAtField = null;

    /**
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $refreshCreatedAtField = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this->setIdentity((int)$this->{static::FIELD_ID})
                ->setClientIdentity((int)$this->{static::FIELD_ID_CLIENT})
                ->setUserIdentifier((int)$this->{static::FIELD_ID_USER})
                ->setRedirectUriString($this->{static::FIELD_REDIRECT_URI})
                ->setCode($this->{static::FIELD_CODE})
                ->setType($this->{static::FIELD_TYPE})
                ->setValue($this->{static::FIELD_VALUE})
                ->setRefreshValue($this->{static::FIELD_REFRESH})
                ->parseIsScopeModified($this->{static::FIELD_IS_SCOPE_MODIFIED})
                ->parseIsEnabled($this->{static::FIELD_IS_ENABLED});
        }
    }

    /**
     * @inheritdoc
     */
    public function getIdentity(): int
    {
        return $this->identityField;
    }

    /**
     * @inheritdoc
     */
    public function setIdentity(int $identity): Entity
    {
        $this->identityField = $identity;

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
     * @inheritDoc
     */
    public function getClientIdentity(): int
    {
        return $this->clientIdentityField;
    }

    /**
     * @inheritDoc
     */
    public function setClientIdentity(int $clientIdentity): Entity
    {
        $this->clientIdentityField = $clientIdentity;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getClientIdentifier(): string
    {
        return $this->clientIdentifierField;
    }

    /**
     * @inheritdoc
     */
    public function setClientIdentifier(string $identifier): Entity
    {
        $this->clientIdentifierField = $identifier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifierField;
    }

    /**
     * @inheritdoc
     */
    public function setUserIdentifier($identifier): Entity
    {
        assert(is_int($identifier) === true || is_string($identifier) === true);

        $this->userIdentifierField = $identifier;

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
     * @inheritdoc
     */
    public function setScopeIdentifiers(array $identifiers): Entity
    {
        $this->scopeIdentifiers = $identifiers;

        $this->scopeList = null;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScopeList(): string
    {
        if ($this->scopeList === null) {
            $this->scopeList = implode(' ', $this->getScopeIdentifiers());
        }

        return $this->scopeList;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUriString(): ?string
    {
        return $this->redirectUriString;
    }

    /**
     * @inheritdoc
     */
    public function setRedirectUriString(?string $uri): Entity
    {
        $this->redirectUriString = $uri;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isScopeModified(): bool
    {
        return $this->isScopeModified;
    }

    /**
     * @inheritdoc
     */
    public function setScopeModified(): Entity
    {
        $this->isScopeModified = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setScopeUnmodified(): Entity
    {
        $this->isScopeModified = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @inheritdoc
     */
    public function setEnabled(): Entity
    {
        $this->isEnabled = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDisabled(): Entity
    {
        $this->isEnabled = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return $this->codeField;
    }

    /**
     * @inheritdoc
     */
    public function setCode(?string $code): Entity
    {
        $this->codeField = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): ?string
    {
        return $this->valueField;
    }

    /**
     * @inheritdoc
     */
    public function setValue(?string $value): Entity
    {
        $this->valueField = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return $this->typeField;
    }

    /**
     * @inheritdoc
     */
    public function setType(?string $type): Entity
    {
        $this->typeField = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRefreshValue(): ?string
    {
        return $this->refreshValueField;
    }

    /**
     * @inheritdoc
     */
    public function setRefreshValue(?string $refreshValue): Entity
    {
        $this->refreshValueField = $refreshValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCodeCreatedAt(): ?DateTimeInterface
    {
        if ($this->codeCreatedAtField === null && ($codeCreatedAt = $this->{static::FIELD_CODE_CREATED_AT}) !== null) {
            $this->codeCreatedAtField = $this->parseDateTime($codeCreatedAt);
        }

        return $this->codeCreatedAtField;
    }

    /**
     * @inheritdoc
     */
    public function setCodeCreatedAt(DateTimeInterface $codeCreatedAt): Entity
    {
        $this->codeCreatedAtField = $codeCreatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValueCreatedAt(): ?DateTimeInterface
    {
        if ($this->valueCreatedAtField === null &&
            ($tokenCreatedAt = $this->{static::FIELD_VALUE_CREATED_AT}) !== null
        ) {
            $this->valueCreatedAtField = $this->parseDateTime($tokenCreatedAt);
        }

        return $this->valueCreatedAtField;
    }

    /**
     * @inheritdoc
     */
    public function setValueCreatedAt(DateTimeInterface $valueCreatedAt): Entity
    {
        $this->valueCreatedAtField = $valueCreatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRefreshCreatedAt(): ?DateTimeInterface
    {
        if ($this->refreshCreatedAtField === null &&
            ($tokenCreatedAt = $this->{static::FIELD_VALUE_CREATED_AT}) !== null
        ) {
            $this->refreshCreatedAtField = $this->parseDateTime($tokenCreatedAt);
        }

        return $this->refreshCreatedAtField;
    }

    /**
     * @inheritdoc
     */
    public function setRefreshCreatedAt(DateTimeInterface $refreshCreatedAt): Entity
    {
        $this->refreshCreatedAtField = $refreshCreatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(DateTimeInterface $createdAt): Entity
    {
        /** @var Entity $self */
        $self = $this->setCreatedAtImpl($createdAt);

        return $self;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): Entity
    {
        /** @var Entity $self */
        $self = $this->setUpdatedAtImpl($updatedAt);

        return $self;
    }

    /**
     * @inheritdoc
     */
    public function hasBeenUsedEarlier(): bool
    {
        return $this->getValueCreatedAt() !== null;
    }

    /**
     * @param string $value
     * @return Token
     */
    protected function parseIsScopeModified(string $value): Token
    {
        $value === '1' ? $this->setScopeModified() : $this->setScopeUnmodified();

        return $this;
    }

    /**
     * @param string $value
     * @return Token
     */
    protected function parseIsEnabled(string $value): Token
    {
        $value === '1' ? $this->setEnabled() : $this->setDisabled();

        return $this;
    }
}
