<?php

namespace MockFactory;

class CloudResource
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new cloud environment
     */
    public function create(array $params): array
    {
        return $this->client->post('/mock/cloud', [
            'name' => $params['name'],
            'provider' => $params['provider'] ?? 'aws',
            'region' => $params['region'] ?? 'us-east-1',
            'organization' => $params['organization'] ?? null,
        ]);
    }

    /**
     * List all cloud environments
     */
    public function list(?string $provider = null, ?string $organization = null): array
    {
        $params = [];
        if ($provider !== null) {
            $params['provider'] = $provider;
        }
        if ($organization !== null) {
            $params['organization'] = $organization;
        }

        $response = $this->client->get('/mock/cloud', $params);
        return $response['clouds'] ?? [];
    }

    /**
     * Get cloud environment by name
     */
    public function get(string $name): array
    {
        return $this->client->get("/mock/cloud/{$name}");
    }

    /**
     * Delete a cloud environment
     */
    public function delete(string $name): bool
    {
        $this->client->delete("/mock/cloud/{$name}");
        return true;
    }
}
