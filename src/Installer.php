<?php

namespace OpenRouteAI;

class Installer
{
    /**
     * Publish the openroute.php config file to the project root's config directory.
     *
     * @param string|null $targetDir
     * @return bool True if created, false if already exists or fails.
     */
    public static function publishConfig($targetDir = null)
    {
        if ($targetDir === null) {
            // Find project root directory relative to this package src folder
            $targetDir = dirname(__DIR__, 4);
            
            // If running inside the SDK repository itself for development/testing
            if (basename($targetDir) === 'vendor' || !file_exists($targetDir . '/composer.json')) {
                $targetDir = dirname(__DIR__, 1);
            }
        }

        $configDir = $targetDir . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configFile = $configDir . '/openroute.php';
        if (file_exists($configFile)) {
            return false; // Already exists, do not overwrite
        }

        $templateFile = __DIR__ . '/../config/openroute.php';
        if (file_exists($templateFile)) {
            $template = file_get_contents($templateFile);
        } else {
            $template = self::getConfigTemplateFallback();
        }

        return file_put_contents($configFile, $template) !== false;
    }

    /**
     * Fallback template definition in case config/openroute.php is missing.
     *
     * @return string
     */
    private static function getConfigTemplateFallback()
    {
        return <<<'PHP'
<?php

return [
    'api_key' => getenv('OPENROUTER_API_KEY') ?: '',
    'default_model' => getenv('OPENROUTER_DEFAULT_MODEL') ?: 'meta-llama/llama-3.3-70b-instruct',
    'site_url' => getenv('SITE_URL') ?: null,
    'app_name' => getenv('APP_NAME') ?: 'OpenRoute AI PHP SDK',
    'agents' => [
        'summarizer' => [
            'model' => 'meta-llama/llama-3.3-70b-instruct',
            'system_prompt' => 'You are an editor. Summarize the user input into a single concise sentence.',
        ],
        'translator' => [
            'model' => 'meta-llama/llama-3.3-70b-instruct',
            'system_prompt' => 'You are a professional translator. Translate all user input to Spanish. Output ONLY the translation.',
        ]
    ]
];
PHP;
    }
}
