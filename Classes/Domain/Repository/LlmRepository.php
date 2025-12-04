<?php

declare(strict_types=1);

namespace In2code\Texter\Domain\Repository;

use In2code\Texter\Exception\ApiException;
use In2code\Texter\Exception\ConfigurationException;
use In2code\Texter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Http\RequestFactory;

class LlmRepository
{
    private string $apiKey = '';
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct(
        private readonly RequestFactory $requestFactory,
    ) {
        $this->apiKey = getenv('GOOGLE_API_KEY') ?: ConfigurationUtility::getConfigurationByKey('apiKey') ?: '';
    }

    public function getText(string $prompt): string
    {
        $this->checkApiKey();
        return $this->connectToLlm($prompt);
    }

    /**
     * List available models for debugging
     */
    public function listModels(): array
    {
        $this->checkApiKey();
        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $this->apiKey;

        try {
            $response = $this->requestFactory->request($url, 'GET');
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['models'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function connectToLlm(string $prompt): string
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $this->extendPrompt($prompt),
                        ],
                    ],
                ],
            ],
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
        $response = $this->requestFactory->request($url, 'POST', $additionalOptions);
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

    protected function extendPrompt(string $prompt): string
    {
        return $prompt;
    }

    protected function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    protected function checkApiKey(): void
    {
        if ($this->apiKey === '') {
            throw new ConfigurationException('Google API key not configured', 1764932074);
        }
    }
}