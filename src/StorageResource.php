<?php

namespace MockFactory;

class StorageResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createBucket(array $params): array
    {
        $body = [
            'Action' => 'CreateBucket',
            'BucketName' => $params['bucket_name'],
            'Provider' => $params['provider'] ?? 's3',
            'Region' => $params['region'] ?? 'us-east-1',
        ];

        $response = $this->client->post('/storage/bucket', $body);

        return [
            'id' => $response['BucketId'],
            'bucket_name' => $response['BucketName'],
            'provider' => $response['Provider'],
            'region' => $response['Region'],
        ];
    }

    public function uploadObject(string $bucketName, string $key, string $data): void
    {
        $this->client->post('/storage/object', [
            'Action' => 'PutObject',
            'BucketName' => $bucketName,
            'Key' => $key,
            'Data' => base64_encode($data),
        ]);
    }

    public function deleteBucket(string $bucketName): void
    {
        $this->client->post('/storage/bucket', [
            'Action' => 'DeleteBucket',
            'BucketName' => $bucketName,
        ]);
    }
}
