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

namespace Whoa\Passport\Integration;

use Doctrine\DBAL\Connection;
use Exception;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Settings\Packages\PassportSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\OAuthServer\Contracts\ClientInterface;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;
use Whoa\Passport\Entities\Client;
use Whoa\Passport\Entities\DatabaseSchema;
use Whoa\Passport\Package\PassportSettings as C;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function array_filter;
use function assert;
use function bin2hex;
use function call_user_func;
use function implode;
use function password_verify;
use function random_bytes;
use function uniqid;

/**
 * @package Whoa\Passport
 */
abstract class BasePassportServerIntegration implements PassportServerIntegrationInterface
{
    /** @var string Approval parameter */
    public const SCOPE_APPROVAL_TYPE = 'type';

    /** @var string Approval parameter */
    public const SCOPE_APPROVAL_CLIENT_ID = 'client_id';

    /** @var string Approval parameter */
    public const SCOPE_APPROVAL_CLIENT_NAME = 'client_name';

    /** @var string Approval parameter */
    public const SCOPE_APPROVAL_REDIRECT_URI = 'redirect_uri';

    /** @var string Approval parameter */
    public const SCOPE_APPROVAL_IS_SCOPE_MODIFIED = 'is_scope_modified';

    /** @var string Approval parameter */
    public const SCOPE_APPROVAL_SCOPE = 'scope';

    /** @var string Approval parameter */
    public const SCOPE_APPROVAL_STATE = 'state';

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var array
     */
    private array $settings;

    /**
     * @var string
     */
    private string $defaultClientId;

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @var DatabaseSchemaInterface|null
     */
    private ?DatabaseSchemaInterface $databaseSchema = null;

    /**
     * @var string
     */
    private string $approvalUriString;

    /**
     * @var string
     */
    private string $errorUriString;

    /**
     * @var int
     */
    private int $codeExpiration;

    /**
     * @var int
     */
    private int $tokenExpiration;
    /**
     * @var bool
     */
    private bool $isRenewRefreshValue;

    /**
     * @var callable|null
     */
    private $customPropProvider;

    /**
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->settings = $container->get(SettingsProviderInterface::class)->get(C::class);

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        /** @var callable|null $customPropProvider */
        $customPropProvider = $this->settings[PassportSettingsInterface::KEY_TOKEN_CUSTOM_PROPERTIES_PROVIDER] ?? null;
        $wrapper = $customPropProvider !== null ?
            function (TokenInterface $token) use ($container, $customPropProvider): array {
                return call_user_func($customPropProvider, $container, $token);
            } : null;

