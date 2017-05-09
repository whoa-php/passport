<?php namespace Limoncello\Templates\Commands;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use Limoncello\Contracts\Commands\IoInterface;
use Limoncello\Templates\Package\TemplatesSettings;
use Limoncello\Templates\TwigTemplates;
use Psr\Container\ContainerInterface;

/**
 * @package Limoncello\Templates
 */
class TemplatesCreate extends TemplatesBase
{
    /**
     * @inheritdoc
     */
    public function getCommandData(): array
    {
        return [
            self::COMMAND_NAME        => 'l:cache:templates:create',
            self::COMMAND_DESCRIPTION => 'Creates templates caches.',
            self::COMMAND_HELP        => 'This command creates caches for HTML templates.',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getArguments(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function execute(ContainerInterface $container, IoInterface $inOut)
    {
        $settings        = $this->getTemplatesSettings($container);
        $cacheFolder     = $settings[TemplatesSettings::KEY_CACHE_FOLDER];
        $templatesFolder = $settings[TemplatesSettings::KEY_TEMPLATES_FOLDER];
        $templates       = TemplatesSettings::getTemplateNames($templatesFolder);
        $templateEngine  = $this->createCachingTemplateEngine($templatesFolder, $cacheFolder);

        foreach ($templates as $templateName) {
            // it will write template to cache
            $templateEngine->getTwig()->resolveTemplate($templateName);
        }
    }

    /**
     * @param string $templatesFolder
     * @param string $cacheFolder
     *
     * @return TwigTemplates
     */
    protected function createCachingTemplateEngine(string $templatesFolder, string $cacheFolder): TwigTemplates
    {
        $templates = new TwigTemplates($templatesFolder, $cacheFolder);

        return $templates;
    }
}