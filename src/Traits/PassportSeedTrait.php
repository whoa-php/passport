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

use Whoa\Passport\Adaptors\Generic\RedirectUri;
use Whoa\Passport\Adaptors\Generic\Scope;
use Whoa\Passport\Contracts\Entities\ClientInterface;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;

/**
 * @package Whoa\Passport
 */
trait PassportSeedTrait
{
    /**
     * @param PassportServerIntegrationInterface $integration
     * @param ClientInterface                    $client
     * @param array                              $scopeDescriptions
     * @param string[]                           $redirectUris
     *
     * @return void
     */
    protected function seedClient(
        PassportServerIntegrationInterface $integration,
        ClientInterface $client,
        array $scopeDescriptions,
        array $redirectUris = []
    ): void
    {
        $scopeIds  = $client->getScopeIdentifiers();
        $scopeRepo = $integration->getScopeRepository();
        foreach ($scopeDescriptions as $scopeId => $scopeDescription) {
            $scopeRepo->create(
                (new Scope())
                    ->setIdentifier($scopeId)
                    ->setDescription($scopeDescription)
            );
            $scopeIds[] = $scopeId;
        }

        $integration->getClientRepository()->create($client->setScopeIdentifiers($scopeIds));

        $uriRepo = $integration->getRedirectUriRepository();
        foreach ($redirectUris as $uri) {
            $uriRepo->create(
                (new RedirectUri())
                    ->setValue($uri)
                    ->setClientIdentifier($client->getIdentifier())
            );
        }
    }
}
