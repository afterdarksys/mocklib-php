<?php

namespace MockFactory;

class OCIResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========================================================================
    // Object Storage — Namespace
    // ========================================================================

    public function getNamespace(): string
    {
        $response = $this->client->get('/n');
        return $response['namespace'] ?? $response['value'] ?? '';
    }

    // ========================================================================
    // Object Storage — Buckets
    // ========================================================================

    public function listBuckets(string $namespace, ?string $compartmentId = null): array
    {
        $params = [];
        if ($compartmentId !== null) {
            $params['compartmentId'] = $compartmentId;
        }

        $response = $this->client->get("/n/{$namespace}/b", $params);
        return $response['items'] ?? $response ?? [];
    }

    public function createBucket(string $namespace, array $params): array
    {
        $body = [
            'name'          => $params['name'],
            'compartmentId' => $params['compartment_id'],
        ];

        if (isset($params['public_access_type'])) {
            $body['publicAccessType'] = $params['public_access_type'];
        }

        if (isset($params['storage_tier'])) {
            $body['storageTier'] = $params['storage_tier'];
        }

        if (isset($params['freeform_tags'])) {
            $body['freeformTags'] = $params['freeform_tags'];
        }

        return $this->client->post("/n/{$namespace}/b", $body);
    }

    public function deleteBucket(string $namespace, string $bucketName): void
    {
        $this->client->delete("/n/{$namespace}/b/{$bucketName}");
    }

    // ========================================================================
    // Object Storage — Objects
    // ========================================================================

    public function putObject(string $namespace, string $bucketName, string $objectName, string $content, array $params = []): array
    {
        $body = [
            'objectName'   => $objectName,
            'content'      => base64_encode($content),
            'contentType'  => $params['content_type'] ?? 'application/octet-stream',
        ];

        if (isset($params['metadata'])) {
            $body['opc-meta'] = $params['metadata'];
        }

        return $this->client->request(
            'PUT',
            "/n/{$namespace}/b/{$bucketName}/o/{$objectName}",
            $body
        );
    }

    public function getObject(string $namespace, string $bucketName, string $objectName): array
    {
        return $this->client->get("/n/{$namespace}/b/{$bucketName}/o/{$objectName}");
    }

    public function deleteObject(string $namespace, string $bucketName, string $objectName): void
    {
        $this->client->delete("/n/{$namespace}/b/{$bucketName}/o/{$objectName}");
    }

    // ========================================================================
    // Compute — Instances
    // ========================================================================

    public function createInstance(array $params): array
    {
        $body = [
            'compartmentId'      => $params['compartment_id'],
            'availabilityDomain' => $params['availability_domain'],
            'shape'              => $params['shape'],
            'displayName'        => $params['display_name'] ?? null,
            'imageId'            => $params['image_id'] ?? null,
            'subnetId'           => $params['subnet_id'] ?? null,
        ];

        if (isset($params['metadata'])) {
            $body['metadata'] = $params['metadata'];
        }

        if (isset($params['freeform_tags'])) {
            $body['freeformTags'] = $params['freeform_tags'];
        }

        $response = $this->client->post('/20160918/instances', $body);
        return $this->normalizeInstance($response);
    }

    public function listInstances(array $params = []): array
    {
        $query = [];
        if (isset($params['compartment_id'])) {
            $query['compartmentId'] = $params['compartment_id'];
        }
        if (isset($params['availability_domain'])) {
            $query['availabilityDomain'] = $params['availability_domain'];
        }
        if (isset($params['lifecycle_state'])) {
            $query['lifecycleState'] = $params['lifecycle_state'];
        }

        $response = $this->client->get('/20160918/instances', $query);

        $instances = [];
        foreach ($response['items'] ?? $response ?? [] as $inst) {
            $instances[] = $this->normalizeInstance($inst);
        }

        return $instances;
    }

    public function stopInstance(string $instanceId): array
    {
        $response = $this->client->post("/20160918/instances/{$instanceId}/actions/stop", []);
        return $this->normalizeInstance($response);
    }

    public function startInstance(string $instanceId): array
    {
        $response = $this->client->post("/20160918/instances/{$instanceId}/actions/start", []);
        return $this->normalizeInstance($response);
    }

    public function deleteInstance(string $instanceId): void
    {
        $this->client->delete("/20160918/instances/{$instanceId}");
    }

    // ========================================================================
    // Networking — VCNs
    // ========================================================================

    public function createVCN(array $params): array
    {
        $body = [
            'compartmentId' => $params['compartment_id'],
            'cidrBlock'     => $params['cidr_block'],
            'displayName'   => $params['display_name'] ?? null,
        ];

        if (isset($params['dns_label'])) {
            $body['dnsLabel'] = $params['dns_label'];
        }

        if (isset($params['freeform_tags'])) {
            $body['freeformTags'] = $params['freeform_tags'];
        }

        return $this->client->post('/20160918/vcns', $body);
    }

    public function listVCNs(array $params = []): array
    {
        $query = [];
        if (isset($params['compartment_id'])) {
            $query['compartmentId'] = $params['compartment_id'];
        }
        if (isset($params['lifecycle_state'])) {
            $query['lifecycleState'] = $params['lifecycle_state'];
        }

        $response = $this->client->get('/20160918/vcns', $query);
        return $response['items'] ?? $response ?? [];
    }

    public function getVCN(string $vcnId): array
    {
        return $this->client->get("/20160918/vcns/{$vcnId}");
    }

    public function deleteVCN(string $vcnId): void
    {
        $this->client->delete("/20160918/vcns/{$vcnId}");
    }

    // ========================================================================
    // Block Storage — Volumes
    // ========================================================================

    public function createVolume(array $params): array
    {
        $body = [
            'compartmentId'      => $params['compartment_id'],
            'availabilityDomain' => $params['availability_domain'],
            'displayName'        => $params['display_name'] ?? null,
            'sizeInGBs'          => $params['size_in_gbs'] ?? 50,
        ];

        if (isset($params['vpus_per_gb'])) {
            $body['vpusPerGB'] = $params['vpus_per_gb'];
        }

        if (isset($params['freeform_tags'])) {
            $body['freeformTags'] = $params['freeform_tags'];
        }

        return $this->client->post('/20160918/volumes', $body);
    }

    public function listVolumes(array $params = []): array
    {
        $query = [];
        if (isset($params['compartment_id'])) {
            $query['compartmentId'] = $params['compartment_id'];
        }
        if (isset($params['availability_domain'])) {
            $query['availabilityDomain'] = $params['availability_domain'];
        }
        if (isset($params['lifecycle_state'])) {
            $query['lifecycleState'] = $params['lifecycle_state'];
        }

        $response = $this->client->get('/20160918/volumes', $query);
        return $response['items'] ?? $response ?? [];
    }

    public function getVolume(string $volumeId): array
    {
        return $this->client->get("/20160918/volumes/{$volumeId}");
    }

    public function deleteVolume(string $volumeId): void
    {
        $this->client->delete("/20160918/volumes/{$volumeId}");
    }

    // ========================================================================
    // Container Registry — Repositories
    // ========================================================================

    public function listRepositories(array $params = []): array
    {
        $query = [];
        if (isset($params['compartment_id'])) {
            $query['compartmentId'] = $params['compartment_id'];
        }
        if (isset($params['is_public'])) {
            $query['isPublic'] = $params['is_public'] ? 'true' : 'false';
        }

        $response = $this->client->get('/20180917/repositories', $query);
        return $response['items'] ?? $response ?? [];
    }

    public function createRepository(array $params): array
    {
        $body = [
            'compartmentId' => $params['compartment_id'],
            'displayName'   => $params['display_name'],
            'isPublic'      => $params['is_public'] ?? false,
        ];

        if (isset($params['description'])) {
            $body['description'] = $params['description'];
        }

        return $this->client->post('/20180917/repositories', $body);
    }

    // ========================================================================
    // Private helpers
    // ========================================================================

    private function normalizeInstance(array $inst): array
    {
        return [
            'id'                  => $inst['id'] ?? null,
            'display_name'        => $inst['displayName'] ?? null,
            'availability_domain' => $inst['availabilityDomain'] ?? null,
            'compartment_id'      => $inst['compartmentId'] ?? null,
            'lifecycle_state'     => $inst['lifecycleState'] ?? null,
            'shape'               => $inst['shape'] ?? null,
            'time_created'        => $inst['timeCreated'] ?? null,
            'freeform_tags'       => $inst['freeformTags'] ?? [],
        ];
    }
}
