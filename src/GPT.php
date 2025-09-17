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

        $config = Configuration::getInstance();
        
        try {
            $chat_completion = $this->openai_client->chat()->create([
                'model' => $config->getGptModel(),
                'n' => 1,
                'max_tokens' => $config->getGptMaxTokens(),
                'messages' => $chat_messages,
            ]);

            $response = $chat_completion->choices[0]->message->content;
            $tokens_prompt = $chat_completion->usage->promptTokens;
            $tokens_completion = $chat_completion->usage->completionTokens;

        } catch (Exception $e) {

            error_log( $e->getMessage() );
            error_log( $e->getTraceAsString() );

            $status = "error";
            $contact_name = $config->placeholderRaw('contact.name');
            $contact_email = $config->placeholderRaw('contact.email');
            $response = 'An error occurred. Please try again. If it still occurs, please contact ' . $contact_name . ' at ' . $contact_email . ' and provide the following information. Error message: "' . $e->getMessage() . '" + Timestamp: ' . time() . '. Thank you.';

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
