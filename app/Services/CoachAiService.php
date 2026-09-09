namespace App\Services;

use App\Actions\Training\BuildCoachContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Exception;

class CoachAiService
{
    protected BuildCoachContext $buildCoachContext;

    public function __construct(BuildCoachContext $buildCoachContext)
    {
        $this->buildCoachContext = $buildCoachContext;
    }

    /**
     * Generate a parsable, safe workout card for an athlete.
     *
     * @param string $playerIdentityId
     * @param string $userRequestPrompt e.g., "Queue up Phase 10, Week 3, Day 4"
     * @param string|null $gymLocation
     * @return string Markdown workout card content
     * @throws Exception
     */
    public function generateWorkoutCard(string $playerIdentityId, string $userRequestPrompt, ?string $gymLocation = null): string
    {
        // 1. Load the master LLM Coach system prompt
        $promptPath = resource_path('prompts/momentum_coach.md');
        if (!File::exists($promptPath)) {
            throw new Exception("LLM Coach system prompt not found at [{$promptPath}]. Please ensure resources/prompts/momentum_coach.md exists.");
        }
        $systemPrompt = File::get($promptPath);

        // 2. Build dynamic athlete JSON context from database
        $contextArray = $this->buildCoachContext->execute($playerIdentityId, $gymLocation);
        $contextJson = json_encode($contextArray, JSON_PRETTY_PRINT);

        // 3. Assemble complete prompt payload
        $fullUserContent = "ATHLETE DATABASE CONTEXT (JSON):\n```json\n{$contextJson}\n```\n\nUSER REQUEST:\n{$userRequestPrompt}";

        // 4. Dispatch to API Provider (OpenAI GPT-4o example)
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            throw new Exception("OpenAI API key is not configured in services.openai.key");
        }

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $fullUserContent,
                    ],
                ],
                'temperature' => 0.3, // Low temperature for high adherence to clinical rules and output syntax
            ]);

        if ($response->failed()) {
            throw new Exception("AI Coach API call failed: " . $response->body());
        }

        $cardContent = $response->json('choices.0.message.content');

        if (empty($cardContent)) {
            throw new Exception("AI Coach returned an empty response.");
        }

        return trim($cardContent);
    }
}
