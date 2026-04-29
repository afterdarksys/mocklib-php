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
            'Action'           => 'CreateTable',
            'TableName'        => $params['table_name'],
            'PartitionKey'     => $params['partition_key'],
            'PartitionKeyType' => $params['partition_key_type'] ?? 'S',
            'SortKey'          => $params['sort_key'] ?? null,
            'SortKeyType'      => $params['sort_key_type'] ?? 'S',
        ];

        if (isset($params['billing_mode'])) {
            $body['BillingMode'] = $params['billing_mode'];
        }

        if (isset($params['read_capacity'])) {
            $body['ProvisionedThroughput']['ReadCapacityUnits'] = $params['read_capacity'];
        }

        if (isset($params['write_capacity'])) {
            $body['ProvisionedThroughput']['WriteCapacityUnits'] = $params['write_capacity'];
        }

        $response = $this->client->post('/aws/dynamodb', $body);

        return [
            'id'               => $response['TableId'],
            'table_name'       => $response['TableName'],
            'partition_key'    => $response['PartitionKey'],
            'partition_key_type' => $response['PartitionKeyType'],
            'sort_key'         => $response['SortKey'] ?? null,
            'sort_key_type'    => $response['SortKeyType'] ?? null,
            'state'            => $response['State'],
        ];
    }

    public function putItem(string $tableName, array $item): void
    {
        $this->client->post('/aws/dynamodb', [
            'Action'    => 'PutItem',
            'TableName' => $tableName,
            'Item'      => $item,
        ]);
    }

    public function getItem(string $tableName, array $key): ?array
    {
        $response = $this->client->post('/aws/dynamodb', [
            'Action'    => 'GetItem',
            'TableName' => $tableName,
            'Key'       => $key,
        ]);

        return $response['Item'] ?? null;
    }

    public function updateItem(string $tableName, array $key, array $updateExpression): array
    {
        $body = [
            'Action'                  => 'UpdateItem',
            'TableName'               => $tableName,
            'Key'                     => $key,
            'UpdateExpression'        => $updateExpression['expression'],
            'ExpressionAttributeNames'  => $updateExpression['attribute_names'] ?? [],
            'ExpressionAttributeValues' => $updateExpression['attribute_values'] ?? [],
        ];

        if (isset($updateExpression['condition_expression'])) {
            $body['ConditionExpression'] = $updateExpression['condition_expression'];
        }

        if (isset($updateExpression['return_values'])) {
            $body['ReturnValues'] = $updateExpression['return_values'];
        }

        $response = $this->client->post('/aws/dynamodb', $body);

        return $response['Attributes'] ?? [];
    }

    public function deleteItem(string $tableName, array $key, ?string $conditionExpression = null): void
    {
        $body = [
            'Action'    => 'DeleteItem',
            'TableName' => $tableName,
            'Key'       => $key,
        ];

        if ($conditionExpression !== null) {
            $body['ConditionExpression'] = $conditionExpression;
        }

        $this->client->post('/aws/dynamodb', $body);
    }

    public function query(string $tableName, array $params): array
    {
        $body = [
            'Action'                  => 'Query',
            'TableName'               => $tableName,
            'KeyConditionExpression'  => $params['key_condition_expression'],
            'ExpressionAttributeNames'  => $params['attribute_names'] ?? [],
            'ExpressionAttributeValues' => $params['attribute_values'] ?? [],
        ];

        if (isset($params['filter_expression'])) {
            $body['FilterExpression'] = $params['filter_expression'];
        }

        if (isset($params['index_name'])) {
            $body['IndexName'] = $params['index_name'];
        }

        if (isset($params['limit'])) {
            $body['Limit'] = $params['limit'];
        }

        if (isset($params['scan_index_forward'])) {
            $body['ScanIndexForward'] = $params['scan_index_forward'];
        }

        if (isset($params['exclusive_start_key'])) {
            $body['ExclusiveStartKey'] = $params['exclusive_start_key'];
        }

        $response = $this->client->post('/aws/dynamodb', $body);

        return [
            'items'             => $response['Items'] ?? [],
            'count'             => $response['Count'] ?? 0,
            'scanned_count'     => $response['ScannedCount'] ?? 0,
            'last_evaluated_key'=> $response['LastEvaluatedKey'] ?? null,
        ];
    }

    public function scan(string $tableName, array $params = []): array
    {
        $body = [
            'Action'    => 'Scan',
            'TableName' => $tableName,
        ];

        if (isset($params['filter_expression'])) {
            $body['FilterExpression'] = $params['filter_expression'];
        }

        if (isset($params['attribute_names'])) {
            $body['ExpressionAttributeNames'] = $params['attribute_names'];
        }

        if (isset($params['attribute_values'])) {
            $body['ExpressionAttributeValues'] = $params['attribute_values'];
        }

        if (isset($params['limit'])) {
            $body['Limit'] = $params['limit'];
        }

        if (isset($params['exclusive_start_key'])) {
            $body['ExclusiveStartKey'] = $params['exclusive_start_key'];
        }

        if (isset($params['index_name'])) {
            $body['IndexName'] = $params['index_name'];
        }

        $response = $this->client->post('/aws/dynamodb', $body);

        return [
            'items'             => $response['Items'] ?? [],
            'count'             => $response['Count'] ?? 0,
            'scanned_count'     => $response['ScannedCount'] ?? 0,
            'last_evaluated_key'=> $response['LastEvaluatedKey'] ?? null,
        ];
    }

    public function deleteTable(string $tableName): void
    {
        $this->client->post('/aws/dynamodb', [
            'Action'    => 'DeleteTable',
            'TableName' => $tableName,
        ]);
    }
}
