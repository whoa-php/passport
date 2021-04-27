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
use Whoa\Passport\Contracts\Entities\RedirectUriInterface;
use Whoa\Passport\Exceptions\InvalidArgumentException;
use Whoa\Passport\Models\RedirectUri as Model;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

/**
 * @package Whoa\Passport
 */
abstract class RedirectUri extends DatabaseItem implements RedirectUriInterface
{
    /** Field name */
    const FIELD_ID = Model::FIELD_ID;

    /** Field name */
    const FIELD_ID_CLIENT = Model::FIELD_ID_CLIENT;

    /** Field name */
    const FIELD_VALUE = Model::FIELD_VALUE;

    /**
     * @var int|null
     */
    private $identifierField;

    /**
     * @var string|null
     */
    private $clientIdentifierField;

    /**
     * @var string|null
     */
    private $valueField;

    /**
     * @var Uri|null
     */
    private $uriObject;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this
                ->setIdentifier((int)$this->{static::FIELD_ID})
                ->setClientIdentifier($this->{static::FIELD_ID_CLIENT})
                ->setValue($this->{static::FIELD_VALUE});
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
    public function setIdentifier(int $identifier): RedirectUriInterface
    {
        $this->identifierField = $identifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUuid($uuid = null): RedirectUriInterface
    {
        /** @var RedirectUriInterface $self */
        $self = $this->setUuidImpl($uuid);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getClientIdentifier(): ?string
    {
        return $this->clientIdentifierField;
    }

    /**
     * @inheritdoc
     */
    public function setClientIdentifier(string $identifier): RedirectUriInterface
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
    public function setValue(string $uri): RedirectUriInterface
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
        $this->uriObject  = $uriObject;

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
    public function setCreatedAt(DateTimeInterface $createdAt): RedirectUriInterface
    {
        /** @var RedirectUriInterface $self */
        $self = $this->setCreatedAtImpl($createdAt);

        return $self;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(DateTimeInterface $createdAt): RedirectUriInterface
    {
        /** @var RedirectUriInterface $self */
        $self = $this->setUpdatedAtImpl($createdAt);

        return $self;
    }
}
