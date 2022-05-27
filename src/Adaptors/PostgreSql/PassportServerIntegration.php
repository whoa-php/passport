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

namespace Whoa\Passport\Adaptors\PostgreSql;

use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Contracts\Repositories\ClientRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\RedirectUriRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\ScopeRepositoryInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Whoa\Passport\Integration\BasePassportServerIntegration;

/**
 * @package Whoa\Passport
 */
class PassportServerIntegration extends BasePassportServerIntegration
{
    /**
     * @var ClientRepositoryInterface|null
     */
    private ?ClientRepositoryInterface $clientRepo = null;

    /**
     * @var TokenRepositoryInterface|null
     */
    private ?TokenRepositoryInterface $tokenRepo = null;

    /**
     * @var ScopeRepositoryInterface|null
     */
    private ?ScopeRepositoryInterface $scopeRepo = null;

    /**
     * @var RedirectUriRepositoryInterface|null
     */
    private ?RedirectUriRepositoryInterface $uriRepo = null;

    /**
     * @inheritdoc
     */
    public function getClientRepository(): ClientRepositoryInterface
    {
        if ($this->clientRepo === null) {
            $this->clientRepo = new ClientRepository($this->getConnection(), $this->getDatabaseSchema());
        }

        return $this->clientRepo;
    }

    /**
     * @inheritdoc
     */
    public function getScopeRepository(): ScopeRepositoryInterface
    {
        if ($this->scopeRepo === null) {
            $this->scopeRepo = new ScopeRepository($this->getConnection(), $this->getDatabaseSchema());
        }

        return $this->scopeRepo;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUriRepository(): RedirectUriRepositoryInterface
    {
        if ($this->uriRepo === null) {
            $this->uriRepo = new RedirectUriRepository($this->getConnection(), $this->getDatabaseSchema());
        }

        return $this->uriRepo;
    }

    /**
     * @inheritdoc
     */
    public function getTokenRepository(): TokenRepositoryInterface
    {
        if ($this->tokenRepo === null) {
            $this->tokenRepo = new TokenRepository($this->getConnection(), $this->getDatabaseSchema());
        }

        return $this->tokenRepo;
    }

    /**
     * @inheritdoc
     */
    public function createTokenInstance(): TokenInterface
    {
        return new Token();
    }
}
