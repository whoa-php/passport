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
use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Models\Token as Model;
use function assert;
use function implode;
use function is_int;
use function is_string;

/**
 * @package Whoa\Passport
 */
abstract class Token extends DatabaseItem implements TokenInterface
{
    /** Field name */
    const FIELD_ID = Model::FIELD_ID;

    /** Field name */
    const FIELD_ID_CLIENT = Model::FIELD_ID_CLIENT;

    /** Field name */
    const FIELD_ID_USER = 'id_user';

    /** Field name */
    const FIELD_SCOPES = Model::REL_SCOPES;

    /** Field name */
    const FIELD_IS_SCOPE_MODIFIED = Model::FIELD_IS_SCOPE_MODIFIED;

    /** Field name */
    const FIELD_IS_ENABLED = Model::FIELD_IS_ENABLED;

    /** Field name */
    const FIELD_REDIRECT_URI = Model::FIELD_REDIRECT_URI;

    /** Field name */
    const FIELD_CODE = Model::FIELD_CODE;

    /** Field name */
    const FIELD_VALUE = Model::FIELD_VALUE;

    /** Field name */
    const FIELD_TYPE = Model::FIELD_TYPE;

    /** Field name */
    const FIELD_REFRESH = Model::FIELD_REFRESH;

    /** Field name */
    const FIELD_CODE_CREATED_AT = Model::FIELD_CODE_CREATED_AT;

    /** Field name */
    const FIELD_VALUE_CREATED_AT = Model::FIELD_VALUE_CREATED_AT;

    /** Field name */
    const FIELD_REFRESH_CREATED_AT = Model::FIELD_REFRESH_CREATED_AT;

    /**
     * @var int|null
     */
    private $identifierField;

    /**
     * @var string
     */
    private $clientIdentifierField = '';

    /**
     * @var int|string|null
     */
    private $userIdentifierField;

    /**
     * @var string[]
     */
    private $scopeIdentifiers = [];

    /**
     * @var string|null
     */
    private $scopeList = null;

    /**
     * @var bool
     */
    private $isScopeModified = false;

    /**
     * @var bool
     */
    private $isEnabled = true;

    /**
     * @var string|null
     */
    private $redirectUriString = null;

    /**
     * @var string|null
     */
    private $codeField = null;

    /**
     * @var string|null
     */
    private $valueField = null;

    /**
     * @var string|null
     */
    private $typeField = null;

    /**
     * @var string|null
     */
    private $refreshValueField = null;

    /**
     * @var DateTimeInterface|null
     */
    private $codeCreatedAtField = null;

    /**
     * @var DateTimeInterface|null
     */
    private $valueCreatedAtField = null;

    /**
     * @var DateTimeInterface|null
     */
    private $refreshCreatedAtField = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this
                ->setIdentifier((int)$this->{static::FIELD_ID})
                ->setClientIdentifier($this->{static::FIELD_ID_CLIENT})
                ->setUserIdentifier((int)$this->{static::FIELD_ID_USER})
                ->setRedirectUriString($this->{static::FIELD_REDIRECT_URI})
                ->setCode($this->{static::FIELD_CODE})
                ->setType($this->{static::FIELD_TYPE})
                ->setValue($this->{static::FIELD_VALUE})
                ->setRefreshValue($this->{static::FIELD_REFRESH});
            $this
                ->parseIsScopeModified($this->{static::FIELD_IS_SCOPE_MODIFIED})
                ->parseIsEnabled($this->{static::FIELD_IS_ENABLED});
        }
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?int
    {
        return $this->identifierField;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(int $identifier): TokenInterface
    {
        $this->identifierField = $identifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUuid($uuid = null): TokenInterface
    {
        /** @var TokenInterface $self */
        $self = $this->setUuidImpl($uuid);

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
    public function setClientIdentifier(string $identifier): TokenInterface
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
    public function setUserIdentifier($identifier): TokenInterface
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
    public function setScopeIdentifiers(array $identifiers): TokenInterface
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
    public function setRedirectUriString(?string $uri): TokenInterface
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
    public function setScopeModified(): TokenInterface
    {
        $this->isScopeModified = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setScopeUnmodified(): TokenInterface
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
    public function setEnabled(): TokenInterface
    {
        $this->isEnabled = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDisabled(): TokenInterface
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
    public function setCode(?string $code): TokenInterface
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
    public function setValue(?string $value): TokenInterface
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
    public function setType(?string $type): TokenInterface
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
    public function setRefreshValue(?string $refreshValue): TokenInterface
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
    public function setCodeCreatedAt(DateTimeInterface $codeCreatedAt): TokenInterface
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
    public function setValueCreatedAt(DateTimeInterface $valueCreatedAt): TokenInterface
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
    public function setRefreshCreatedAt(DateTimeInterface $refreshCreatedAt): TokenInterface
    {
        $this->refreshCreatedAtField = $refreshCreatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(DateTimeInterface $createdAt): TokenInterface
    {
        /** @var TokenInterface $self */
        $self = $this->setCreatedAtImpl($createdAt);

        return $self;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): TokenInterface
    {
        /** @var TokenInterface $self */
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
     *
     * @return Token
     */
    protected function parseIsScopeModified(string $value): Token
    {
        $value === '1' ? $this->setScopeModified() : $this->setScopeUnmodified();

        return $this;
    }

    /**
     * @param string $value
     *
     * @return Token
     */
    protected function parseIsEnabled(string $value): Token
    {
        $value === '1' ? $this->setEnabled() : $this->setDisabled();

        return $this;
    }
}
