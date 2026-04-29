<?php

namespace MockFactory;

class GCPComputeResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========================================================================
    // Compute — Instances
    // ========================================================================

    public function createInstance(string $project, string $zone, array $params): array
    {
        $body = [
            'name'        => $params['name'],
            'machineType' => $params['machine_type'] ?? 'n1-standard-1',
            'disks'       => $params['disks'] ?? [],
            'networkInterfaces' => $params['network_interfaces'] ?? [],
        ];

        if (isset($params['metadata'])) {
            $body['metadata'] = $params['metadata'];
        }

        if (isset($params['labels'])) {
            $body['labels'] = $params['labels'];
        }

        if (isset($params['tags'])) {
            $body['tags'] = $params['tags'];
        }

        $response = $this->client->post(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/instances",
            $body
        );

        return $this->normalizeInstance($response);
    }

    public function listInstances(string $project, string $zone, array $params = []): array
    {
        $query = [];
        if (isset($params['filter'])) {
            $query['filter'] = $params['filter'];
        }
        if (isset($params['max_results'])) {
            $query['maxResults'] = $params['max_results'];
        }
        if (isset($params['page_token'])) {
            $query['pageToken'] = $params['page_token'];
        }

        $response = $this->client->get(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/instances",
            $query
        );

        $instances = [];
        foreach ($response['items'] ?? [] as $inst) {
            $instances[] = $this->normalizeInstance($inst);
        }

        return $instances;
    }

    public function getInstance(string $project, string $zone, string $instance): array
    {
        $response = $this->client->get(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/instances/{$instance}"
        );

        return $this->normalizeInstance($response);
    }

    public function startInstance(string $project, string $zone, string $instance): array
    {
        return $this->client->post(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/instances/{$instance}/start",
            []
        );
    }

    public function stopInstance(string $project, string $zone, string $instance): array
    {
        return $this->client->post(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/instances/{$instance}/stop",
            []
        );
    }

    public function deleteInstance(string $project, string $zone, string $instance): array
    {
        return $this->client->delete(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/instances/{$instance}"
        );
    }

    // ========================================================================
    // Networking — Networks (VPCs)
    // ========================================================================

    public function createNetwork(string $project, array $params): array
    {
        $body = [
            'name'                  => $params['name'],
            'autoCreateSubnetworks' => $params['auto_create_subnetworks'] ?? false,
        ];

        if (isset($params['description'])) {
            $body['description'] = $params['description'];
        }

        if (isset($params['routing_config'])) {
            $body['routingConfig'] = $params['routing_config'];
        }

        return $this->client->post(
            "/gcp/compute/v1/projects/{$project}/global/networks",
            $body
        );
    }

    public function listNetworks(string $project, array $params = []): array
    {
        $query = [];
        if (isset($params['filter'])) {
            $query['filter'] = $params['filter'];
        }

        $response = $this->client->get(
            "/gcp/compute/v1/projects/{$project}/global/networks",
            $query
        );

        return $response['items'] ?? [];
    }

    public function deleteNetwork(string $project, string $network): array
    {
        return $this->client->delete(
            "/gcp/compute/v1/projects/{$project}/global/networks/{$network}"
        );
    }

    // ========================================================================
    // Networking — Firewalls
    // ========================================================================

    public function createFirewall(string $project, array $params): array
    {
        $body = [
            'name'     => $params['name'],
            'network'  => $params['network'],
            'allowed'  => $params['allowed'] ?? [],
        ];

        if (isset($params['description'])) {
            $body['description'] = $params['description'];
        }

        if (isset($params['source_ranges'])) {
            $body['sourceRanges'] = $params['source_ranges'];
        }

        if (isset($params['target_tags'])) {
            $body['targetTags'] = $params['target_tags'];
        }

        if (isset($params['direction'])) {
            $body['direction'] = $params['direction'];
        }

        if (isset($params['priority'])) {
            $body['priority'] = $params['priority'];
        }

        return $this->client->post(
            "/gcp/compute/v1/projects/{$project}/global/firewalls",
            $body
        );
    }

    public function listFirewalls(string $project, array $params = []): array
    {
        $query = [];
        if (isset($params['filter'])) {
            $query['filter'] = $params['filter'];
        }

        $response = $this->client->get(
            "/gcp/compute/v1/projects/{$project}/global/firewalls",
            $query
        );

        return $response['items'] ?? [];
    }

    public function deleteFirewall(string $project, string $firewall): array
    {
        return $this->client->delete(
            "/gcp/compute/v1/projects/{$project}/global/firewalls/{$firewall}"
        );
    }

    // ========================================================================
    // Block Storage — Disks
    // ========================================================================

    public function createDisk(string $project, string $zone, array $params): array
    {
        $body = [
            'name'   => $params['name'],
            'sizeGb' => (string)($params['size_gb'] ?? 10),
            'type'   => $params['type'] ?? "zones/{$zone}/diskTypes/pd-standard",
        ];

        if (isset($params['source_image'])) {
            $body['sourceImage'] = $params['source_image'];
        }

        if (isset($params['description'])) {
            $body['description'] = $params['description'];
        }

        if (isset($params['labels'])) {
            $body['labels'] = $params['labels'];
        }

        return $this->client->post(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/disks",
            $body
        );
    }

    public function listDisks(string $project, string $zone, array $params = []): array
    {
        $query = [];
        if (isset($params['filter'])) {
            $query['filter'] = $params['filter'];
        }

        $response = $this->client->get(
            "/gcp/compute/v1/projects/{$project}/zones/{$zone}/disks",
            $query
        );

        return $response['items'] ?? [];
    }

    // ========================================================================
    // Private helpers
    // ========================================================================

    private function normalizeInstance(array $inst): array
    {
        return [
            'id'          => $inst['id'] ?? null,
            'name'        => $inst['name'] ?? null,
            'zone'        => $inst['zone'] ?? null,
            'status'      => $inst['status'] ?? null,
            'machine_type'=> $inst['machineType'] ?? null,
            'self_link'   => $inst['selfLink'] ?? null,
            'creation_timestamp' => $inst['creationTimestamp'] ?? null,
            'network_interfaces' => $inst['networkInterfaces'] ?? [],
            'disks'       => $inst['disks'] ?? [],
            'labels'      => $inst['labels'] ?? [],
        ];
    }
}
