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

namespace Whoa\Passport\Traits;

use Whoa\OAuthServer\Contracts\ClientInterface;
use Whoa\OAuthServer\Exceptions\OAuthTokenBodyException;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function assert;
use function base64_decode;
use function count;
use function explode;
use function is_string;
use function substr;

/**
 * @package Whoa\Passport
 */
trait BasicClientAuthenticationTrait
{
    /**
     * @param PassportServerIntegrationInterface $integration
     * @param ServerRequestInterface $request
     * @param array $parameters
     * @param string $realm
     *
     * @return ClientInterface|null
     */
    protected function determineClient(
        PassportServerIntegrationInterface $integration,
        ServerRequestInterface $request,
        array $parameters,
        string $realm = 'OAuth'
    ): ?ClientInterface {
        // A client may use Basic authentication.
        //
        // Or
        //
        // A client MAY use the "client_id" request parameter to identify itself
        // when sending requests to the token endpoint.
        // @link https://tools.ietf.org/html/rfc6749#section-3.2.1

        $authorizationHeader = $request->getHeader('Authorization');

        // try to parse `Authorization` header for client ID and credentials
        $clientId = null;
        $clientCredentials = null;
        $errorHeaders = ['WWW-Authenticate' => 'Basic realm="' . $realm . '"'];
        if (empty($headerArray = $authorizationHeader) === false) {
            $errorCode = OAuthTokenBodyException::ERROR_INVALID_CLIENT;
            if (empty($authHeader = $headerArray[0]) === true ||
                ($tokenPos = strpos($authHeader, 'Basic ')) === false ||
                $tokenPos !== 0 ||
                ($authValue = substr($authHeader, 6)) === '' ||
                $authValue === false ||
                ($decodedValue = base64_decode($authValue, true)) === false ||
                ($idAndCredentials = explode(':', $decodedValue, 2)) === false
            ) {
                throw new OAuthTokenBodyException($errorCode, null, 401, $errorHeaders);
            }
            $headerPartsCount = count($idAndCredentials);
            switch ($headerPartsCount) {
                case 1:
                    $clientId = $idAndCredentials[0];
                    break;
                case 2:
                default:
                    $clientId = $idAndCredentials[0];
                    $clientCredentials = $idAndCredentials[1];
                    break;
            }
        }

        // check if client ID was specified in parameters it should match
        if (array_key_exists('client_id', $parameters) === true &&
            is_string($value = $parameters['client_id']) === true
        ) {
            if ($clientId !== null && $clientId !== $value) {
                $errorCode = OAuthTokenBodyException::ERROR_INVALID_REQUEST;
                throw new OAuthTokenBodyException($errorCode, null, 400, $errorHeaders);
            }

            $clientId = $value;
            unset($value);
        }

        // when we are here we know if any client ID and credentials were given

        $client = null;
        if ($clientId !== null) {
            assert(is_string($clientId));
            $errorCode = OAuthTokenBodyException::ERROR_INVALID_CLIENT;
            if (($client = $integration->getClientRepository()->read($clientId)) === null) {
                throw new OAuthTokenBodyException($errorCode, null, 401, $errorHeaders);
            }

            // check credentials
            if ($clientCredentials !== null) {
                assert(is_string($clientCredentials));
                // we got the password
                if ($integration->verifyClientCredentials($client, $clientCredentials) === false) {
                    throw new OAuthTokenBodyException($errorCode, null, 401, $errorHeaders);
                }
            } elseif ($client->isConfidential() === true || $client->hasCredentials() === true) {
                // no password provided
                throw new OAuthTokenBodyException($errorCode, null, 401, $errorHeaders);
            }
        }

        return $client;
    }
}
