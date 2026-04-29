<?php

namespace MockFactory;

class VPCResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========================================================================
    // VPCs
    // ========================================================================

    public function create(array $params): array
    {
        $body = [
            'Action'             => 'CreateVpc',
            'CidrBlock'          => $params['cidr_block'],
            'EnableDnsHostnames' => $params['enable_dns_hostnames'] ?? true,
            'EnableDnsSupport'   => $params['enable_dns_support'] ?? true,
            'Tags'               => $params['tags'] ?? [],
        ];

        $response = $this->client->post('/aws/vpc', $body);

        return [
            'id'         => $response['VpcId'],
            'cidr_block' => $response['CidrBlock'],
            'state'      => $response['State'],
            'oci_vcn_id' => $response['OciVcnId'] ?? null,
            'tags'       => $response['Tags'] ?? [],
        ];
    }

    public function delete(string $vpcId): void
    {
        $this->client->post('/aws/vpc', [
            'Action' => 'DeleteVpc',
            'VpcId'  => $vpcId,
        ]);
    }

    public function list(): array
    {
        $response = $this->client->post('/aws/vpc', [
            'Action' => 'DescribeVpcs',
        ]);

        $vpcs = [];
        foreach ($response['Vpcs'] ?? [] as $vpc) {
            $vpcs[] = [
                'id'         => $vpc['VpcId'],
                'cidr_block' => $vpc['CidrBlock'],
                'state'      => $vpc['State'],
                'oci_vcn_id' => $vpc['OciVcnId'] ?? null,
                'tags'       => $vpc['Tags'] ?? [],
            ];
        }

        return $vpcs;
    }

    // ========================================================================
    // Subnets
    // ========================================================================

    public function createSubnet(array $params): array
    {
        $body = [
            'Action'            => 'CreateSubnet',
            'VpcId'             => $params['vpc_id'],
            'CidrBlock'         => $params['cidr_block'],
            'AvailabilityZone'  => $params['availability_zone'] ?? null,
            'Tags'              => $params['tags'] ?? [],
        ];

        $response = $this->client->post('/aws/vpc', $body);

        return [
            'subnet_id'         => $response['SubnetId'],
            'vpc_id'            => $response['VpcId'],
            'cidr_block'        => $response['CidrBlock'],
            'availability_zone' => $response['AvailabilityZone'] ?? null,
            'state'             => $response['State'] ?? null,
            'tags'              => $response['Tags'] ?? [],
        ];
    }

    public function describeSubnets(array $subnetIds = [], array $filters = []): array
    {
        $response = $this->client->post('/aws/vpc', [
            'Action'    => 'DescribeSubnets',
            'SubnetIds' => $subnetIds,
            'Filters'   => $filters,
        ]);

        $subnets = [];
        foreach ($response['Subnets'] ?? [] as $subnet) {
            $subnets[] = [
                'subnet_id'         => $subnet['SubnetId'],
                'vpc_id'            => $subnet['VpcId'],
                'cidr_block'        => $subnet['CidrBlock'],
                'availability_zone' => $subnet['AvailabilityZone'] ?? null,
                'state'             => $subnet['State'] ?? null,
                'tags'              => $subnet['Tags'] ?? [],
            ];
        }

        return $subnets;
    }

    public function deleteSubnet(string $subnetId): void
    {
        $this->client->post('/aws/vpc', [
            'Action'   => 'DeleteSubnet',
            'SubnetId' => $subnetId,
        ]);
    }

    // ========================================================================
    // Security Groups
    // ========================================================================

    public function createSecurityGroup(array $params): array
    {
        $body = [
            'Action'      => 'CreateSecurityGroup',
            'VpcId'       => $params['vpc_id'],
            'GroupName'   => $params['group_name'],
            'Description' => $params['description'] ?? '',
            'Tags'        => $params['tags'] ?? [],
        ];

        $response = $this->client->post('/aws/vpc', $body);

        return [
            'group_id'    => $response['GroupId'],
            'group_name'  => $response['GroupName'] ?? $params['group_name'],
            'vpc_id'      => $response['VpcId'] ?? $params['vpc_id'],
            'description' => $response['Description'] ?? $params['description'] ?? '',
            'tags'        => $response['Tags'] ?? [],
        ];
    }

    public function describeSecurityGroups(array $groupIds = [], array $filters = []): array
    {
        $response = $this->client->post('/aws/vpc', [
            'Action'   => 'DescribeSecurityGroups',
            'GroupIds' => $groupIds,
            'Filters'  => $filters,
        ]);

        $groups = [];
        foreach ($response['SecurityGroups'] ?? [] as $sg) {
            $groups[] = [
                'group_id'    => $sg['GroupId'],
                'group_name'  => $sg['GroupName'] ?? null,
                'vpc_id'      => $sg['VpcId'] ?? null,
                'description' => $sg['Description'] ?? null,
                'ingress'     => $sg['IpPermissions'] ?? [],
                'egress'      => $sg['IpPermissionsEgress'] ?? [],
                'tags'        => $sg['Tags'] ?? [],
            ];
        }

        return $groups;
    }

    public function authorizeIngress(string $groupId, array $ipPermissions): void
    {
        $this->client->post('/aws/vpc', [
            'Action'        => 'AuthorizeSecurityGroupIngress',
            'GroupId'       => $groupId,
            'IpPermissions' => $ipPermissions,
        ]);
    }

    // ========================================================================
    // Internet Gateways
    // ========================================================================

    public function createInternetGateway(array $tags = []): array
    {
        $response = $this->client->post('/aws/vpc', [
            'Action' => 'CreateInternetGateway',
            'Tags'   => $tags,
        ]);

        return [
            'internet_gateway_id' => $response['InternetGatewayId'],
            'state'               => $response['State'] ?? 'detached',
            'tags'                => $response['Tags'] ?? [],
        ];
    }

    public function attachInternetGateway(string $internetGatewayId, string $vpcId): void
    {
        $this->client->post('/aws/vpc', [
            'Action'             => 'AttachInternetGateway',
            'InternetGatewayId'  => $internetGatewayId,
            'VpcId'              => $vpcId,
        ]);
    }

    // ========================================================================
    // Route Tables
    // ========================================================================

    public function createRouteTable(string $vpcId, array $tags = []): array
    {
        $response = $this->client->post('/aws/vpc', [
            'Action' => 'CreateRouteTable',
            'VpcId'  => $vpcId,
            'Tags'   => $tags,
        ]);

        return [
            'route_table_id' => $response['RouteTableId'],
            'vpc_id'         => $response['VpcId'] ?? $vpcId,
            'tags'           => $response['Tags'] ?? [],
        ];
    }

    public function createRoute(array $params): void
    {
        $this->client->post('/aws/vpc', [
            'Action'               => 'CreateRoute',
            'RouteTableId'         => $params['route_table_id'],
            'DestinationCidrBlock' => $params['destination_cidr_block'],
            'GatewayId'            => $params['gateway_id'] ?? null,
            'InstanceId'           => $params['instance_id'] ?? null,
            'NatGatewayId'         => $params['nat_gateway_id'] ?? null,
        ]);
    }

    public function associateRouteTable(string $routeTableId, string $subnetId): array
    {
        $response = $this->client->post('/aws/vpc', [
            'Action'       => 'AssociateRouteTable',
            'RouteTableId' => $routeTableId,
            'SubnetId'     => $subnetId,
        ]);

        return [
            'association_id' => $response['AssociationId'] ?? null,
        ];
    }
}
