# Texter - AI generated texts in TYPO3 with Google Gemini

## Table of Contents

- [Introduction](#introduction)
- [Google Gemini API](#google-gemini-api)
- [Installation](#installation)
- [Custom LLM Integration (like ChatGPT, Claude, Mistral, etc.)](#custom-llm-integration-like-chatgpt-claude-mistral-etc)
- [Changelog and breaking changes](#changelog-and-breaking-changes)
- [Contribution with ddev](#contribution-with-ddev)

## Introduction

Add AI integration to TYPO3 backend. We simply added a CKEditor plugin to generate texts from AI (Gemini). 

Example integration into TYPO3 backend.

Example Video
![documentation_video_rte.gif](Documentation/Images/documentation_video_rte.gif)
Better quality: https://www.youtube.com/watch?v=yPFrigLah3o

Video image #1
![documentation_screenshot_rte1.png](Documentation/Images/documentation_screenshot_rte1.png)

Video image #2
![documentation_screenshot_rte2.png](Documentation/Images/documentation_screenshot_rte2.png)

Video image #3
![documentation_screenshot_rte3.png](Documentation/Images/documentation_screenshot_rte3.png)

Video image #4
![documentation_screenshot_rte4.png](Documentation/Images/documentation_screenshot_rte4.png)

## Google Gemini API

- To use the extension, you need a **Google Gemini API** key. You can register for one
  at https://aistudio.google.com/app/api-keys.
- Alternatively, you can implement your own LLM provider (see [Custom LLM Integration](#custom-llm-integration-like-chatgpt-claude-mistral-etc) below).

## Installation

### With composer

```
composer req in2code/texter
```

### Main configuration

After that, you have to set some initial configuration in Extension Manager configuration:

| Title         | Default value | Description                                                                                                                                          |
|---------------|---------------|------------------------------------------------------------------------------------------------------------------------------------------------------|
| promptPrefix  | -             | Prefix text that should be always added to the prompt at the beginning                                                                               |
| apiKey        | -             | Google Gemini API key. You can let this value empty and simply use ENV_VAR "GOOGLE_API_KEY" instead if you want to use CI pipelines for this setting |

Note: It's recommended to use ENV vars for in2code/imager instead of saving the API-Key in Extension Manager configuration

```
GOOGLE_API_KEY=your_api_key_from_google
```

### RTE configuration

Per default, in2code/texter sets a default RTE configuration via Page TSConfig:

```
RTE.default.preset = texter
```

If you want to overrule this default setting, you can require in2code/texter in your sitepackage (to ensure that your
extension is loaded after texter) and define a different default preset.
Check file [Texter.yaml](Configuration/RTE/Texter.yaml) for an example how to add texter to your RTE configuration.

**Hint** You can also use texter for selected RTE fields in backend. Example Page TSConfig:

```
RTE.config.tt_content.bodytext.preset = texter
RTE.config.tx_news_domain_model_news.bodytext.preset = texter
```

## Custom LLM Integration (like ChatGPT, Claude, Mistral, etc.)

Texter uses a factory pattern to allow custom LLM providers. By default, it uses Google Gemini,
but you can easily integrate other AI services (OpenAI, Claude, local models, etc.).

### Implementing a Custom LLM Repository

1. Create a custom repository class implementing `RepositoryInterface` - see example for OpenAI ChatGPT:

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Domain\Repository\Llm;

use In2code\Texter\Domain\Repository\Llm\AbstractRepository;
use In2code\Texter\Domain\Repository\Llm\RepositoryInterface;
use In2code\Texter\Domain\Service\ConversationHistory;
use In2code\Texter\Exception\ApiException;
use In2code\Texter\Exception\ConfigurationException;
use TYPO3\CMS\Core\Http\RequestFactory;

class ChatGptRepository extends AbstractRepository implements RepositoryInterface
{
    private string $apiKey = '';
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        protected RequestFactory $requestFactory,
        protected ConversationHistory $conversationHistory,
    ) {
        parent::__construct($requestFactory, $conversationHistory);
        $this->apiKey = getenv('OPENAI_API_KEY') ?: '';
    }

    public function checkApiKey(): void
    {
        if ($this->apiKey === '') {
            throw new ConfigurationException('OpenAI API key not configured', 1735646000);
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
        $response = $this->connectToChatGpt($history);
        $this->conversationHistory->addModelResponse($history, $response);
        $this->conversationHistory->saveHistory($history, $pageId);
        return $response;
    }

    protected function connectToChatGpt(array $conversationHistory): string
    {
        // Convert Gemini format to ChatGPT format
        $messages = $this->convertHistoryToChatGptFormat($conversationHistory);

        $payload = [
            'model' => 'gpt-4o',
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 8192,
        ];

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload),
        ];

        $response = $this->requestFactory->request($this->getApiUrl(), $this->requestMethod, $options);

        if ($response->getStatusCode() !== 200) {
            throw new ApiException(
                'Failed to generate text with ChatGPT: ' . $response->getBody()->getContents(),
                1735646001
            );
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (isset($responseData['choices'][0]['message']['content']) === false) {
            throw new ApiException('Invalid ChatGPT API response structure', 1735646002);
        }

        return $responseData['choices'][0]['message']['content'];
    }

    protected function convertHistoryToChatGptFormat(array $geminiHistory): array
    {
        $messages = [];
        foreach ($geminiHistory as $entry) {
            $role = $entry['role'] === 'model' ? 'assistant' : $entry['role'];
            $content = $entry['parts'][0]['text'] ?? '';
            $messages[] = [
                'role' => $role,
                'content' => $content,
            ];
        }
        return $messages;
    }
}
```

2. Register your custom repository in `ext_localconf.php`:

```php
<?php
defined('TYPO3') || die();

// Register custom LLM repository
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['texter']['llmRepositoryClass']
    = \Vendor\MyExtension\Domain\Repository\Llm\ChatGptRepository::class;
```

3. Set your API key as environment variable:

```
OPENAI_API_KEY=your_openai_api_key_here
```

**Hint**: Don't forget to register your Repository in your Services.yaml and flush caches after making these changes.

## Changelog and breaking changes

| Version | Date       | State   | Description                       |
|---------|------------|---------|-----------------------------------|
| 1.0.0   | 2025-12-06 | Task    | Initial release of in2code/texter |



## Contribution with ddev

This repository provides a [DDEV]()-backed development environment. If DDEV is installed, simply run the following
commands to quickly set up a local environment with example usages:

* `ddev start`
* `ddev initialize`

**Backend Login:**
```
Username: admin
Password: admin
```

**Installation hint:**

1. Install ddev before, see: https://ddev.readthedocs.io/en/stable/#installation
2. Install git-lfs before, see: https://git-lfs.github.com/
3. You can place .ddev/.env with the google API key

```
GOOGLE_API_KEY=your_api_key_from_google
```