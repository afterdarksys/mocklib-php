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
            'FunctionName' => $params['function_name'],
            'Runtime'      => $params['runtime'],
            'Handler'      => $params['handler'] ?? 'index.handler',
            'MemorySize'   => $params['memory_mb'] ?? 128,
            'Timeout'      => $params['timeout'] ?? 30,
            'Environment'  => [
                'Variables' => $params['environment_variables'] ?? [],
            ],
        ];

        if (isset($params['role'])) {
            $body['Role'] = $params['role'];
        }

        if (isset($params['description'])) {
            $body['Description'] = $params['description'];
        }

        if (isset($params['tags'])) {
            $body['Tags'] = $params['tags'];
        }

        $response = $this->client->post('/lambda/2015-03-31/functions', $body);

        return $this->normalizeFunction($response);
    }

    public function list(array $params = []): array
    {
        $query = [];
        if (isset($params['marker'])) {
            $query['Marker'] = $params['marker'];
        }
        if (isset($params['max_items'])) {
            $query['MaxItems'] = $params['max_items'];
        }

        $response = $this->client->get('/lambda/2015-03-31/functions', $query);

        $functions = [];
        foreach ($response['Functions'] ?? [] as $fn) {
            $functions[] = $this->normalizeFunction($fn);
        }

        return $functions;
    }

    public function get(string $functionName): array
    {
        $response = $this->client->get("/lambda/2015-03-31/functions/{$functionName}");
        return $this->normalizeFunction($response);
    }

    public function invoke(string $functionName, array $payload = []): array
    {
        $response = $this->client->post(
            "/lambda/2015-03-31/functions/{$functionName}/invocations",
            $payload
        );

        return $response['Payload'] ?? $response ?? [];
    }

    public function delete(string $functionName): void
    {
        $this->client->delete("/lambda/2015-03-31/functions/{$functionName}");
    }

    private function normalizeFunction(array $fn): array
    {
        return [
            'id'            => $fn['FunctionId'] ?? null,
            'function_name' => $fn['FunctionName'] ?? null,
            'runtime'       => $fn['Runtime'] ?? null,
            'memory_mb'     => $fn['MemorySize'] ?? null,
            'timeout'       => $fn['Timeout'] ?? null,
            'state'         => $fn['State'] ?? null,
            'arn'           => $fn['FunctionArn'] ?? null,
            'handler'       => $fn['Handler'] ?? null,
            'description'   => $fn['Description'] ?? null,
            'last_modified' => $fn['LastModified'] ?? null,
        ];
    }
}
