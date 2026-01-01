<?php

declare(strict_types=1);

namespace In2code\Texter\Domain\Repository\Llm;

use In2code\Texter\Domain\Service\ConversationHistory;
use In2code\Texter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Http\RequestFactory;

abstract class AbstractRepository
{
    protected string $requestMethod = 'POST';

    public function __construct(
        protected RequestFactory $requestFactory,
        protected ConversationHistory $conversationHistory,
    ) {
    }

    protected function extendPrompt(string $prompt): string
    {
        $prefix = ConfigurationUtility::getConfigurationByKey('promptPrefix');
        if ($prefix !== '') {
            $prefix .= PHP_EOL;
        }
        return $prefix . $prompt;
    }
}
