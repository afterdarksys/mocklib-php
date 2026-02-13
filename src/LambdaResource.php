<?php

namespace MockFactory;

class LambdaResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function create(array $params): array
    {
        $body = [
            'Action' => 'CreateFunction',
            'FunctionName' => $params['function_name'],
            'Runtime' => $params['runtime'],
            'Handler' => $params['handler'] ?? 'index.handler',
            'MemorySize' => $params['memory_mb'] ?? 128,
            'Timeout' => $params['timeout'] ?? 30,
            'Environment' => [
                'Variables' => $params['environment_variables'] ?? [],
            ],
        ];

        $response = $this->client->post('/aws/lambda', $body);

        return [
            'id' => $response['FunctionId'],
            'function_name' => $response['FunctionName'],
            'runtime' => $response['Runtime'],
            'memory_mb' => $response['MemorySize'],
            'timeout' => $response['Timeout'],
            'state' => $response['State'],
            'arn' => $response['FunctionArn'] ?? null,
        ];
    }

    public function invoke(string $functionName, array $payload = []): array
    {
        $response = $this->client->post('/aws/lambda', [
            'Action' => 'Invoke',
            'FunctionName' => $functionName,
            'Payload' => $payload,
        ]);

        return $response['Payload'] ?? [];
    }

    public function delete(string $functionName): void
    {
        $this->client->post('/aws/lambda', [
            'Action' => 'DeleteFunction',
            'FunctionName' => $functionName,
        ]);
    }
}
