<?php

namespace MockFactory;

class EC2Resource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function runInstances(array $params): array
    {
        $body = [
            'Action'           => 'RunInstances',
            'ImageId'          => $params['image_id'],
            'InstanceType'     => $params['instance_type'] ?? 't2.micro',
            'MinCount'         => $params['min_count'] ?? 1,
            'MaxCount'         => $params['max_count'] ?? 1,
            'KeyName'          => $params['key_name'] ?? null,
            'SubnetId'         => $params['subnet_id'] ?? null,
            'SecurityGroupIds' => $params['security_group_ids'] ?? [],
            'UserData'         => $params['user_data'] ?? null,
            'Tags'             => $params['tags'] ?? [],
        ];

        $response = $this->client->post('/ec2/', $body);

        $instances = [];
        foreach ($response['Instances'] ?? [] as $inst) {
            $instances[] = $this->normalizeInstance($inst);
        }

        return $instances;
    }

    public function describeInstances(array $instanceIds = [], array $filters = []): array
    {
        $body = [
            'Action'      => 'DescribeInstances',
            'InstanceIds' => $instanceIds,
            'Filters'     => $filters,
        ];

        $response = $this->client->post('/ec2/', $body);

        $instances = [];
        foreach ($response['Reservations'] ?? [] as $reservation) {
            foreach ($reservation['Instances'] ?? [] as $inst) {
                $instances[] = $this->normalizeInstance($inst);
            }
        }

        return $instances;
    }

    public function startInstances(array $instanceIds): array
    {
        $response = $this->client->post('/ec2/', [
            'Action'      => 'StartInstances',
            'InstanceIds' => $instanceIds,
        ]);

        return $response['StartingInstances'] ?? [];
    }

    public function stopInstances(array $instanceIds, bool $force = false): array
    {
        $response = $this->client->post('/ec2/', [
            'Action'      => 'StopInstances',
            'InstanceIds' => $instanceIds,
            'Force'       => $force,
        ]);

        return $response['StoppingInstances'] ?? [];
    }

    public function terminateInstances(array $instanceIds): array
    {
        $response = $this->client->post('/ec2/', [
            'Action'      => 'TerminateInstances',
            'InstanceIds' => $instanceIds,
        ]);

        return $response['TerminatingInstances'] ?? [];
    }

    public function describeImages(array $imageIds = [], array $filters = [], ?string $owners = null): array
    {
        $body = [
            'Action'   => 'DescribeImages',
            'ImageIds' => $imageIds,
            'Filters'  => $filters,
        ];

        if ($owners !== null) {
            $body['Owners'] = $owners;
        }

        $response = $this->client->post('/ec2/', $body);

        $images = [];
        foreach ($response['Images'] ?? [] as $image) {
            $images[] = [
                'image_id'    => $image['ImageId'],
                'name'        => $image['Name'] ?? null,
                'description' => $image['Description'] ?? null,
                'state'       => $image['State'] ?? null,
                'owner_id'    => $image['OwnerId'] ?? null,
                'architecture'=> $image['Architecture'] ?? null,
                'platform'    => $image['Platform'] ?? null,
                'tags'        => $image['Tags'] ?? [],
            ];
        }

        return $images;
    }

    private function normalizeInstance(array $inst): array
    {
        return [
            'instance_id'   => $inst['InstanceId'],
            'instance_type' => $inst['InstanceType'] ?? null,
            'image_id'      => $inst['ImageId'] ?? null,
            'state'         => $inst['State']['Name'] ?? $inst['State'] ?? null,
            'public_ip'     => $inst['PublicIpAddress'] ?? null,
            'private_ip'    => $inst['PrivateIpAddress'] ?? null,
            'subnet_id'     => $inst['SubnetId'] ?? null,
            'vpc_id'        => $inst['VpcId'] ?? null,
            'key_name'      => $inst['KeyName'] ?? null,
            'tags'          => $inst['Tags'] ?? [],
            'launch_time'   => $inst['LaunchTime'] ?? null,
        ];
    }
}
