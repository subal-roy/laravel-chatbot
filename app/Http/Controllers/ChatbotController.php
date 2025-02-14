<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $client = new Client();
        $apiKey = env('HUGGINGFACE_API_KEY');
        $url = env('HUGGINGFACE_MODEL_URL');

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer $apiKey",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => "You are a helpful chatbot. Answer questions concisely and accurately.\nUser: " . $request->message . ' ###',
                    'parameters' => [
                        'temperature' => 0.7,
                        'max_new_tokens' => 1000,
                        'stop' => ["\nUser:"],
                    ],
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            $generatedText = isset($responseData[0]['generated_text']) ? trim($responseData[0]['generated_text']) : 'Sorry, I did not understand that.';

            $reply = '';
            if(strpos($generatedText, 'Assistant:') !== false){
                $reply = explode('Assistant:', $generatedText, 2)[1] ?? '';
                $reply = preg_replace('/\s*User:\s*$/', '', $reply);
            }
            elseif (strpos($generatedText, ' ###') !== false) {
                $reply = explode(' ###', $generatedText, 2)[1] ?? '';
                $reply = preg_replace('/\s*User:\s*$/', '', $reply);
            } 
            else {
                $reply = $generatedText;
            }

            return response()->json(['reply' => $reply]);
        } catch (RequestException $e) {
            return response()->json(['reply' => 'Error communicating with the chatbot.'], 500);
        }
    }
}
