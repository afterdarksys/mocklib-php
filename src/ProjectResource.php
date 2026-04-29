<?php

namespace MockFactory;

class ProjectResource
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new project
     */
    public function create(array $params): array
    {
        return $this->client->post('/mock/project', [
            'name' => $params['name'],
            'environment' => $params['environment'] ?? 'development',
            'organization' => $params['organization'] ?? null,
            'description' => $params['description'] ?? null,
        ]);
    }

    /**
     * List all projects
     */
    public function list(?string $organization = null, ?string $environment = null): array
    {
        $params = [];
        if ($organization !== null) {
            $params['organization'] = $organization;
        }
        if ($environment !== null) {
            $params['environment'] = $environment;
        }

        $response = $this->client->get('/mock/project', $params);
        return $response['projects'] ?? [];
    }

    /**
     * Get project by ID
     */
    public function get(string $projectId): array
    {
        return $this->client->get("/mock/project/{$projectId}");
    }

    /**
     * Bind a resource to a project
     */
    public function bindResource(string $projectId, string $resourceType, string $resourceId): bool
    {
        $this->client->post("/mock/project/{$projectId}/resources", [
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
        ]);
        return true;
    }

    /**
     * Unbind a resource from a project
     */
    public function unbindResource(string $projectId, string $resourceType, string $resourceId): bool
    {
        $this->client->delete("/mock/project/{$projectId}/resources/{$resourceType}/{$resourceId}");
        return true;
    }

    /**
     * Delete a project
     */
    public function delete(string $projectId, bool $deleteResources = false): bool
    {
        $this->client->delete("/mock/project/{$projectId}?delete_resources=" . ($deleteResources ? 'true' : 'false'));
        return true;
    }
}
