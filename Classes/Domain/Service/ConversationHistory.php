<?php

declare(strict_types=1);

namespace In2code\Texter\Domain\Service;

use In2code\Texter\Utility\BackendUtility;

class ConversationHistory
{
    private const SESSION_KEY_PREFIX = 'texter_conversation_history_';
    private const MAX_HISTORY_ITEMS = 20; // Keep last 10 questions (user + model = 2 items each)

    public function getHistory(string $pageId): array
    {
        $sessionKey = $this->getSessionKey($pageId);
        $history = BackendUtility::getSessionData($sessionKey) ?? [];

        // Ensure history doesn't exceed maximum items
        if (count($history) > self::MAX_HISTORY_ITEMS) {
            $history = array_slice($history, -self::MAX_HISTORY_ITEMS);
        }

        return is_array($history) ? $history : [];
    }

    public function saveHistory(array $history, string $pageId): void
    {
        // Limit history size
        if (count($history) > self::MAX_HISTORY_ITEMS) {
            $history = array_slice($history, -self::MAX_HISTORY_ITEMS);
        }

        $sessionKey = $this->getSessionKey($pageId);
        BackendUtility::setAndSaveSessionData($sessionKey, $history);
    }

    public function addUserMessage(array &$history, string $text): void
    {
        $history[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $text]
            ]
        ];
    }

    public function addModelResponse(array &$history, string $text): void
    {
        $history[] = [
            'role' => 'model',
            'parts' => [
                ['text' => $text]
            ]
        ];
    }

    public function clearHistory(string $pageId): void
    {
        $sessionKey = $this->getSessionKey($pageId);
        BackendUtility::setAndSaveSessionData($sessionKey, []);
    }

    protected function getSessionKey(string $pageId): string
    {
        return self::SESSION_KEY_PREFIX . $pageId;
    }
}
