<?php

declare(strict_types=1);

namespace In2code\Texter\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendUtility
{
    public static function getSessionData(string $key): mixed
    {
        return self::getBackendUser()->getSessionData($key);
    }

    public static function setAndSaveSessionData(string $key, mixed $data): void
    {
        self::getBackendUser()->setAndSaveSessionData($key, $data);
    }

    public static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
