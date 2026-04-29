<?php

namespace MockFactory;

class STSResource
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getCallerIdentity(): array
    {
        $response = $this->client->post('/sts/', [
            'Action' => 'GetCallerIdentity',
        ]);

        return [
            'account'  => $response['Account'] ?? null,
            'arn'      => $response['Arn'] ?? null,
            'user_id'  => $response['UserId'] ?? null,
        ];
    }

    public function assumeRole(array $params): array
    {
        $body = [
            'Action'          => 'AssumeRole',
            'RoleArn'         => $params['role_arn'],
            'RoleSessionName' => $params['role_session_name'],
            'DurationSeconds' => $params['duration_seconds'] ?? 3600,
        ];

        if (isset($params['policy'])) {
            $body['Policy'] = $params['policy'];
        }

        if (isset($params['external_id'])) {
            $body['ExternalId'] = $params['external_id'];
        }

        $response = $this->client->post('/sts/', $body);
        $creds = $response['Credentials'] ?? [];

        return [
            'access_key_id'     => $creds['AccessKeyId'] ?? null,
            'secret_access_key' => $creds['SecretAccessKey'] ?? null,
            'session_token'     => $creds['SessionToken'] ?? null,
            'expiration'        => $creds['Expiration'] ?? null,
            'assumed_role_id'   => $response['AssumedRoleUser']['AssumedRoleId'] ?? null,
            'arn'               => $response['AssumedRoleUser']['Arn'] ?? null,
        ];
    }

    public function getSessionToken(array $params = []): array
    {
        $body = [
            'Action'          => 'GetSessionToken',
            'DurationSeconds' => $params['duration_seconds'] ?? 3600,
        ];

        if (isset($params['serial_number'])) {
            $body['SerialNumber'] = $params['serial_number'];
        }

        if (isset($params['token_code'])) {
            $body['TokenCode'] = $params['token_code'];
        }

        $response = $this->client->post('/sts/', $body);
        $creds = $response['Credentials'] ?? [];

        return [
            'access_key_id'     => $creds['AccessKeyId'] ?? null,
            'secret_access_key' => $creds['SecretAccessKey'] ?? null,
            'session_token'     => $creds['SessionToken'] ?? null,
            'expiration'        => $creds['Expiration'] ?? null,
        ];
    }
}
