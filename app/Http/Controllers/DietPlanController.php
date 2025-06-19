<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DietPlanController extends Controller
{
    /**
     * Generate a new diet plan using OpenAI
     */
    public function generate(Request $request)
    {
        try {
            $request->validate([
                'prompt' => 'required|string',
                'title' => 'required|string|max:255',
                'plan_type' => 'required|string',
                'meals_per_day' => 'required|integer|min:3|max:6',
                'meal_complexity' => 'required|string|in:simple,moderate,complex',
                'client_id' => 'nullable|integer|exists:users,id',
                'custom_calories' => 'nullable|integer',
                'additional_notes' => 'nullable|string'
            ]);

            $trainerId = Auth::id();

            Log::info('Diet Plan Generation Request', [
                'trainer_id' => $trainerId,
                'request_data' => $request->only(['title', 'plan_type', 'meals_per_day', 'meal_complexity', 'client_id'])
            ]);

            // Get client information if provided
            $clientInfo = '';
            if ($request->client_id) {
                $client = DB::table('users')->find($request->client_id);
                if ($client) {
                    $clientInfo = "\nClient: {$client->first_name} {$client->last_name}";
                    
                    // Calculate age from date_of_birth if available
                    if (isset($client->date_of_birth) && $client->date_of_birth) {
                        $age = \Carbon\Carbon::parse($client->date_of_birth)->age;
                        $clientInfo .= "\nAge: {$age}";
                    }
                    
                    if (isset($client->location) && $client->location) $clientInfo .= "\nLocation: {$client->location}";
                    if (isset($client->gym) && $client->gym) $clientInfo .= "\nGym: {$client->gym}";
                    
                    // Note: The following fields don't exist in the current users table schema
                    // If you need these fields, consider adding them to the users table or creating a separate user_profiles table
                    // if (isset($client->weight) && $client->weight) $clientInfo .= "\nWeight: {$client->weight}kg";
                    // if (isset($client->height) && $client->height) $clientInfo .= "\nHeight: {$client->height}cm";
                    // if (isset($client->activity_level) && $client->activity_level) $clientInfo .= "\nActivity Level: {$client->activity_level}";
                    // if (isset($client->fitness_goals) && $client->fitness_goals) $clientInfo .= "\nFitness Goals: {$client->fitness_goals}";
                    // if (isset($client->dietary_restrictions) && $client->dietary_restrictions) $clientInfo .= "\nDietary Restrictions: {$client->dietary_restrictions}";
                }
            }

            // Create comprehensive prompt for OpenAI
            $fullPrompt = $this->buildDietPlanPrompt(
                $request->plan_type,
                $request->meals_per_day,
                $request->meal_complexity,
                $request->custom_calories,
                $clientInfo,
                $request->additional_notes ?? ''
            );

            Log::info('Sending prompt to OpenAI', ['prompt_length' => strlen($fullPrompt)]);

            // Call OpenAI API
            $aiResponse = $this->callOpenAI($fullPrompt);
            
            Log::info('OpenAI Response received', ['response_length' => strlen(json_encode($aiResponse))]);

            // Parse the AI response
            $parsedPlan = $this->parseOpenAIResponse($aiResponse);

            // Save diet plan to database
            $planId = DB::table('diet_plans')->insertGetId([
                'trainer_id' => $trainerId,
                'client_id' => $request->client_id,
                'title' => $request->title,
                'description' => 'AI-generated diet plan - ' . ucfirst($request->plan_type),
                'plan_type' => $request->plan_type,
                'meals_per_day' => $request->meals_per_day,
                'meal_complexity' => $request->meal_complexity,
                'total_calories' => $parsedPlan['daily_totals']['calories'] ?? 0,
                'generated_by_ai' => true,
                'ai_prompt' => $fullPrompt,
                'ai_response' => json_encode($aiResponse),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Save individual meals
            foreach ($parsedPlan['meals'] as $meal) {
                DB::table('diet_plan_items')->insert([
                    'diet_plan_id' => $planId,
                    'meal_name' => $meal['name'],
                    'meal_type' => $meal['type'],
                    'ingredients' => json_encode($meal['ingredients']),
                    'instructions' => $meal['instructions'] ?? '',
                    'calories' => $meal['calories'] ?? 0,
                    'protein' => $meal['protein'] ?? 0,
                    'carbs' => $meal['carbs'] ?? 0,
                    'fats' => $meal['fats'] ?? 0,
                    'meal_order' => $meal['order'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Get the complete plan with items
            $plan = $this->getPlanWithItems($planId);

            return response()->json([
                'success' => true,
                'plan' => $plan,
                'message' => 'Diet plan generated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Diet plan generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate diet plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all diet plans for the authenticated trainer
     */
    public function index()
    {
        try {
            $trainerId = Auth::id();
            
            $plans = DB::table('diet_plans')
                ->leftJoin('users', 'diet_plans.client_id', '=', 'users.id')
                ->where('diet_plans.trainer_id', $trainerId)
                ->whereNull('diet_plans.deleted_at')
                ->select(
                    'diet_plans.*',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as client_name')
                )
                ->orderBy('diet_plans.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'plans' => $plans
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching diet plans', [
                'error' => $e->getMessage(),
                'trainer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch diet plans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific diet plan with its items
     */
    public function show($id)
    {
        try {
            $trainerId = Auth::id();
            
            $plan = DB::table('diet_plans')
                ->leftJoin('users', 'diet_plans.client_id', '=', 'users.id')
                ->where('diet_plans.id', $id)
                ->where('diet_plans.trainer_id', $trainerId)
                ->whereNull('diet_plans.deleted_at')
                ->select(
                    'diet_plans.*',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as client_name')
                )
                ->first();

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diet plan not found'
                ], 404);
            }

            // Get plan items
            $items = DB::table('diet_plan_items')
                ->where('diet_plan_id', $id)
                ->orderBy('meal_order')
                ->get();
                
            $plan->items = $items;

            return response()->json([
                'success' => true,
                'plan' => $plan
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching diet plan', [
                'error' => $e->getMessage(),
                'plan_id' => $id,
                'trainer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch diet plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a diet plan to a client
     */
    public function assignToClient(Request $request, $planId)
    {
        try {
            $request->validate([
                'client_id' => 'required|integer|exists:users,id'
            ]);

            $trainerId = Auth::id();
            
            // Verify the plan belongs to this trainer
            $plan = DB::table('diet_plans')
                ->where('id', $planId)
                ->where('trainer_id', $trainerId)
                ->whereNull('deleted_at')
                ->first();

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diet plan not found'
                ], 404);
            }

            // Update the client assignment
            DB::table('diet_plans')
                ->where('id', $planId)
                ->update([
                    'client_id' => $request->client_id,
                    'updated_at' => now()
                ]);

            // Get updated plan with client info
            $updatedPlan = DB::table('diet_plans')
                ->leftJoin('users', 'diet_plans.client_id', '=', 'users.id')
                ->where('diet_plans.id', $planId)
                ->select(
                    'diet_plans.*',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as client_name')
                )
                ->first();

            return response()->json([
                'success' => true,
                'plan' => $updatedPlan,
                'message' => 'Diet plan assigned successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning diet plan', [
                'error' => $e->getMessage(),
                'plan_id' => $planId,
                'trainer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign diet plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a diet plan
     */
    public function duplicate(Request $request, $planId)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'client_id' => 'nullable|integer|exists:users,id'
            ]);

            $trainerId = Auth::id();
            
            // Get the original plan
            $originalPlan = DB::table('diet_plans')
                ->where('id', $planId)
                ->where('trainer_id', $trainerId)
                ->whereNull('deleted_at')
                ->first();

            if (!$originalPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Original diet plan not found'
                ], 404);
            }

            // Create new plan
            $newPlanId = DB::table('diet_plans')->insertGetId([
                'trainer_id' => $trainerId,
                'client_id' => $request->client_id,
                'title' => $request->title,
                'description' => $originalPlan->description,
                'plan_type' => $originalPlan->plan_type,
                'meals_per_day' => $originalPlan->meals_per_day,
                'meal_complexity' => $originalPlan->meal_complexity,
                'total_calories' => $originalPlan->total_calories,
                'generated_by_ai' => $originalPlan->generated_by_ai,
                'ai_prompt' => $originalPlan->ai_prompt,
                'ai_response' => $originalPlan->ai_response,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Copy plan items
            $originalItems = DB::table('diet_plan_items')
                ->where('diet_plan_id', $planId)
                ->get();

            foreach ($originalItems as $item) {
                DB::table('diet_plan_items')->insert([
                    'diet_plan_id' => $newPlanId,
                    'meal_name' => $item->meal_name,
                    'meal_type' => $item->meal_type,
                    'ingredients' => $item->ingredients,
                    'instructions' => $item->instructions,
                    'calories' => $item->calories,
                    'protein' => $item->protein,
                    'carbs' => $item->carbs,
                    'fats' => $item->fats,
                    'meal_order' => $item->meal_order,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Get the new plan with client info
            $newPlan = DB::table('diet_plans')
                ->leftJoin('users', 'diet_plans.client_id', '=', 'users.id')
                ->where('diet_plans.id', $newPlanId)
                ->select(
                    'diet_plans.*',
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as client_name')
                )
                ->first();

            return response()->json([
                'success' => true,
                'plan' => $newPlan,
                'message' => 'Diet plan duplicated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicating diet plan', [
                'error' => $e->getMessage(),
                'plan_id' => $planId,
                'trainer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate diet plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a diet plan
     */
    public function destroy($planId)
    {
        try {
            $trainerId = Auth::id();
            
            // Verify the plan belongs to this trainer
            $plan = DB::table('diet_plans')
                ->where('id', $planId)
                ->where('trainer_id', $trainerId)
                ->whereNull('deleted_at')
                ->first();

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diet plan not found'
                ], 404);
            }

            // Soft delete the plan
            DB::table('diet_plans')
                ->where('id', $planId)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Diet plan deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting diet plan', [
                'error' => $e->getMessage(),
                'plan_id' => $planId,
                'trainer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete diet plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build the diet plan prompt for OpenAI
     */
    private function buildDietPlanPrompt($planType, $mealsPerDay, $complexity, $customCalories, $clientInfo, $additionalNotes)
    {
        $prompt = "Create a detailed {$planType} diet plan with the following specifications:\n\n";
        $prompt .= "Meals per day: {$mealsPerDay}\n";
        $prompt .= "Meal complexity: {$complexity}\n";
        
        if ($customCalories) {
            $prompt .= "Target daily calories: {$customCalories}\n";
        }
        
        if ($clientInfo) {
            $prompt .= "\nClient Information:{$clientInfo}\n";
        }
        
        if ($additionalNotes) {
            $prompt .= "\nAdditional Notes: {$additionalNotes}\n";
        }
        
        $prompt .= "\nPlease provide a comprehensive diet plan in JSON format with the following structure:\n";
        $prompt .= "{\n";
        $prompt .= "  \"meals\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"name\": \"Meal Name\",\n";
        $prompt .= "      \"type\": \"breakfast/lunch/dinner/snack\",\n";
        $prompt .= "      \"order\": 1,\n";
        $prompt .= "      \"ingredients\": [\n";
        $prompt .= "        {\n";
        $prompt .= "          \"name\": \"ingredient name\",\n";
        $prompt .= "          \"amount\": \"quantity with unit (e.g., 200g, 1 cup, 2 pieces)\"\n";
        $prompt .= "        }\n";
        $prompt .= "      ],\n";
        $prompt .= "      \"instructions\": \"Preparation instructions\",\n";
        $prompt .= "      \"calories\": 400,\n";
        $prompt .= "      \"protein\": 25,\n";
        $prompt .= "      \"carbs\": 45,\n";
        $prompt .= "      \"fats\": 15\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"daily_totals\": {\n";
        $prompt .= "    \"calories\": 2000,\n";
        $prompt .= "    \"protein\": 150,\n";
        $prompt .= "    \"carbs\": 200,\n";
        $prompt .= "    \"fats\": 65\n";
        $prompt .= "  }\n";
        $prompt .= "}\n\n";
        $prompt .= "Please ensure the response is valid JSON only, with no additional text or formatting.";
        
        return $prompt;
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt)
    {
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])
        ->withOptions([
            'verify' => false,
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]
        ])
        ->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional nutritionist and diet expert. Create detailed, balanced diet plans based on user specifications. Always respond with valid JSON format only.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 3500,
            'temperature' => 0.7
        ]);

        if (!$response->successful()) {
            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to get response from OpenAI API: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Parse OpenAI response
     */
    private function parseOpenAIResponse($aiResponse)
    {
        try {
            $content = $aiResponse['choices'][0]['message']['content'];
            
            // Clean up the content - remove any markdown formatting
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*$/', '', $content);
            $content = trim($content);
            
            $parsed = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON parsing error', [
                    'content' => $content,
                    'error' => json_last_error_msg()
                ]);
                throw new \Exception('Failed to parse OpenAI response as JSON');
            }
            
            // Validate the structure
            if (!isset($parsed['meals']) || !isset($parsed['daily_totals'])) {
                throw new \Exception('Invalid diet plan structure from OpenAI');
            }
            
            return $parsed;
            
        } catch (\Exception $e) {
            Log::error('Error parsing OpenAI response', [
                'error' => $e->getMessage(),
                'response' => $aiResponse
            ]);
            
            // Return a fallback structure
            return [
                'meals' => [
                    [
                        'name' => 'Balanced Breakfast',
                        'type' => 'breakfast',
                        'order' => 1,
                        'ingredients' => ['Oatmeal', 'Banana', 'Almonds', 'Greek Yogurt'],
                        'instructions' => 'Cook oatmeal and top with sliced banana, almonds, and a dollop of Greek yogurt',
                        'calories' => 400,
                        'protein' => 15,
                        'carbs' => 50,
                        'fats' => 12
                    ],
                    [
                        'name' => 'Grilled Chicken Lunch',
                        'type' => 'lunch',
                        'order' => 2,
                        'ingredients' => ['Chicken breast', 'Brown rice', 'Broccoli', 'Olive oil'],
                        'instructions' => 'Grill chicken breast, steam broccoli, serve with brown rice drizzled with olive oil',
                        'calories' => 500,
                        'protein' => 35,
                        'carbs' => 45,
                        'fats' => 15
                    ],
                    [
                        'name' => 'Salmon Dinner',
                        'type' => 'dinner',
                        'order' => 3,
                        'ingredients' => ['Salmon fillet', 'Sweet potato', 'Asparagus', 'Lemon'],
                        'instructions' => 'Bake salmon with lemon, roast sweet potato and asparagus',
                        'calories' => 450,
                        'protein' => 30,
                        'carbs' => 35,
                        'fats' => 18
                    ]
                ],
                'daily_totals' => [
                    'calories' => 1350,
                    'protein' => 80,
                    'carbs' => 130,
                    'fats' => 45
                ]
            ];
        }
    }

    /**
     * Get plan with items
     */
    private function getPlanWithItems($planId, $trainerId = null)
    {
        $query = DB::table('diet_plans')
            ->leftJoin('users', 'diet_plans.client_id', '=', 'users.id')
            ->select(
                'diet_plans.*',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as client_name")
            )
            ->where('diet_plans.id', $planId);
            
        if ($trainerId) {
            $query->where('diet_plans.trainer_id', $trainerId);
        }
        
        $plan = $query->first();
        
        if ($plan) {
            $items = DB::table('diet_plan_items')
                ->where('diet_plan_id', $planId)
                ->orderBy('meal_order')
                ->get();
                
            $plan->items = $items;
        }
        
        return $plan;
    }
}
