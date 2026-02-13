<?php

namespace MockFactory;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private string $apiKey;
    private string $baseUrl;
    private HttpClient $httpClient;

    public VPCResource $vpc;
    public LambdaResource $lambda;
    public DynamoDBResource $dynamodb;
    public SQSResource $sqs;
    public StorageResource $storage;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? getenv('MOCKFACTORY_API_KEY');

        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException(
                'API key required: pass api_key or set MOCKFACTORY_API_KEY env var'
            );
        }

        $this->baseUrl = $config['api_url'] ?? 'https://api.mockfactory.io/v1';

        $this->httpClient = new HttpClient([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'User-Agent' => 'mocklib-php/0.1.0',
            ],
            'timeout' => 30,
        ]);

        // Initialize resource clients
        $this->vpc = new VPCResource($this);
        $this->lambda = new LambdaResource($this);
        $this->dynamodb = new DynamoDBResource($this);
        $this->sqs = new SQSResource($this);
        $this->storage = new StorageResource($this);
    }

    public function request(string $method, string $endpoint, ?array $body = null): array
    {
        try {
            $options = [];
            if ($body !== null) {
                $options['json'] = $body;
            }

            $response = $this->httpClient->request($method, $endpoint, $options);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data ?? [];
        } catch (GuzzleException $e) {
            throw new MockFactoryException(
                'API request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function post(string $endpoint, array $body): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    public function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }
}
