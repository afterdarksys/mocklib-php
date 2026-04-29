<?php

namespace MockFactory;

class Route53Resource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createHostedZone(array $params): array
    {
        $body = [
            'Action'            => 'CreateHostedZone',
            'Name'              => $params['name'],
            'CallerReference'   => $params['caller_reference'] ?? uniqid('mf-', true),
            'Comment'           => $params['comment'] ?? null,
            'PrivateZone'       => $params['private_zone'] ?? false,
        ];

        if (isset($params['vpc_id'])) {
            $body['VPC'] = [
                'VPCId'     => $params['vpc_id'],
                'VPCRegion' => $params['vpc_region'] ?? 'us-east-1',
            ];
        }

        $response = $this->client->post('/route53/', $body);

        return $this->normalizeHostedZone($response['HostedZone'] ?? $response);
    }

    public function listHostedZones(array $params = []): array
    {
        $body = [
            'Action'   => 'ListHostedZones',
            'MaxItems' => $params['max_items'] ?? 100,
        ];

        if (isset($params['marker'])) {
            $body['Marker'] = $params['marker'];
        }

        $response = $this->client->post('/route53/', $body);

        $zones = [];
        foreach ($response['HostedZones'] ?? [] as $zone) {
            $zones[] = $this->normalizeHostedZone($zone);
        }

        return $zones;
    }

    public function changeResourceRecordSets(string $hostedZoneId, array $changes, ?string $comment = null): array
    {
        $body = [
            'Action'       => 'ChangeResourceRecordSets',
            'HostedZoneId' => $hostedZoneId,
            'Changes'      => $changes,
        ];

        if ($comment !== null) {
            $body['Comment'] = $comment;
        }

        $response = $this->client->post('/route53/', $body);

        return [
            'change_info' => [
                'id'          => $response['ChangeInfo']['Id'] ?? null,
                'status'      => $response['ChangeInfo']['Status'] ?? null,
                'submitted_at'=> $response['ChangeInfo']['SubmittedAt'] ?? null,
                'comment'     => $response['ChangeInfo']['Comment'] ?? null,
            ],
        ];
    }

    public function listResourceRecordSets(string $hostedZoneId, array $params = []): array
    {
        $body = [
            'Action'       => 'ListResourceRecordSets',
            'HostedZoneId' => $hostedZoneId,
            'MaxItems'     => $params['max_items'] ?? 300,
        ];

        if (isset($params['start_record_name'])) {
            $body['StartRecordName'] = $params['start_record_name'];
        }

        if (isset($params['start_record_type'])) {
            $body['StartRecordType'] = $params['start_record_type'];
        }

        $response = $this->client->post('/route53/', $body);

        $records = [];
        foreach ($response['ResourceRecordSets'] ?? [] as $rrs) {
            $records[] = [
                'name'    => $rrs['Name'],
                'type'    => $rrs['Type'],
                'ttl'     => $rrs['TTL'] ?? null,
                'records' => $rrs['ResourceRecords'] ?? [],
            ];
        }

        return $records;
    }

    private function normalizeHostedZone(array $zone): array
    {
        return [
            'id'           => $zone['Id'] ?? null,
            'name'         => $zone['Name'] ?? null,
            'private_zone' => $zone['Config']['PrivateZone'] ?? false,
            'comment'      => $zone['Config']['Comment'] ?? null,
            'record_count' => $zone['ResourceRecordSetCount'] ?? null,
        ];
    }
}
