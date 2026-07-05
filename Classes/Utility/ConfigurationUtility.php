<?php

declare(strict_types=1);

namespace In2code\Texter\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationUtility
{
    private const DEFAULT_GEMINI_MODEL = 'gemini-2.5-flash:generateContent';

    public static function getConfigurationByKey(string $key): string
    {
        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('texter');
        return $configuration[$key] ?? '';
    }

    public static function getModel(): string
    {
        return getenv('GOOGLE_GEMINI_MODEL') ?: self::getConfigurationByKey('geminiModel') ?: self::DEFAULT_GEMINI_MODEL;
    }
}
