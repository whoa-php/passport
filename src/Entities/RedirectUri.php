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
use Laminas\Diactoros\Uri;
use Psr\Http\Message\UriInterface;
use Whoa\Passport\Contracts\Entities\RedirectUriInterface as Entity;
use Whoa\Passport\Contracts\Models\RedirectUriModelInterface as Model;
use Whoa\Passport\Exceptions\InvalidArgumentException;

/**
 * @package Whoa\Passport
 */
abstract class RedirectUri extends DatabaseItem implements Entity
{
    /** @var string Field name */
    public const FIELD_ID = Model::FIELD_ID;

    /** @var string Field name */
    public const FIELD_ID_CLIENT = Model::FIELD_ID_CLIENT;

    /** @var string Field name */
    public const FIELD_VALUE = Model::FIELD_VALUE;

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
     * @var string|null
     */
    private ?string $valueField = null;

    /**
     * @var Uri|null
     */
    private ?Uri $uriObject = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this->setIdentity((int)$this->{static::FIELD_ID})
                ->setClientIdentity((int)$this->{static::FIELD_ID_CLIENT})
                ->setValue($this->{static::FIELD_VALUE});
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
    public function getValue(): ?string
    {
        return $this->valueField;
    }

    /**
     * @inheritdoc
     */
    public function setValue(string $uri): Entity
    {
        // @link https://tools.ietf.org/html/rfc6749#section-3.1.2
        //
        // The redirection endpoint URI MUST be an absolute URI.
        // The endpoint URI MUST NOT include a fragment component.

        $uriObject = new Uri($uri);
        if (empty($uriObject->getHost()) === true || empty($uriObject->getFragment()) === false) {
            throw new InvalidArgumentException('redirect URI');
        }

        $this->valueField = $uri;
        $this->uriObject = $uriObject;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUri(): UriInterface
    {
        return $this->uriObject;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(DateTimeInterface $createdAt): Entity
    {
        return $this->setCreatedAtImpl($createdAt);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(DateTimeInterface $createdAt): Entity
    {
        return $this->setUpdatedAtImpl($createdAt);
    }
}
