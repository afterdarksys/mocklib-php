<?php

namespace MockFactory;

class DomainResource
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new domain
     */
    public function create(array $params): array
    {
        return $this->client->post('/mock/domain', [
            'domain' => $params['domain'],
            'organization' => $params['organization'] ?? null,
            'verified' => $params['verified'] ?? false,
            'dns_records' => $params['dns_records'] ?? [],
        ]);
    }

    /**
     * List all domains
     */
    public function list(?string $organization = null, ?bool $verified = null): array
    {
        $params = [];
        if ($organization !== null) {
            $params['organization'] = $organization;
        }
        if ($verified !== null) {
            $params['verified'] = $verified;
        }

        $response = $this->client->get('/mock/domain', $params);
        return $response['domains'] ?? [];
    }

    /**
     * Get domain by name
     */
    public function get(string $domain): array
    {
        return $this->client->get("/mock/domain/{$domain}");
    }

    /**
     * Verify a domain
     */
    public function verify(string $domain): bool
    {
        $this->client->post("/mock/domain/{$domain}/verify");
        return true;
    }

    /**
     * Delete a domain
     */
    public function delete(string $domain): bool
    {
        $this->client->delete("/mock/domain/{$domain}");
        return true;
    }
}
