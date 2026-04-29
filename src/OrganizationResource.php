<?php

namespace MockFactory;

class OrganizationResource
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new organization
     */
    public function create(array $params): array
    {
        return $this->client->post('/mock/organization', [
            'name' => $params['name'],
            'plan' => $params['plan'] ?? 'free',
            'description' => $params['description'] ?? null,
            'owner' => $params['owner'] ?? null,
        ]);
    }

    /**
     * List all organizations
     */
    public function list(?string $plan = null): array
    {
        $params = [];
        if ($plan !== null) {
            $params['plan'] = $plan;
        }

        $response = $this->client->get('/mock/organization', $params);
        return $response['organizations'] ?? [];
    }

    /**
     * Get organization by name
     */
    public function get(string $name): array
    {
        return $this->client->get("/mock/organization/{$name}");
    }

    /**
     * Delete an organization
     */
    public function delete(string $name): bool
    {
        $this->client->delete("/mock/organization/{$name}");
        return true;
    }

    /**
     * Add a user to an organization
     */
    public function addUser(string $orgName, string $username, string $role = 'member'): bool
    {
        $this->client->post("/mock/organization/{$orgName}/users", [
            'username' => $username,
            'role' => $role,
        ]);
        return true;
    }

    /**
     * Remove a user from an organization
     */
    public function removeUser(string $orgName, string $username): bool
    {
        $this->client->delete("/mock/organization/{$orgName}/users/{$username}");
        return true;
    }
}
