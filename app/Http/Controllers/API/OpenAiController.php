<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OpenAiController extends Controller
{
    private const CHAT_URL = 'https://api.openai.com/v1/chat/completions';
    private const IMAGE_URL = 'https://api.openai.com/v1/images/generations';
    private const MODEL = 'gpt-4o-mini';

    public function recipeFromImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_base64' => 'required|string',
            'notes' => 'nullable|string|max:2000',
            'dish_type' => 'nullable|string|max:100',
            'cooking_method' => 'nullable|string|max:100',
            'age_group' => 'nullable|string|max:50',
            'difficulty' => 'nullable|string|max:50',
            'attach_image' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $dishType = $request->input('dish_type', 'Hot');
        $cookingMethod = $request->input('cooking_method', 'Gas (Adult Only)');
        $ageGroup = $request->input('age_group', '9–10');
        $difficulty = $request->input('difficulty', 'With Parent');
        $notes = $request->input('notes', '');

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional chef and child-safe cooking instructor.',
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => <<<TEXT
Identify the dish from the image and generate ONE complete recipe.

Safety Constraints:
• Dish Type: {$dishType}
• Cooking Method: {$cookingMethod}
• Age Group: {$ageGroup}
• Difficulty: {$difficulty}

STRICT OUTPUT RULES:
• Valid JSON only
• No markdown
• No explanations
• Quotes required

FORMAT (MANDATORY):

{
  "name": "Dish Name",
  "description": "Short, kid-friendly description",
  "cookingTime": "Exact total time required (e.g. 10 minutes, 2–4 hours chilling, 30 minutes total)",
  "ingredients": ["ingredient1", "ingredient2"],
  "steps": ["Step 1", "Step 2"]
}

RULES FOR cookingTime:
• MUST be a string
• MUST NOT be empty
• If no heat is used, include prep or chilling time
• NEVER use symbols like — or null

Notes: {$notes}
TEXT
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:image/jpeg;base64,'.$request->image_base64,
                        ],
                    ],
                ],
            ],
        ];

        $recipe = $this->chatJson($messages, 0.4);
        if ($recipe instanceof \Illuminate\Http\JsonResponse) {
            return $recipe;
        }

        if ($request->boolean('attach_image', true)) {
            $recipe['imageUrl'] = $this->safeImageUrl($recipe['name'] ?? 'food dish');
        }

        return Helper::responseFunction(true, 200, 'Recipe generated successfully!', [
            'recipe' => $recipe,
        ], 200);
    }

    public function randomRecipe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dish_type' => 'required|string|max:100',
            'cooking_method' => 'required|string|max:100',
            'age_group' => 'required|string|max:50',
            'difficulty' => 'required|string|max:50',
            'used_signatures' => 'nullable|array',
            'used_signatures.*' => 'string|max:500',
            'attach_image' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $used = $request->input('used_signatures', []);
        $usedRecipesText = empty($used) ? 'None' : implode(' | ', $used);

        $dishType = $request->dish_type;
        $cookingMethod = $request->cooking_method;
        $ageGroup = $request->age_group;
        $difficulty = $request->difficulty;

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a child-safe cooking instructor. You must NEVER repeat or slightly modify an existing dish.',
            ],
            [
                'role' => 'user',
                'content' => <<<TEXT
Generate ONE COMPLETELY NEW kid-friendly recipe.

🚫 ALREADY GENERATED DISHES (DO NOT REPEAT OR SIMILARIZE):
{$usedRecipesText}

STRICT RULES:
• Name must be NEW
• Dish type must be DIFFERENT
• Core ingredients must be DIFFERENT
• Cooking style must be DIFFERENT
• NO sandwich/toast/wrap repeats
• NO flavor variants of same dish

Constraints:
• Dish Type: {$dishType}
• Cooking Method: {$cookingMethod}
• Age Group: {$ageGroup}
• Difficulty: {$difficulty}

Safety:
• Real food only
• Common household ingredients
• Child-safe steps

If you cannot create a completely new dish,
CREATE A DIFFERENT CATEGORY DISH (snack, bowl, dessert, drink).

OUTPUT JSON ONLY:

{
  "name": "",
  "description": "",
  "cookingTime": "Exact total time (e.g. 15 minutes, 30 minutes total)",
  "ingredients": [],
  "steps": []
}

RULES:
• cookingTime MUST be a string
• MUST NOT be empty
• If no heat is used, include prep or chilling time
TEXT
            ],
        ];

        $recipe = $this->chatJson($messages, 0.7);
        if ($recipe instanceof \Illuminate\Http\JsonResponse) {
            return $recipe;
        }

        if ($request->boolean('attach_image', true)) {
            $recipe['imageUrl'] = $this->safeImageUrl($recipe['name'] ?? 'food dish');
        }

        return Helper::responseFunction(true, 200, 'Recipe generated successfully!', [
            'recipe' => $recipe,
        ], 200);
    }

    public function recipesFromIngredients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ingredients' => 'required|array|min:1',
            'ingredients.*' => 'string|max:100',
            'notes' => 'nullable|string|max:2000',
            'dish_type' => 'nullable|string|max:100',
            'cooking_method' => 'nullable|string|max:100',
            'age_group' => 'nullable|string|max:50',
            'difficulty' => 'nullable|string|max:50',
            'attach_image' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $ingredients = implode(', ', $request->ingredients);
        $dishType = $request->input('dish_type', 'Hot');
        $cookingMethod = $request->input('cooking_method', 'Gas (Adult Only)');
        $ageGroup = $request->input('age_group', '9–10');
        $difficulty = $request->input('difficulty', 'With Parent');
        $notes = $request->input('notes', 'None');

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional chef and child-safe cooking instructor.',
            ],
            [
                'role' => 'user',
                'content' => <<<TEXT
You MUST generate EXACTLY 5 DISTINCT recipes.

Base ingredient(s):
{$ingredients}

CATEGORY ASSIGNMENTS (MANDATORY):
Recipe 1 → Sandwich / Toast / Open-faced
Recipe 2 → Snack / Finger Food
Recipe 3 → Main Course (Indian or International)
Recipe 4 → Baked OR Oven Dish (NOT bread loaf)
Recipe 5 → Sweet OR Fusion Dish

STRICT RULES:
• Dish names MUST be unique
• Cooking techniques MUST differ
• You MAY add extra ingredients
• Recipes must feel REAL & different (Paneer Bhurji ≠ Paneer Butter Masala)

Safety Constraints:
• Dish Type: {$dishType}
• Cooking Method: {$cookingMethod}
• Age Group: {$ageGroup}
• Difficulty: {$difficulty}

OUTPUT RULES:
• JSON ONLY
• No markdown
• No explanation text

FORMAT:
{
  "recipes": [
    {
      "name": "Dish Name",
      "description": "Short, fun and kid-friendly description",
      "cookingTime": "Exact total time (e.g. 20 minutes, 2–4 hours chilling)",
      "ingredients": ["ingredient1", "ingredient2"],
      "steps": ["Step 1", "Step 2"]
    }
  ]
}

RULES FOR cookingTime:
• MUST be present
• MUST be a readable string
• Include prep + cooking or chilling time

Notes: {$notes}
TEXT
            ],
        ];

        $payload = $this->chatJson($messages, 0.95);
        if ($payload instanceof \Illuminate\Http\JsonResponse) {
            return $payload;
        }

        $recipes = $payload['recipes'] ?? [];
        if (! is_array($recipes) || empty($recipes)) {
            return Helper::responseFunction(false, 422, 'No recipes returned. Please try again.', null, 422);
        }

        if ($request->boolean('attach_image', true)) {
            foreach ($recipes as &$recipe) {
                if (is_array($recipe)) {
                    $recipe['imageUrl'] = $this->safeImageUrl($recipe['name'] ?? 'food dish');
                }
            }
            unset($recipe);
        }

        return Helper::responseFunction(true, 200, 'Recipes generated successfully!', [
            'recipes' => $recipes,
        ], 200);
    }

    public function recipeFromIngredients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ingredients' => 'required|array|min:1',
            'ingredients.*' => 'string|max:100',
            'index' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:2000',
            'dish_type' => 'required|string|max:100',
            'cooking_method' => 'required|string|max:100',
            'age_group' => 'required|string|max:50',
            'difficulty' => 'required|string|max:50',
            'attach_image' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $ingredients = implode(', ', $request->ingredients);
        $dishType = $request->dish_type;
        $cookingMethod = $request->cooking_method;
        $ageGroup = $request->age_group;
        $difficulty = $request->difficulty;
        $index = (int) $request->input('index', 0);
        $notes = $request->input('notes', 'None');

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional chef and child-safe instructor.',
            ],
            [
                'role' => 'user',
                'content' => <<<TEXT
You are a STRICT rule-following AI chef.

Generate ONE recipe that MUST FOLLOW ALL CONSTRAINTS BELOW.

🔹 BASE INGREDIENTS (MANDATORY):
{$ingredients}

🔹 FILTER CONSTRAINTS (NON-NEGOTIABLE):
• Dish Type: {$dishType}
• Cooking Method: {$cookingMethod}
• Age Group: {$ageGroup}
• Difficulty: {$difficulty}

Recipe index: {$index}

RULES:
• Dish MUST match Dish Type
• Steps MUST respect Cooking Method
• Language MUST be suitable for Age Group
• Difficulty MUST match exactly
• You MAY add extra ingredients
• Must be DIFFERENT from previous recipes
• Use a DIFFERENT cooking style

🚫 If constraints are not possible, ADAPT the dish while STILL respecting ALL filters.

OUTPUT JSON ONLY (NO MARKDOWN, NO TEXT):

{
  "name": "",
  "description": "",
  "cookingTime": "Exact total time required",
  "ingredients": [],
  "steps": []
}

RULES:
• cookingTime is REQUIRED
• Must be a string (no —, null, or empty)

Notes (optional): {$notes}
TEXT
            ],
        ];

        $recipe = $this->chatJson($messages, 0.9);
        if ($recipe instanceof \Illuminate\Http\JsonResponse) {
            return $recipe;
        }

        if ($request->boolean('attach_image', true)) {
            $recipe['imageUrl'] = $this->safeImageUrl($recipe['name'] ?? 'food dish');
        }

        return Helper::responseFunction(true, 200, 'Recipe generated successfully!', [
            'recipe' => $recipe,
        ], 200);
    }

    public function generateImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $url = $this->safeImageUrl($request->title);

        return Helper::responseFunction(true, 200, 'Image generated successfully!', [
            'image_url' => $url,
        ], 200);
    }

    /**
     * @return array|\Illuminate\Http\JsonResponse
     */
    private function chatJson(array $messages, float $temperature)
    {
        $apiKey = Helper::getOpenAiApiKey();
        if (empty($apiKey)) {
            return Helper::responseFunction(false, 503, 'OpenAI API key is not configured. Please add it in admin settings.', null, 503);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->acceptJson()
                ->post(self::CHAT_URL, [
                    'model' => self::MODEL,
                    'messages' => $messages,
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => $temperature,
                ]);
        } catch (\Throwable $e) {
            Log::error('OpenAI chat request failed', ['error' => $e->getMessage()]);

            return Helper::responseFunction(false, 502, 'Unable to reach OpenAI. Please try again.', null, 502);
        }

        if (! $response->successful()) {
            return Helper::responseFunction(false, $response->status(), $this->friendlyOpenAiError($response), null, $response->status() >= 500 ? 502 : $response->status());
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            return Helper::responseFunction(false, 422, 'Empty recipe response. Please try again.', null, 422);
        }

        $text = trim($content);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```$/', '', $text) ?? $text;
            $text = trim($text);
        }

        $decoded = json_decode($text, true);
        if (! is_array($decoded)) {
            Log::warning('OpenAI returned non-JSON content', ['content' => $text]);

            return Helper::responseFunction(false, 422, 'Could not parse recipe data. Please try again.', null, 422);
        }

        return $decoded;
    }

    private function safeImageUrl(string $title): string
    {
        $apiKey = Helper::getOpenAiApiKey();
        if (empty($apiKey)) {
            return '';
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(90)
                ->acceptJson()
                ->post(self::IMAGE_URL, [
                    'prompt' => 'High quality food photograph of: '.$title.', appetizing, centered',
                    'n' => 1,
                    'size' => '512x512',
                ]);

            if (! $response->successful()) {
                return '';
            }

            return (string) data_get($response->json(), 'data.0.url', '');
        } catch (\Throwable $e) {
            Log::warning('OpenAI image generation failed', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    private function friendlyOpenAiError($response): string
    {
        $message = data_get($response->json(), 'error.message');
        if (is_string($message) && $message !== '') {
            return $message;
        }

        return match (true) {
            $response->status() === 401 => 'OpenAI authentication failed. Check the API key in admin settings.',
            $response->status() === 429 => 'OpenAI rate limit reached. Please try again shortly.',
            $response->status() >= 500 => 'OpenAI is temporarily unavailable. Please try again.',
            default => 'Recipe generation failed. Please try again.',
        };
    }
}
