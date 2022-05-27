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

namespace Whoa\Passport\Authentication;

use Whoa\Contracts\Passport\PassportAccountInterface;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;

use function array_key_exists;
use function assert;
use function is_int;
use function is_string;

/**
 * @package Whoa\Passport
 */
class PassportAccount implements PassportAccountInterface
{
    /**
     * @var array
     */
    private array $properties;

    /**
     * @var DatabaseSchemaInterface
     */
    private DatabaseSchemaInterface $schema;

    /**
     * @var bool|string
     */
    private $userIdentityKey = false;

    /**
     * @var bool|string
     */
    private $clientIdentityKey = false;

    /**
     * @var bool|string
     */
    private $scopesKey = false;

    /**
     * @param DatabaseSchemaInterface $schema
     * @param array $properties
     */
    public function __construct(DatabaseSchemaInterface $schema, array $properties = [])
    {
        $this->schema = $schema;
        $this->setPassportProperties($properties);
    }

    /**
     * @inheritdoc
     */
    public function setPassportProperties(array $properties): PassportAccountInterface
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasProperty($key): bool
    {
        assert(is_string($key) || is_int($key));

        return array_key_exists($key, $this->properties);
    }

    /**
     * @inheritdoc
     */
    public function getProperty($key)
    {
        assert($this->hasProperty($key));

        return $this->properties[$key];
    }

    /**
     * @inheritdoc
     */
    public function hasUserIdentity(): bool
    {
        return $this->hasProperty($this->getUserIdentityKey());
    }

    /**
     * @inheritdoc
     */
    public function getUserIdentity()
    {
        return $this->getProperty($this->getUserIdentityKey());
    }

    /**
     * @inheritdoc
     */
    public function hasClientIdentity(): bool
    {
        return $this->hasProperty($this->getClientIdentityKey());
    }

    /**
     * @inheritdoc
     */
    public function getClientIdentity()
    {
        return $this->getProperty($this->getClientIdentityKey());
    }

    /**
     * @inheritdoc
     */
    public function hasScopes(): bool
    {
        return $this->hasProperty($this->getScopesKey());
    }

    /**
     * @inheritdoc
     */
    public function getScopes(): array
    {
        return $this->getProperty($this->getScopesKey());
    }

    /**
     * @inheritdoc
     */
    public function hasScope(string $scope): bool
    {
        return $this->hasScopes() === true && in_array($scope, $this->getScopes()) === true;
    }

    /**
     * @return string
     */
    protected function getUserIdentityKey(): string
    {
        if ($this->userIdentityKey === false) {
            $this->userIdentityKey = $this->getSchema()->getTokensUserIdentityColumn();
        }

        return $this->userIdentityKey;
    }

    /**
     * @return string
     */
    protected function getClientIdentityKey(): string
    {
        if ($this->clientIdentityKey === false) {
            $this->clientIdentityKey = $this->getSchema()->getTokensClientIdentityColumn();
        }

        return $this->clientIdentityKey;
    }

    /**
     * @return string
     */
    protected function getScopesKey(): string
    {
        if ($this->scopesKey === false) {
            $this->scopesKey = $this->getSchema()->getTokensViewScopesColumn();
        }

        return $this->scopesKey;
    }

    /**
     * @return DatabaseSchemaInterface
     */
    protected function getSchema(): DatabaseSchemaInterface
    {
        return $this->schema;
    }
}
