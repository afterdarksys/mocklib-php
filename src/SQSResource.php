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
            'Action'                 => 'CreateQueue',
            'QueueName'              => $params['queue_name'],
            'VisibilityTimeout'      => $params['visibility_timeout'] ?? 30,
            'MessageRetentionPeriod' => $params['message_retention'] ?? 345600,
        ];

        if (isset($params['delay_seconds'])) {
            $body['DelaySeconds'] = $params['delay_seconds'];
        }

        if (isset($params['max_message_size'])) {
            $body['MaximumMessageSize'] = $params['max_message_size'];
        }

        if (isset($params['fifo'])) {
            $body['FifoQueue'] = $params['fifo'];
        }

        $response = $this->client->post('/aws/sqs', $body);

        return [
            'id'                 => $response['QueueId'],
            'queue_name'         => $response['QueueName'],
            'queue_url'          => $response['QueueUrl'],
            'visibility_timeout' => $params['visibility_timeout'] ?? 30,
        ];
    }

    public function sendMessage(string $queueUrl, string $messageBody, array $params = []): string
    {
        $body = [
            'Action'      => 'SendMessage',
            'QueueUrl'    => $queueUrl,
            'MessageBody' => $messageBody,
        ];

        if (isset($params['delay_seconds'])) {
            $body['DelaySeconds'] = $params['delay_seconds'];
        }

        if (isset($params['message_attributes'])) {
            $body['MessageAttributes'] = $params['message_attributes'];
        }

        if (isset($params['message_group_id'])) {
            $body['MessageGroupId'] = $params['message_group_id'];
        }

        if (isset($params['message_deduplication_id'])) {
            $body['MessageDeduplicationId'] = $params['message_deduplication_id'];
        }

        $response = $this->client->post('/aws/sqs', $body);

        return $response['MessageId'];
    }

    public function receiveMessages(string $queueUrl, int $maxMessages = 1, array $params = []): array
    {
        $body = [
            'Action'              => 'ReceiveMessage',
            'QueueUrl'            => $queueUrl,
            'MaxNumberOfMessages' => $maxMessages,
        ];

        if (isset($params['visibility_timeout'])) {
            $body['VisibilityTimeout'] = $params['visibility_timeout'];
        }

        if (isset($params['wait_time_seconds'])) {
            $body['WaitTimeSeconds'] = $params['wait_time_seconds'];
        }

        if (isset($params['message_attribute_names'])) {
            $body['MessageAttributeNames'] = $params['message_attribute_names'];
        }

        $response = $this->client->post('/aws/sqs', $body);

        return $response['Messages'] ?? [];
    }

    public function deleteMessage(string $queueUrl, string $receiptHandle): void
    {
        $this->client->post('/aws/sqs', [
            'Action'        => 'DeleteMessage',
            'QueueUrl'      => $queueUrl,
            'ReceiptHandle' => $receiptHandle,
        ]);
    }

    public function deleteQueue(string $queueUrl): void
    {
        $this->client->post('/aws/sqs', [
            'Action'   => 'DeleteQueue',
            'QueueUrl' => $queueUrl,
        ]);
    }

    public function getQueueAttributes(string $queueUrl, array $attributeNames = ['All']): array
    {
        $response = $this->client->post('/aws/sqs', [
            'Action'         => 'GetQueueAttributes',
            'QueueUrl'       => $queueUrl,
            'AttributeNames' => $attributeNames,
        ]);

        return $response['Attributes'] ?? [];
    }

    public function purgeQueue(string $queueUrl): void
    {
        $this->client->post('/aws/sqs', [
            'Action'   => 'PurgeQueue',
            'QueueUrl' => $queueUrl,
        ]);
    }
}
