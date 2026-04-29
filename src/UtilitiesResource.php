<?php

namespace MockFactory;

class UtilitiesResource
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========================================================================
    // Binary/Hex Conversion
    // ========================================================================

    public function bin2hex(string $binary): string
    {
        $response = $this->client->post('/utilities/bin2hex', ['binary' => $binary]);
        return $response['hex'];
    }

    public function hex2bin(string $hexString): string
    {
        $response = $this->client->post('/utilities/hex2bin', ['hex' => $hexString]);
        return $response['binary'];
    }

    // ========================================================================
    // IP Address Conversion
    // ========================================================================

    public function ip2bin(string $ip): string
    {
        $response = $this->client->post('/utilities/ip2bin', ['ip' => $ip]);
        return $response['binary'];
    }

    public function bin2ip(string $binary): string
    {
        $response = $this->client->post('/utilities/bin2ip', ['binary' => $binary]);
        return $response['ip'];
    }

    public function ip2long(string $ip): int
    {
        $response = $this->client->post('/utilities/ip2long', ['ip' => $ip]);
        return $response['long'];
    }

    public function long2ip(int $longInt): string
    {
        $response = $this->client->post('/utilities/long2ip', ['long' => $longInt]);
        return $response['ip'];
    }

    // ========================================================================
    // IPv6 Helpers
    // ========================================================================

    public function expandIPv6(string $ipv6): string
    {
        $response = $this->client->post('/utilities/expand-ipv6', ['ipv6' => $ipv6]);
        return $response['expanded'];
    }

    public function compressIPv6(string $ipv6): string
    {
        $response = $this->client->post('/utilities/compress-ipv6', ['ipv6' => $ipv6]);
        return $response['compressed'];
    }

    public function isValidIPv6(string $ipv6): bool
    {
        $response = $this->client->post('/utilities/validate-ipv6', ['ipv6' => $ipv6]);
        return $response['valid'];
    }

    // ========================================================================
    // CIDR Helpers
    // ========================================================================

    public function cidrToRange(string $cidr): array
    {
        return $this->client->post('/utilities/cidr-to-range', ['cidr' => $cidr]);
    }

    public function ipInCIDR(string $ip, string $cidr): bool
    {
        $response = $this->client->post('/utilities/ip-in-cidr', ['ip' => $ip, 'cidr' => $cidr]);
        return $response['in_range'];
    }

    public function cidrOverlap(string $cidr1, string $cidr2): bool
    {
        $response = $this->client->post('/utilities/cidr-overlap', ['cidr1' => $cidr1, 'cidr2' => $cidr2]);
        return $response['overlap'];
    }

    // ========================================================================
    // YAML Helpers
    // ========================================================================

    public function yamlToJSON(string $yamlStr): array
    {
        $response = $this->client->post('/utilities/yaml-to-json', ['yaml' => $yamlStr]);
        return $response['json'];
    }

    public function jsonToYAML(array $jsonObj): string
    {
        $response = $this->client->post('/utilities/json-to-yaml', ['json' => $jsonObj]);
        return $response['yaml'];
    }

    // ========================================================================
    // Base64 Helpers
    // ========================================================================

    public function base64Encode(string $data): string
    {
        $response = $this->client->post('/utilities/base64-encode', ['data' => $data]);
        return $response['encoded'];
    }

    public function base64Decode(string $encoded): string
    {
        $response = $this->client->post('/utilities/base64-decode', ['encoded' => $encoded]);
        return $response['decoded'];
    }

    // ========================================================================
    // Hash Helpers
    // ========================================================================

    public function md5(string $data): string
    {
        $response = $this->client->post('/utilities/md5', ['data' => $data]);
        return $response['hash'];
    }

    public function sha1(string $data): string
    {
        $response = $this->client->post('/utilities/sha1', ['data' => $data]);
        return $response['hash'];
    }

    public function sha256(string $data): string
    {
        $response = $this->client->post('/utilities/sha256', ['data' => $data]);
        return $response['hash'];
    }

    public function sha512(string $data): string
    {
        $response = $this->client->post('/utilities/sha512', ['data' => $data]);
        return $response['hash'];
    }

    // ========================================================================
    // UUID Helpers
    // ========================================================================

    public function generateUUID(int $version = 4): string
    {
        $response = $this->client->post('/utilities/generate-uuid', ['version' => $version]);
        return $response['uuid'];
    }

    public function validateUUID(string $uuidStr): bool
    {
        $response = $this->client->post('/utilities/validate-uuid', ['uuid' => $uuidStr]);
        return $response['valid'];
    }

    // ========================================================================
    // String Helpers
    // ========================================================================

    public function slugify(string $text): string
    {
        $response = $this->client->post('/utilities/slugify', ['text' => $text]);
        return $response['slug'];
    }

    public function randomString(int $length = 16, string $charset = 'alphanumeric'): string
    {
        $response = $this->client->post('/utilities/random-string', [
            'length' => $length,
            'charset' => $charset,
        ]);
        return $response['string'];
    }

    // ========================================================================
    // ARN Helpers
    // ========================================================================

    public function parseARN(string $arn): array
    {
        return $this->client->post('/utilities/parse-arn', ['arn' => $arn]);
    }

    public function buildARN(string $service, string $resource, ?string $account = null, ?string $region = null, string $partition = 'aws'): string
    {
        $response = $this->client->post('/utilities/build-arn', [
            'service' => $service,
            'resource' => $resource,
            'account' => $account,
            'region' => $region,
            'partition' => $partition,
        ]);
        return $response['arn'];
    }

    public function validateARN(string $arn): bool
    {
        $response = $this->client->post('/utilities/validate-arn', ['arn' => $arn]);
        return $response['valid'];
    }
}
