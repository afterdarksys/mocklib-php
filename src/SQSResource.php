<?php

namespace MockFactory;

class SQSResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createQueue(array $params): array
    {
        $body = [
            'Action' => 'CreateQueue',
            'QueueName' => $params['queue_name'],
            'VisibilityTimeout' => $params['visibility_timeout'] ?? 30,
            'MessageRetentionPeriod' => $params['message_retention'] ?? 345600,
        ];

        $response = $this->client->post('/aws/sqs', $body);

        return [
            'id' => $response['QueueId'],
            'queue_name' => $response['QueueName'],
            'queue_url' => $response['QueueUrl'],
            'visibility_timeout' => $params['visibility_timeout'] ?? 30,
        ];
    }

    public function sendMessage(string $queueUrl, string $messageBody): string
    {
        $response = $this->client->post('/aws/sqs', [
            'Action' => 'SendMessage',
            'QueueUrl' => $queueUrl,
            'MessageBody' => $messageBody,
        ]);

        return $response['MessageId'];
    }

    public function receiveMessages(string $queueUrl, int $maxMessages = 1): array
    {
        $response = $this->client->post('/aws/sqs', [
            'Action' => 'ReceiveMessage',
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => $maxMessages,
        ]);

        return $response['Messages'] ?? [];
    }

    public function deleteQueue(string $queueUrl): void
    {
        $this->client->post('/aws/sqs', [
            'Action' => 'DeleteQueue',
            'QueueUrl' => $queueUrl,
        ]);
    }
}
