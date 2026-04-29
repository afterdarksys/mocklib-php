<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MockFactory\Client;

// Create MockFactory client
$client = new Client([
    'api_key' => 'mf_your_api_key_here'
]);

// ========================================================================
// Basic Cloud Resources
// ========================================================================

// Create a VPC
$vpc = $client->vpc->create([
    'cidr_block' => '10.0.0.0/16',
    'enable_dns_hostnames' => true,
    'enable_dns_support' => true,
]);
echo "Created VPC: {$vpc['VpcId']}\n";

// Create a Lambda function
$lambda = $client->lambda->create([
    'function_name' => 'my-api',
    'runtime' => 'python3.9',
    'memory_mb' => 256,
    'timeout' => 30,
]);
echo "Created Lambda: {$lambda['FunctionName']}\n";

// Create a DynamoDB table
$table = $client->dynamodb->createTable([
    'table_name' => 'users',
    'partition_key' => 'user_id',
    'partition_key_type' => 'S',
]);
echo "Created DynamoDB table: {$table['TableName']}\n";

// Create an SQS queue
$queue = $client->sqs->createQueue([
    'queue_name' => 'my-queue',
    'visibility_timeout' => 30,
]);
echo "Created SQS queue: {$queue['QueueUrl']}\n";

// Create a storage bucket
$bucket = $client->storage->createBucket([
    'bucket_name' => 'my-bucket',
    'provider' => 's3',
    'region' => 'us-east-1',
]);
echo "Created bucket: {$bucket['BucketName']}\n";

// ========================================================================
// Hierarchical Resources
// ========================================================================

// Create an organization
$org = $client->organization->create([
    'name' => 'acme-corp',
    'plan' => 'pro',
    'description' => 'Acme Corporation',
]);
echo "Created organization: {$org['name']}\n";

// Create a domain
$domain = $client->domain->create([
    'domain' => 'example.com',
    'organization' => 'acme-corp',
    'verified' => true,
]);
echo "Created domain: {$domain['domain']}\n";

// Create a cloud environment
$cloud = $client->cloud->create([
    'name' => 'dev-cloud',
    'provider' => 'aws',
    'region' => 'us-east-1',
    'organization' => 'acme-corp',
]);
echo "Created cloud: {$cloud['name']}\n";

// Create a project
$project = $client->project->create([
    'name' => 'web-app',
    'environment' => 'production',
    'organization' => 'acme-corp',
    'description' => 'Main web application',
]);
echo "Created project: {$project['name']}\n";

// ========================================================================
// IAM Resources
// ========================================================================

// Create an IAM user
$user = $client->iam->createUser('john.smith', '/', 'acme-corp', 'dev-cloud');
echo "Created IAM user: {$user['username']}\n";

// Create an IAM group
$group = $client->iam->createGroup('developers', 'acme-corp', 'dev-cloud', 'Development team');
echo "Created IAM group: {$group['group_name']}\n";

// Add user to group
$client->iam->addUserToGroup('john.smith', 'developers');
echo "Added user to group\n";

// Create an IAM policy
$policyDocument = [
    'Version' => '2012-10-17',
    'Statement' => [
        [
            'Effect' => 'Allow',
            'Action' => 's3:Get*',
            'Resource' => '*',
        ],
    ],
];

$policy = $client->iam->createPolicy('s3-read-only', $policyDocument, 'Read-only S3 access', 'acme-corp', 'dev-cloud');
echo "Created IAM policy: {$policy['policy_name']}\n";

// Attach policy to user
$client->iam->attachUserPolicy('john.smith', 's3-read-only');
echo "Attached policy to user\n";

// Create access key for user
$accessKey = $client->iam->createAccessKey('john.smith', 'CLI access');
echo "Created access key: {$accessKey['access_key_id']}\n";

// ========================================================================
// Data Generation
// ========================================================================

// Generate test users
$users = $client->generator->generateUsers(5, 'developer', 'acme-corp', 'dev-cloud', 'acme.com');
echo "Generated " . count($users) . " users\n";

// Generate test employees
$employees = $client->generator->generateEmployees(10, 'acme-corp', ['Engineering', 'Sales', 'Marketing']);
echo "Generated " . count($employees) . " employees\n";

// Generate test scenario
$scenario = $client->generator->generateTestScenario('startup');
echo "Generated test scenario\n";

// ========================================================================
// Utilities
// ========================================================================

// IP conversion
$binary = $client->utilities->ip2bin('192.168.1.1');
echo "IP to binary: {$binary}\n";

// CIDR operations
$inRange = $client->utilities->ipInCIDR('10.0.0.50', '10.0.0.0/24');
echo "IP in CIDR: " . ($inRange ? 'true' : 'false') . "\n";

// Hash generation
$hash = $client->utilities->sha256('Hello World');
echo "SHA256 hash: {$hash}\n";

// UUID generation
$uuid = $client->utilities->generateUUID(4);
echo "Generated UUID: {$uuid}\n";

// ARN parsing
$arnParts = $client->utilities->parseARN('arn:aws:iam::123456789:user/john');
echo "ARN service: {$arnParts['service']}\n";

// Base64 encoding
$encoded = $client->utilities->base64Encode('Hello World');
echo "Base64 encoded: {$encoded}\n";

// Slugify
$slug = $client->utilities->slugify('Hello World & Stuff!');
echo "Slugified: {$slug}\n";

echo "\nAll operations completed successfully!\n";
