<?php

namespace MockFactory;

class IAMResource
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========================================================================
    // IAM Users
    // ========================================================================

    public function createUser(string $username, string $path = '/', ?string $organization = null, ?string $cloud = null): array
    {
        return $this->client->post('/iam/user', [
            'username' => $username,
            'path' => $path,
            'organization' => $organization,
            'cloud' => $cloud,
        ]);
    }

    public function listUsers(?string $organization = null, ?string $cloud = null): array
    {
        $params = [];
        if ($organization !== null) {
            $params['organization'] = $organization;
        }
        if ($cloud !== null) {
            $params['cloud'] = $cloud;
        }

        $response = $this->client->get('/iam/user', $params);
        return $response['users'] ?? [];
    }

    public function getUser(string $username): array
    {
        return $this->client->get("/iam/user/{$username}");
    }

    public function deleteUser(string $username): bool
    {
        $this->client->delete("/iam/user/{$username}");
        return true;
    }

    // ========================================================================
    // IAM Groups
    // ========================================================================

    public function createGroup(string $groupName, ?string $organization = null, ?string $cloud = null, ?string $description = null): array
    {
        return $this->client->post('/iam/group', [
            'group_name' => $groupName,
            'organization' => $organization,
            'cloud' => $cloud,
            'description' => $description,
        ]);
    }

    public function addUserToGroup(string $username, string $groupName): bool
    {
        $this->client->post("/iam/group/{$groupName}/users", [
            'username' => $username,
        ]);
        return true;
    }

    public function removeUserFromGroup(string $username, string $groupName): bool
    {
        $this->client->delete("/iam/group/{$groupName}/users/{$username}");
        return true;
    }

    // ========================================================================
    // IAM Roles
    // ========================================================================

    public function createRole(string $roleName, array $trustPolicy, ?string $organization = null, ?string $cloud = null, ?string $description = null): array
    {
        return $this->client->post('/iam/role', [
            'role_name' => $roleName,
            'trust_policy' => $trustPolicy,
            'organization' => $organization,
            'cloud' => $cloud,
            'description' => $description,
        ]);
    }

    // ========================================================================
    // IAM Policies
    // ========================================================================

    public function createPolicy(string $policyName, array $policyDocument, ?string $description = null, ?string $organization = null, ?string $cloud = null): array
    {
        return $this->client->post('/iam/policy', [
            'policy_name' => $policyName,
            'policy_document' => $policyDocument,
            'description' => $description,
            'organization' => $organization,
            'cloud' => $cloud,
        ]);
    }

    public function listPolicies(?string $organization = null, ?string $cloud = null): array
    {
        $params = [];
        if ($organization !== null) {
            $params['organization'] = $organization;
        }
        if ($cloud !== null) {
            $params['cloud'] = $cloud;
        }

        $response = $this->client->get('/iam/policy', $params);
        return $response['policies'] ?? [];
    }

    public function getPolicy(string $policyName): array
    {
        return $this->client->get("/iam/policy/{$policyName}");
    }

    public function deletePolicy(string $policyName): bool
    {
        $this->client->delete("/iam/policy/{$policyName}");
        return true;
    }

    // ========================================================================
    // Policy Attachments
    // ========================================================================

    public function attachUserPolicy(string $username, string $policyName): bool
    {
        $this->client->post("/iam/user/{$username}/policies", [
            'policy_name' => $policyName,
        ]);
        return true;
    }

    public function detachUserPolicy(string $username, string $policyName): bool
    {
        $this->client->delete("/iam/user/{$username}/policies/{$policyName}");
        return true;
    }

    public function attachGroupPolicy(string $groupName, string $policyName): bool
    {
        $this->client->post("/iam/group/{$groupName}/policies", [
            'policy_name' => $policyName,
        ]);
        return true;
    }

    public function detachGroupPolicy(string $groupName, string $policyName): bool
    {
        $this->client->delete("/iam/group/{$groupName}/policies/{$policyName}");
        return true;
    }

    public function attachRolePolicy(string $roleName, string $policyName): bool
    {
        $this->client->post("/iam/role/{$roleName}/policies", [
            'policy_name' => $policyName,
        ]);
        return true;
    }

    public function detachRolePolicy(string $roleName, string $policyName): bool
    {
        $this->client->delete("/iam/role/{$roleName}/policies/{$policyName}");
        return true;
    }

    // ========================================================================
    // Access Keys
    // ========================================================================

    public function createAccessKey(string $username, ?string $description = null): array
    {
        return $this->client->post("/iam/user/{$username}/access-keys", [
            'description' => $description,
        ]);
    }

    public function listAccessKeys(string $username): array
    {
        $response = $this->client->get("/iam/user/{$username}/access-keys");
        return $response['access_keys'] ?? [];
    }

    public function deleteAccessKey(string $username, string $accessKeyId): bool
    {
        $this->client->delete("/iam/user/{$username}/access-keys/{$accessKeyId}");
        return true;
    }

    // ========================================================================
    // AWS-action style methods for POST /iam/ form-body API
    // ========================================================================

    public function listRoles(?string $pathPrefix = null): array
    {
        $params = [];
        if ($pathPrefix !== null) {
            $params['path_prefix'] = $pathPrefix;
        }

        $response = $this->client->get('/iam/role', $params);
        return $response['roles'] ?? [];
    }

    public function getRole(string $roleName): array
    {
        return $this->client->get("/iam/role/{$roleName}");
    }

    public function deleteRole(string $roleName): bool
    {
        $this->client->delete("/iam/role/{$roleName}");
        return true;
    }

    // ========================================================================
    // Permission Checks & Simulation
    // ========================================================================

    public function checkPermission(string $username, string $action, string $resource, ?string $cloud = null): array
    {
        return $this->client->post('/iam/check-permission', [
            'username' => $username,
            'action' => $action,
            'resource' => $resource,
            'cloud' => $cloud,
        ]);
    }

    public function simulatePolicy(string $policyName, string $action, string $resource, ?string $username = null): array
    {
        return $this->client->post('/iam/simulate-policy', [
            'policy_name' => $policyName,
            'action' => $action,
            'resource' => $resource,
            'username' => $username,
        ]);
    }
}
