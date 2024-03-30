<?php

namespace SalesRender\Plugin\Instance\Chat\Components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use SalesRender\Plugin\Core\Chat\Components\MessageStatusSender\MessageStatusSender;

class MainSmsApi
{
    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'timeout' => 2,
        ]);
    }

    public function sendEmail(string $sender, string $name, string $subject, string $recipient, string $text): array
    {
        $messageData = [
            "from_email" => $sender,
            "from_name" => $name,
            "subject" => $subject,
            "to" => $recipient,
            "text" => $text
        ];

        $url = 'https://api.mainsms.ru/v1/email/messages';

        try {
            return $this->createAuthenticatedRequest($url, $messageData);
        } catch (\Exception $e) {
            throw new \Exception('Email не может быть отправлен ' . $e->getMessage());
        }
    }

    public function getStatusEmailbyId(string $id): string
    {
        $url = 'https://api.mainsms.ru/v1/email/messages/' . $id;

        try {
            $response = $this->createAuthenticatedRequest($url, [], 'GET');
            return $response['status'];
        } catch (\Exception $e) {
            throw new \Exception('Не удалось получить статус: ' . $e->getMessage());
        }
    }

    private function createAuthenticatedRequest(string $url, array $data, string $method = 'POST'): array
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ]
        ];

        if ($method === 'POST') {
            $options['json'] = $data;
        } else {
            $options['query'] = $data;
        }

        try {
            $response = $this->client->request($method, $url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw $e;
        }
    }

    public function mapStatus(string $mainSmsStatus): ?string
    {
        $statusMap = [
            'queued' => MessageStatusSender::SENT,              //Принято в очередь
            'sent' => MessageStatusSender::SENT,                //Отправлено
            'delivered' => MessageStatusSender::DELIVERED,      //Доставлено
            'skipped' => MessageStatusSender::SENT,             //Не отправлено. Получатель отписался или находится в
                                                                //списке проблемных получателей
            'hard_bounced' => MessageStatusSender::ERROR,       //Сообщение не может быть доставлено
            'soft_bounced' => MessageStatusSender::SENT,        //Не доставлено. Временно отклонено принимающей стороной
        ];

        return $statusMap[$mainSmsStatus];
    }
}
