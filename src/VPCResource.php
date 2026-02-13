<?php

namespace MockFactory;

class VPCResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function create(array $params): array
    {
        $body = [
            'Action' => 'CreateVpc',
            'CidrBlock' => $params['cidr_block'],
            'EnableDnsHostnames' => $params['enable_dns_hostnames'] ?? true,
            'EnableDnsSupport' => $params['enable_dns_support'] ?? true,
            'Tags' => $params['tags'] ?? [],
        ];

        $response = $this->client->post('/aws/vpc', $body);

        return [
            'id' => $response['VpcId'],
            'cidr_block' => $response['CidrBlock'],
            'state' => $response['State'],
            'oci_vcn_id' => $response['OciVcnId'] ?? null,
            'tags' => $response['Tags'] ?? [],
        ];
    }

    public function delete(string $vpcId): void
    {
        $this->client->post('/aws/vpc', [
            'Action' => 'DeleteVpc',
            'VpcId' => $vpcId,
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
                'id' => $vpc['VpcId'],
                'cidr_block' => $vpc['CidrBlock'],
                'state' => $vpc['State'],
                'oci_vcn_id' => $vpc['OciVcnId'] ?? null,
                'tags' => $vpc['Tags'] ?? [],
            ];
        }

        return $vpcs;
    }
}
