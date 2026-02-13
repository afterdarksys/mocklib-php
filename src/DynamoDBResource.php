<?php

namespace MockFactory;

class DynamoDBResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createTable(array $params): array
    {
        $body = [
            'Action' => 'CreateTable',
            'TableName' => $params['table_name'],
            'PartitionKey' => $params['partition_key'],
            'PartitionKeyType' => $params['partition_key_type'] ?? 'S',
            'SortKey' => $params['sort_key'] ?? null,
            'SortKeyType' => $params['sort_key_type'] ?? 'S',
        ];

        $response = $this->client->post('/aws/dynamodb', $body);

        return [
            'id' => $response['TableId'],
            'table_name' => $response['TableName'],
            'partition_key' => $response['PartitionKey'],
            'partition_key_type' => $response['PartitionKeyType'],
            'sort_key' => $response['SortKey'] ?? null,
            'sort_key_type' => $response['SortKeyType'] ?? null,
            'state' => $response['State'],
        ];
    }

    public function putItem(string $tableName, array $item): void
    {
        $this->client->post('/aws/dynamodb', [
            'Action' => 'PutItem',
            'TableName' => $tableName,
            'Item' => $item,
        ]);
    }

    public function getItem(string $tableName, array $key): ?array
    {
        $response = $this->client->post('/aws/dynamodb', [
            'Action' => 'GetItem',
            'TableName' => $tableName,
            'Key' => $key,
        ]);

        return $response['Item'] ?? null;
    }

    public function deleteTable(string $tableName): void
    {
        $this->client->post('/aws/dynamodb', [
            'Action' => 'DeleteTable',
            'TableName' => $tableName,
        ]);
    }
}
