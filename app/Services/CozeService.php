<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CozeService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $botId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.coze.base_url'), '/');
        $this->apiKey  = config('services.coze.api_key');
        $this->botId   = config('services.coze.bot_id');

        if (empty($this->apiKey) || empty($this->botId) || str_contains($this->apiKey, 'xxxx') || str_contains($this->botId, 'xxxx')) {
            throw new RuntimeException(
                'Coze no está configurado: define COZE_API_KEY y COZE_BOT_ID con tus valores reales en el .env (y corre "php artisan config:clear" si ya lo hiciste).'
            );
        }
    }

    /**
     * Instancia el cliente HTTP agregando deshabilitación de SSL si está en entorno local.
     */
    protected function buildClient(int $timeout = 10): PendingRequest
    {
        $client = Http::withToken($this->apiKey)->timeout($timeout);

        if (app()->isLocal()) {
            $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Envía un mensaje al bot y realiza polling hasta obtener la respuesta final.
     *
     * @param  string|null $cozeConversationId  null = Coze crea una conversación nueva
     * @return array{conversation_id: string, reply: string}
     */
    public function sendMessage(?string $cozeConversationId, string $userId, string $message): array
    {
        $query = $cozeConversationId ? ['conversation_id' => $cozeConversationId] : [];

        $response = $this->buildClient(20)
            ->post("{$this->baseUrl}/v3/chat?" . http_build_query($query), [
                'bot_id'              => $this->botId,
                'user_id'             => $userId,
                'stream'              => false,
                'auto_save_history'   => true,
                'additional_messages' => [
                    [
                        'role'         => 'user',
                        'content'      => $message,
                        'content_type' => 'text',
                    ],
                ],
            ]);

        if (!$response->successful() || $response->json('code') !== 0) {
            Log::error('Coze: fallo al crear el chat', ['body' => $response->body()]);
            throw new RuntimeException('No se pudo contactar al asistente. Intenta de nuevo.');
        }

        $chatId         = $response->json('data.id');
        $conversationId = $response->json('data.conversation_id');
        $status         = $response->json('data.status');

        $intentos = 0;
        // Incrementamos a 40 intentos con pausas de 0.5s (20 segundos en total)
        while (!in_array($status, ['completed', 'failed', 'requires_action'], true) && $intentos < 40) {
            usleep(500_000); // 0.5 segundos
            $intentos++;

            $retrieveResponse = $this->buildClient(10)->get("{$this->baseUrl}/v3/chat/retrieve", [
                'conversation_id' => $conversationId,
                'chat_id'         => $chatId,
            ]);

            $status = $retrieveResponse->json('data.status');
        }

        if ($status !== 'completed') {
            Log::error('Coze: el chat no terminó a tiempo', [
                'status'  => $status,
                'chat_id' => $chatId,
                'raw'     => $retrieveResponse->json() ?? $retrieveResponse->body(),
            ]);

            throw new RuntimeException('El asistente tardó demasiado en responder. Intenta de nuevo.');
        }

        $mensajes = $this->buildClient(10)
            ->get("{$this->baseUrl}/v3/chat/message/list", [
                'conversation_id' => $conversationId,
                'chat_id'         => $chatId,
            ])->json('data', []);

        $respuesta = collect($mensajes)->firstWhere('type', 'answer');

        return [
            'conversation_id' => $conversationId,
            'reply'            => $respuesta['content'] ?? 'No obtuve una respuesta del asistente.',
        ];
    }
}