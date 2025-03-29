<?php
namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BotService
{
    protected $apiKey;
    protected $baseUrl;
    protected $model;
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('HUGGING_FACE_API_KEY');

        $this->model   = "meta-llama/Meta-Llama-3.1-8B-Instruct-fast";
        $this->baseUrl = 'https://router.huggingface.co/nebius/v1/chat/completions';

        // $this->model   = "google/gemma-2-2b-it";
        // $this->baseUrl = 'https://router.huggingface.co/hf-inference/models/google/gemma-2-2b-it/v1/chat/completions';

        // $this->model   = "Qwen/Qwen2.5-VL-7B-Instruct";
        // $this->baseUrl = 'https://router.huggingface.co/hf-inference/models/Qwen/Qwen2.5-VL-7B-Instruct/v1/chat/completions';

    }

    public function generateResponse(string $prompt, $sessionId)
    {
        $engineeredPrompt    = $this->applyPromptEngineering($prompt);
        $conversationHistory = Cache::get("chat_history_{$sessionId}", []);

        $conversationHistory[] = ['role' => 'user', 'content' => $engineeredPrompt];

        if (count($conversationHistory) > 10) {
            $conversationHistory = array_slice($conversationHistory, -10);
        }

        $postData = [
            "model"    => $this->model,
            "messages" => $conversationHistory,
            "stream"   => false,
        ];

        try {

            $response = $this->client->post($this->baseUrl, [
                "headers" => [
                    "Authorization" => "Bearer " . $this->apiKey,
                    "Content-type"  => "application/json",
                ],
                "json"    => $postData,
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info($body);

            $botResponse = $body['choices'][0]['message']['content'] ?? '';

            return $botResponse;

        } catch (Exception $e) {
            Log::error("Hugging Face API error: " . $e->getMessage());
            return ['error' => "Falied to generate response"];
        }
    }

    private function applyPromptEngineering(string $prompt)
    {

        $engineeredPrompt = "You are an AI System designed to generate course details for an educational platform called VistaLearn for a user. You will receive a message from a user and provide a structured response in the following format:

            Title: [Course Title]
            Excerpt: [A brief, engaging summary of the course content, no more than 2-3 sentences]
            Description: [A detailed explanation of the course, including objectives, target audience, and what learners can expect to gain from the course]

            If the user request does not relate to a course or learning content, kindly let them know that the request is outside the scope of EduCreate. Respond in a friendly and professional tone, encouraging them to provide a relevant prompt.

            Politely ignore questions that contain profane words or adult content. If the prompt involves profane words or adult content, start the response with the text [RED FLAG] and provide a polite response similar to Example A and B.

            Example A:
            user:
            I want a course about [profane/adult content].
            assistant:
            [RED FLAG] I'm here to assist with educational content and learning-related inquiries. If you have any topics or subjects you'd like to learn about, feel free to let me know!

            Example B:
            user:
            Can you create a course on how to rob a bank?
            assistant:
            [RED FLAG] I'm here to help with educational content that's safe, ethical, and beneficial. If you have any learning topics in mind, feel free to share them!

            Example 1:
            user: I want a course about Laravel for beginners.
            assistant:
            Title: Laravel for Beginners
            Excerpt: An introductory course to Laravel, designed to help you build dynamic web applications with ease.
            Description: This course covers the fundamentals of Laravel, including routing, controllers, views, models, and authentication. By the end of the course, you will be able to create robust web applications using Laravel's powerful framework. Ideal for beginners looking to break into backend web development.

            Example 2:
            user: Create a course about AI and Machine Learning fundamentals.
            assistant:
            Title: AI and Machine Learning Fundamentals
            Excerpt: Dive into the world of artificial intelligence and machine learning with this comprehensive introductory course.
            Description: This course explores the foundational concepts of AI and machine learning, including supervised and unsupervised learning, neural networks, and deep learning. You'll gain hands-on experience building models and understanding real-world applications. Perfect for beginners and enthusiasts eager to start their AI journey.

            Provide an answer starting with something like 'Sure, I can help you with that.' or something similar. Then, proceed to generate the course details based on the user's input.

    user: " . $prompt . "
    assistant:";

        return $engineeredPrompt;
    }

    public function clearChatHistory($sessionId)
    {
        if (Cache::has("chat_history_{$sessionId}")) {
            Cache::forget("chat_history_{$sessionId}");
            Log::info("Cache for user with sessionId: {$sessionId} cleared");
        } else {
            Log::info('No cache exists for user with sessionId: ' . $sessionId);
        }
    }

    public function resetUserChat($sessionId)
    {
        $this->clearChatHistory($sessionId);
        return response()->json(['message' => 'Chat history cleared successfully.']);
    }

    
}
