<?php

namespace MockFactory;

class SNSResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createTopic(array $params): array
    {
        $body = [
            'Action' => 'CreateTopic',
            'Name'   => $params['name'],
        ];

        if (isset($params['attributes'])) {
            $body['Attributes'] = $params['attributes'];
        }

        if (isset($params['tags'])) {
            $body['Tags'] = $params['tags'];
        }

        $response = $this->client->post('/sns/', $body);

        return [
            'topic_arn' => $response['TopicArn'],
        ];
    }

    public function listTopics(?string $nextToken = null): array
    {
        $body = ['Action' => 'ListTopics'];

        if ($nextToken !== null) {
            $body['NextToken'] = $nextToken;
        }

        $response = $this->client->post('/sns/', $body);

        $topics = [];
        foreach ($response['Topics'] ?? [] as $topic) {
            $topics[] = ['topic_arn' => $topic['TopicArn']];
        }

        return [
            'topics'     => $topics,
            'next_token' => $response['NextToken'] ?? null,
        ];
    }

    public function publish(array $params): array
    {
        $body = [
            'Action'  => 'Publish',
            'Message' => $params['message'],
        ];

        if (isset($params['topic_arn'])) {
            $body['TopicArn'] = $params['topic_arn'];
        }

        if (isset($params['target_arn'])) {
            $body['TargetArn'] = $params['target_arn'];
        }

        if (isset($params['phone_number'])) {
            $body['PhoneNumber'] = $params['phone_number'];
        }

        if (isset($params['subject'])) {
            $body['Subject'] = $params['subject'];
        }

        if (isset($params['message_attributes'])) {
            $body['MessageAttributes'] = $params['message_attributes'];
        }

        if (isset($params['message_structure'])) {
            $body['MessageStructure'] = $params['message_structure'];
        }

        $response = $this->client->post('/sns/', $body);

        return [
            'message_id' => $response['MessageId'],
        ];
    }

    public function subscribe(array $params): array
    {
        $body = [
            'Action'   => 'Subscribe',
            'TopicArn' => $params['topic_arn'],
            'Protocol' => $params['protocol'],
            'Endpoint' => $params['endpoint'],
        ];

        if (isset($params['attributes'])) {
            $body['Attributes'] = $params['attributes'];
        }

        $response = $this->client->post('/sns/', $body);

        return [
            'subscription_arn' => $response['SubscriptionArn'],
        ];
    }
}