        $this->defaultClientId = $this->settings[PassportSettingsInterface::KEY_DEFAULT_CLIENT_ID];
        $this->connection = $connection;
        $this->approvalUriString = $this->settings[PassportSettingsInterface::KEY_APPROVAL_URI_STRING];
        $this->errorUriString = $this->settings[PassportSettingsInterface::KEY_ERROR_URI_STRING];
        $this->codeExpiration = $this->settings[PassportSettingsInterface::KEY_CODE_EXPIRATION_TIME_IN_SECONDS] ?? 600;
        $this->tokenExpiration = $this->settings[PassportSettingsInterface::KEY_TOKEN_EXPIRATION_TIME_IN_SECONDS] ?? 3600;
        $this->isRenewRefreshValue = $this->settings[PassportSettingsInterface::KEY_RENEW_REFRESH_VALUE_ON_TOKEN_REFRESH] ?? false;
        $this->customPropProvider = $wrapper;
    }

    /**
     * @inheritdoc
     */
    public function validateUserId(string $userName, ?string $password = null, $extras = null)
    {
        $validator = $this->settings[PassportSettingsInterface::KEY_USER_CREDENTIALS_VALIDATOR];

        return call_user_func($validator, $this->getContainer(), $userName, $password, $extras);
    }

    /**
     * @inheritdoc
     */
    public function verifyAllowedUserScope(int $userIdentity, array $scope = null): ?array
    {
        $validator = $this->settings[PassportSettingsInterface::KEY_USER_SCOPE_VALIDATOR];

        return call_user_func($validator, $this->getContainer(), $userIdentity, $scope);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultClientIdentifier(): string
    {
        return $this->defaultClientId;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function generateCodeValue(TokenInterface $token): string
    {
        $codeValue = bin2hex(random_bytes(16)) . uniqid();

        assert(empty($codeValue) === false);

        return $codeValue;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function generateTokenValues(TokenInterface $token): array
    {
        $tokenValue = bin2hex(random_bytes(16)) . uniqid();
        $tokenType = 'bearer';
        $tokenExpiresIn = $this->getTokenExpirationPeriod();
        $refreshValue = bin2hex(random_bytes(16)) . uniqid();

        assert(empty($tokenValue) === false);
        assert(empty($tokenType) === false);
        assert($tokenExpiresIn > 0);
        assert(empty($refreshValue) === false);

        return [$tokenValue, $tokenType, $tokenExpiresIn, $refreshValue];
    }

    /**
     * @inheritdoc
     */
    public function getCodeExpirationPeriod(): int
    {
        return $this->codeExpiration;
    }

    /**
     * @inheritdoc
     */
    public function getTokenExpirationPeriod(): int
    {
        return $this->tokenExpiration;
    }

    /**
     * @inheritdoc
     */
    public function isRenewRefreshValue(): bool
    {
        return $this->isRenewRefreshValue;
    }

    /**
     * @inheritdoc
     */
    public function createInvalidClientAndRedirectUriErrorResponse(): ResponseInterface
    {
        return new RedirectResponse($this->getErrorUriString());
    }

    /**
     * @inheritdoc
     */
    public function createAskResourceOwnerForApprovalResponse(
        string $type,
        ClientInterface $client,
        string $redirectUri = null,
        bool $isScopeModified = false,
        array $scopeList = null,
        string $state = null,
        array $extraParameters = []
    ): ResponseInterface {
        /** @var Client $client */
        assert($client instanceof Client);

        // TODO think if we can receive objects instead of individual properties
        $scopeList = empty($scopeList) === true ? null : implode(' ', $scopeList);
        $filtered = array_filter([
            self::SCOPE_APPROVAL_TYPE => $type,
            self::SCOPE_APPROVAL_CLIENT_ID => $client->getIdentifier(),
            self::SCOPE_APPROVAL_CLIENT_NAME => $client->getName(),
            self::SCOPE_APPROVAL_REDIRECT_URI => $redirectUri,
            self::SCOPE_APPROVAL_IS_SCOPE_MODIFIED => $isScopeModified,
            self::SCOPE_APPROVAL_SCOPE => $scopeList,
            self::SCOPE_APPROVAL_STATE => $state,
        ], function ($value) {
            return $value !== null;
        });

        return new RedirectResponse($this->createRedirectUri($this->getApprovalUriString(), $filtered));
    }

    /**
     * @inheritdoc
     */
    public function verifyClientCredentials(ClientInterface $client, string $credentials): bool
    {
        /** @var \Whoa\Passport\Contracts\Entities\ClientInterface $client */
        assert($client instanceof \Whoa\Passport\Contracts\Entities\ClientInterface);

        return password_verify($credentials, $client->getCredentials());
    }

    /**
     * @inheritdoc
     */
    public function getBodyTokenExtraParameters(TokenInterface $token): array
    {
        return $this->customPropProvider !== null ? call_user_func($this->customPropProvider, $token) : [];
    }

    /**
     * @param string $uri
     * @param array $data
     *
     * @return UriInterface
     */
    protected function createRedirectUri(string $uri, array $data): UriInterface
    {
        $query = http_build_query($data, '', '&', PHP_QUERY_RFC3986);

        return (new Uri($uri))->withQuery($query);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return DatabaseSchemaInterface
     */
    protected function getDatabaseSchema(): DatabaseSchemaInterface
    {
        if ($this->databaseSchema === null) {
            $this->databaseSchema = new DatabaseSchema();
        }

        return $this->databaseSchema;
    }

    /**
     * @return string
     */
    protected function getApprovalUriString(): string
    {
        return $this->approvalUriString;
    }

    /**
     * @return string
     */
    protected function getErrorUriString(): string
    {
        return $this->errorUriString;
    }
}
