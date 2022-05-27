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

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Authentication\AccountInterface;
use Whoa\Contracts\Authentication\AccountManagerInterface;
use Whoa\Contracts\Passport\PassportAccountInterface;
use Whoa\Contracts\Passport\PassportAccountManagerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Whoa\Passport\Exceptions\AuthenticationException;
use Psr\Container\ContainerInterface;
use Whoa\Passport\Package\PassportSettings as S;
use Psr\Log\LoggerAwareTrait;

/**
 * @package Whoa\Passport
 */
class AccountManager implements PassportAccountManagerInterface
{
    use LoggerAwareTrait;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var null|PassportAccountInterface
     */
    private ?PassportAccountInterface $account = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function getAccount(): ?AccountInterface
    {
        return $this->getPassport();
    }

    /**
     * @inheritdoc
     */
    public function getPassport(): ?PassportAccountInterface
    {
        return $this->account;
    }

    /**
     * @inheritdoc
     */
    public function setAccount(AccountInterface $account): AccountManagerInterface
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setAccountWithTokenValue(string $value): PassportAccountInterface
    {
        /** @var TokenRepositoryInterface $tokenRepo */
        $tokenRepo = $this->getContainer()->get(TokenRepositoryInterface::class);
        $expInSeconds = $this->getPassportSettings()[S::KEY_TOKEN_EXPIRATION_TIME_IN_SECONDS];
        $properties = $tokenRepo->readPassport($value, $expInSeconds);
        if ($properties === null) {
            throw new AuthenticationException();
        }

        /** @var DatabaseSchemaInterface $schema */
        $schema = $this->getContainer()->get(DatabaseSchemaInterface::class);
        $account = new PassportAccount($schema, $properties);
        $this->setAccount($account);

        $this->logger === null ?: $this->logger->info('Passport account is set for a given token value.');

        return $account;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getPassportSettings(): array
    {
        return $this->getContainer()->get(SettingsProviderInterface::class)->get(S::class);
    }
}
