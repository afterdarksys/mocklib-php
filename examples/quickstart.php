<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MockFactory\Client;

// Initialize client (reads MOCKFACTORY_API_KEY from environment)
$client = new Client();

// Or pass API key explicitly:
// $client = new Client(['api_key' => 'mf_...']);

echo "MockLib PHP SDK - Quick Start\n\n";

// Create VPC
echo "Creating VPC...\n";
$vpc = $client->vpc->create([
    'cidr_block' => '10.0.0.0/16',
    'tags' => [
        'Name' => 'demo-vpc',
        'Purpose' => 'testing'
    ]
]);
echo "✓ Created VPC: {$vpc['id']}\n";
echo "  CIDR: {$vpc['cidr_block']}\n";
echo "  State: {$vpc['state']}\n\n";

// Create Lambda function
echo "Creating Lambda function...\n";
$lambda = $client->lambda->create([
    'function_name' => 'demo-function',
    'runtime' => 'python3.9',
    'memory_mb' => 256,
    'timeout' => 30,
    'environment_variables' => [
        'ENV' => 'testing',
        'DEBUG' => 'true'
    ]
]);
echo "✓ Created Lambda: {$lambda['id']}\n";
echo "  Name: {$lambda['function_name']}\n";
echo "  Runtime: {$lambda['runtime']}\n";
echo "  Memory: {$lambda['memory_mb']}MB\n\n";

// Create DynamoDB table
echo "Creating DynamoDB table...\n";
$table = $client->dynamodb->createTable([
    'table_name' => 'users',
    'partition_key' => 'user_id',
    'partition_key_type' => 'S',
    'sort_key' => 'created_at',
    'sort_key_type' => 'N'
]);
echo "✓ Created DynamoDB table: {$table['id']}\n";
echo "  Name: {$table['table_name']}\n";
echo "  Partition Key: {$table['partition_key']}\n\n";

// Create SQS queue
echo "Creating SQS queue...\n";
$queue = $client->sqs->createQueue([
    'queue_name' => 'background-jobs',
    'visibility_timeout' => 30
]);
echo "✓ Created SQS queue: {$queue['id']}\n";
echo "  Name: {$queue['queue_name']}\n";
echo "  URL: {$queue['queue_url']}\n\n";

// List all VPCs
echo "Listing all VPCs...\n";
$vpcs = $client->vpc->list();
echo "✓ Found " . count($vpcs) . " VPC(s)\n";
foreach ($vpcs as $v) {
    echo "  - {$v['id']}: {$v['cidr_block']} ({$v['state']})\n";
}

echo "\n✅ Demo complete!\n";
echo "💰 Estimated cost: ~\$0.05 (metadata operations are cheap!)\n";
echo "\nClean up:\n";
echo "  \$client->vpc->delete('{$vpc['id']}');\n";
echo "  \$client->lambda->delete('{$lambda['function_name']}');\n";
