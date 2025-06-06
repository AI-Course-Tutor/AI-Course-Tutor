<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

class GPT {
    private $openai_client;

    public function __construct()
    {
        $this->openai_client = OpenAI::client($_ENV['OPENAI_API_KEY']);
    }

    public function getResponse($chat_messages) {

        $status = "ok";
        $tokens_prompt = 0;
        $tokens_completion = 0;

        try {
            $chat_completion = $this->openai_client->chat()->create([
                'model' => 'gpt-4o-mini-2024-07-18', // name of model that should be used
                'n' => 1, // ensure we get only 1 answer (this is already the default, just to make sure)
                'max_tokens' => 4096, // maximum number allowed for gpt-4o-mini (is also default, just to make sure)
                'messages' => $chat_messages,
            ]);

            $response = $chat_completion->choices[0]->message->content;
            $tokens_prompt = $chat_completion->usage->promptTokens;
            $tokens_completion = $chat_completion->usage->completionTokens;

        } catch (Exception $e) {

            error_log( $e->getMessage() );
            error_log( $e->getTraceAsString() );

            $status = "error";
            $response = 'An error occurred. Please try again. If it still occurs, please contact [your-name] at [your-email] and provide the following information. Error message: "' . $e->getMessage() . '" + Timestamp: ' . time() . '. Thank you.';

        }

        return [
            'status' => $status,
            'response' => $response,
            'tokens_prompt' => $tokens_prompt,
            'tokens_completion' => $tokens_completion
        ];

    }
}
?>
