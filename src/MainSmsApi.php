<?php

namespace SalesRender\Plugin\Instance\Chat\Components;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use SalesRender\Plugin\Core\Chat\Components\MessageStatusSender\MessageStatusSender;

class MainSmsApi
{
    private Client $client;
    private array $authHeaders;

    public function __construct(string $apiToken)
    {
        $authHeaders = [
            'Authorization' => 'Bearer' .  $apiToken,
            'Content-Type' => 'application/json'
            ];
        $this->authHeaders = $authHeaders;
        $this->client = new Client(
            ['http_errors' => false]
        );
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
        $options = ['headers' => $this->authHeaders, 'body' => json_encode($messageData),  'timeout' => 2];

        $url = 'https://api.mainsms.ru/v1/email/messages';

        $response = $this->client->post($url, $options);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getStatusEmailbyId(string $id): string
    {
        $url = 'https://api.mainsms.ru/v1/email/messages/' . $id;

        $response = $this->client->get($url, ['headers' => $this->authHeaders]);

        $status = json_decode($response->getBody()->getContents(), true)['status'];

        return $status;
    }
}

    /*public function mapStatus(string $mainSmsStatus): ?string
    {
        $statusMap = [
            'queued' => MessageStatusSender::SENT,              //Принято в очередь
            'sent' => MessageStatusSender::SENT,                //Отправлено
            'delivered' => MessageStatusSender::DELIVERED,      //Доставлено
            'skipped' => MessageStatusSender::SENT,             //Не отправлено. Получатель отписался или находится в списке проблемных получателей
            'hard_bounced' => MessageStatusSender::ERROR,       //Сообщение не может быть доставлено
            'soft_bounced' => MessageStatusSender::SENT,        //Не доставлено. Временно отклонено принимающей стороной
        ];

        return $statusMap[$mainSmsStatus];
    }

    private function isSuccessResponse(ResponseInterface $response): bool
    {
        $strCode = (string) $response->getStatusCode();
        # в случае успеха код ошибки может быть 2**
        return preg_match('/^2\d{2}$/', $strCode);
    }
} */
