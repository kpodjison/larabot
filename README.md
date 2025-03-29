
## Larabot

A powerful Laravel-based AI bot built using HuggingFace API and advanced prompt engineering techniques. This project demonstrates how to integrate pre-trained language models to process user inputs, generate structured responses, and handle conversational tasks. Perfect for building order processing bots, customer service assistants, form autofills and more.

## Technologies Used
- Laravel 12
- HuggingFace Inference API
- GuzzleHTTP  (For API request)

**💡**: [Hugging Face API-Inference DOCS](https://huggingface.co/docs/api-inference/tasks/chat-completion)

**💡**: [Supported Models](https://huggingface.co/docs/api-inference/supported-models)

## Installation

1. Clone the repository

   ```bash
   https://github.com/kpodjison/larabot.git
   cd larabot

2. Install dependencies

    ```bash   
    composer install

3. Set up the environment     
    copy .env.example file and rename it to .env
    update HUGGING_FACE_API_KEY key in env with your Hugging Face API key

4. Start backend server

    ```bash   
    php artisan serve

5. Make a post request to the API endpoint [api/bot](http://127.0.0.1:8000/api/bot)  in your API client

    ```json   
   {
    "prompt":"I would like to create a laravel course",
    "userId":"jvc12343"
   }

    Example of context-aware conversations

        ```json
        {
            "prompt":"I want to include topics like routes, controllers, and views.",
            "userId":"jvc12343"
        }