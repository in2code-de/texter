<?php

declare(strict_types=1);

namespace In2code\Texter\Domain\Settings;

use In2code\Texter\Domain\Model\Setting;
use In2code\Texter\Domain\Model\SettingAccess;
use In2code\Texter\Utility\ConfigurationUtility;

class SettingsDefinition
{
    /**
     * @return Setting[]
     */
    public function getSettings(): array
    {
        return [
            new Setting(
                key: 'apiKey',
                type: Setting::TYPE_PASSWORD,
                environmentVariable: ConfigurationUtility::ENVIRONMENT_API_KEY,
                access: SettingAccess::Admin,
            ),
            new Setting(
                key: 'geminiModel',
                default: ConfigurationUtility::DEFAULT_GEMINI_MODEL,
                environmentVariable: ConfigurationUtility::ENVIRONMENT_GEMINI_MODEL,
            ),
            new Setting(
                key: 'thinkingLevel',
                type: Setting::TYPE_SELECT,
                default: ConfigurationUtility::DEFAULT_THINKING_LEVEL,
                options: ConfigurationUtility::THINKING_LEVELS,
            ),
            new Setting(
                key: 'promptPrefix',
                type: Setting::TYPE_TEXTAREA,
            ),
        ];
    }
}
