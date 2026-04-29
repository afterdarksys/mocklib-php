<?php

namespace MockFactory;

class GeneratorResource
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Generate realistic user data
     */
    public function generateUsers(int $count = 10, string $role = 'mixed', ?string $organization = null, ?string $cloud = null, ?string $domain = null): array
    {
        $response = $this->client->post('/generator/users', [
            'count' => $count,
            'role' => $role,
            'organization' => $organization,
            'cloud' => $cloud,
            'domain' => $domain,
        ]);

        return $response['users'] ?? [];
    }

    /**
     * Generate realistic employee data
     */
    public function generateEmployees(int $count = 10, ?string $organization = null, ?array $departments = null): array
    {
        $response = $this->client->post('/generator/employees', [
            'count' => $count,
            'organization' => $organization,
            'departments' => $departments,
        ]);

        return $response['employees'] ?? [];
    }

    /**
     * Generate realistic organization structures
     */
    public function generateOrganizations(int $count = 5): array
    {
        $response = $this->client->post('/generator/organizations', [
            'count' => $count,
        ]);

        return $response['organizations'] ?? [];
    }

    /**
     * Generate realistic network configuration
     */
    public function generateNetworkConfig(?string $cloud = null, string $vpcCidr = '10.0.0.0/16', int $subnets = 3): array
    {
        return $this->client->post('/generator/network-config', [
            'cloud' => $cloud,
            'vpc_cidr' => $vpcCidr,
            'subnets' => $subnets,
        ]);
    }

    /**
     * Generate common IAM policy templates
     */
    public function generateIAMPolicies(string $policyType = 'common', ?array $services = null): array
    {
        $response = $this->client->post('/generator/iam-policies', [
            'policy_type' => $policyType,
            'services' => $services,
        ]);

        return $response['policies'] ?? [];
    }

    /**
     * Generate complete test scenarios
     */
    public function generateTestScenario(string $scenario = 'startup'): array
    {
        return $this->client->post('/generator/test-scenario', [
            'scenario' => $scenario,
        ]);
    }
}
