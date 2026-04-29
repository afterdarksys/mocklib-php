<?php

namespace MockFactory;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private string $apiKey;
    private string $baseUrl;
    private HttpClient $httpClient;

    // AWS resource clients
    public VPCResource $vpc;
    public LambdaResource $lambda;
    public DynamoDBResource $dynamodb;
    public SQSResource $sqs;
    public EC2Resource $ec2;
    public STSResource $sts;
    public Route53Resource $route53;
    public SNSResource $sns;

    // Generic storage (S3-compatible abstraction)
    public StorageResource $storage;

    // Hierarchical resource clients
    public OrganizationResource $organization;
    public DomainResource $domain;
    public CloudResource $cloud;
    public ProjectResource $project;

    // IAM resource client
    public IAMResource $iam;

    // Multi-cloud resource clients
    public OCIResource $oci;
    public GCPComputeResource $gcp;
    public AzureResource $azure;

    // Data generation and utilities
    public GeneratorResource $generator;
    public UtilitiesResource $utilities;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? getenv('MOCKFACTORY_API_KEY');

        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException(
                'API key required: pass api_key or set MOCKFACTORY_API_KEY env var'
            );
        }

        $this->baseUrl = $config['api_url'] ?? 'https://api.mockfactory.io/v1';

        $this->httpClient = new HttpClient([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'User-Agent'    => 'mocklib-php/0.2.0',
            ],
            'timeout'  => 30,
        ]);

        // AWS resource clients
        $this->vpc     = new VPCResource($this);
        $this->lambda  = new LambdaResource($this);
        $this->dynamodb= new DynamoDBResource($this);
        $this->sqs     = new SQSResource($this);
        $this->ec2     = new EC2Resource($this);
        $this->sts     = new STSResource($this);
        $this->route53 = new Route53Resource($this);
        $this->sns     = new SNSResource($this);

        // Generic storage
        $this->storage = new StorageResource($this);

        // Hierarchical resource clients
        $this->organization = new OrganizationResource($this);
        $this->domain       = new DomainResource($this);
        $this->cloud        = new CloudResource($this);
        $this->project      = new ProjectResource($this);

        // IAM client
        $this->iam = new IAMResource($this);

        // Multi-cloud resource clients
        $this->oci   = new OCIResource($this);
        $this->gcp   = new GCPComputeResource($this);
        $this->azure = new AzureResource($this);

        // Data generation and utilities
        $this->generator  = new GeneratorResource($this);
        $this->utilities  = new UtilitiesResource($this);
    }

    public function request(string $method, string $endpoint, ?array $body = null): array
    {
        try {
            $options = [];
            if ($body !== null) {
                $options['json'] = $body;
            }

            $response = $this->httpClient->request($method, $endpoint, $options);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data ?? [];
        } catch (GuzzleException $e) {
            throw new MockFactoryException(
                'API request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function post(string $endpoint, array $body): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    public function get(string $endpoint, array $params = []): array
    {
        $queryString = !empty($params) ? '?' . http_build_query($params) : '';
        return $this->request('GET', $endpoint . $queryString);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }
}
