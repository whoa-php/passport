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
use Whoa\Passport\Contracts\Entities\ScopeInterface as Entity;
use Whoa\Passport\Contracts\Models\ScopeModelInterface as Model;

/**
 * @package Whoa\Passportv
 */
abstract class Scope extends DatabaseItem implements Entity
{
    /** @var string Field name */
    public const FIELD_ID = Model::FIELD_ID;

    /** @var string Field name */
    public const FIELD_IDENTIFIER = Model::FIELD_IDENTIFIER;

    /** @var string Field name */
    public const FIELD_NAME = Model::FIELD_NAME;

    /** @var string Field name */
    public const FIELD_DESCRIPTION = Model::FIELD_DESCRIPTION;

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
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this->setIdentity((int)$this->{static::FIELD_ID})
                ->setIdentifier($this->{static::FIELD_IDENTIFIER})
                ->setName($this->{static::FIELD_NAME})
                ->setDescription($this->{static::FIELD_DESCRIPTION});
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
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->nameField;
    }

    /**
     * @inheritDoc
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
}
