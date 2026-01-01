<?php

declare(strict_types=1);

namespace In2code\Texter\Domain\Repository\Llm;

use In2code\Texter\Domain\Service\ConversationHistory;
use In2code\Texter\Exception\ApiException;
use In2code\Texter\Exception\ConfigurationException;
use In2code\Texter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Http\RequestFactory;

class GeminiRepository extends AbstractRepository implements RepositoryInterface
{
    private string $apiKey = '';
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct(
        protected RequestFactory $requestFactory,
        protected ConversationHistory $conversationHistory,
    ) {
        parent::__construct($requestFactory, $conversationHistory);
        $this->apiKey = getenv('GOOGLE_API_KEY') ?: ConfigurationUtility::getConfigurationByKey('apiKey') ?: '';
    }

    public function checkApiKey(): void
    {
        if ($this->apiKey === '') {
            throw new ConfigurationException('Google API key not configured', 1764932074);
        }
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getText(string $prompt, string $pageId = '0'): string
    {
        $this->checkApiKey();
        $history = $this->conversationHistory->getHistory($pageId);
        $this->conversationHistory->addUserMessage($history, $this->extendPrompt($prompt));
        $response = $this->connectToLlm($history);
        $this->conversationHistory->addModelResponse($history, $response);
        $this->conversationHistory->saveHistory($history, $pageId);
        return $response;
    }

    protected function connectToLlm(array $conversationHistory): string
    {
        $payload = [
            'contents' => $conversationHistory,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 8192,
                'thinkingConfig' => [
                    'thinkingBudget' => 0,
                ],
            ],
        ];
        $additionalOptions = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload),
        ];
        $url = $this->getApiUrl() . '?key=' . $this->apiKey;
        $response = $this->requestFactory->request($url, $this->requestMethod, $additionalOptions);
        if ($response->getStatusCode() !== 200) {
            throw new ApiException('Failed to generate text: ' . $response->getBody()->getContents(), 1764248401);
        }
        $responseData = json_decode($response->getBody()->getContents(), true);
        if (isset($responseData['candidates'][0]['content']['parts']) === false) {
            throw new ApiException('Invalid response from Gemini API: ' . json_encode($responseData), 1764248402);
        }

        // Extract and return the generated text
        $parts = $responseData['candidates'][0]['content']['parts'];
        foreach ($parts as $part) {
            if (isset($part['text'])) {
                return $part['text'];
            }
        }

        throw new ApiException('No text content found in Gemini API response', 1764248403);
    }
}
