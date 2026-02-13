# MockLib PHP SDK

PHP client library for MockFactory cloud emulation API.

## Installation

```bash
composer require mockfactory/mocklib
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use MockFactory\Client;

// Initialize client
$client = new Client(['api_key' => 'mf_...']);

// Or use environment variable:
// export MOCKFACTORY_API_KEY="mf_..."
// $client = new Client();

// Create VPC
$vpc = $client->vpc->create([
    'cidr_block' => '10.0.0.0/16'
]);
echo "Created VPC: {$vpc['id']}\n";

// Create Lambda function
$lambda = $client->lambda->create([
    'function_name' => 'my-function',
    'runtime' => 'python3.9',
    'memory_mb' => 256
]);
echo "Created Lambda: {$lambda['function_name']}\n";

// Create DynamoDB table
$table = $client->dynamodb->createTable([
    'table_name' => 'users',
    'partition_key' => 'user_id'
]);
echo "Created table: {$table['table_name']}\n";

// Create SQS queue
$queue = $client->sqs->createQueue([
    'queue_name' => 'background-jobs'
]);
echo "Created queue: {$queue['queue_url']}\n";
```

## Examples

See `examples/quickstart.php` for a complete working example.

```bash
export MOCKFACTORY_API_KEY="mf_..."
php examples/quickstart.php
```

## API Reference

### Client

```php
// Create client with API key
$client = new Client(['api_key' => 'mf_...']);

// Create client with custom config
$client = new Client([
    'api_key' => 'mf_...',
    'api_url' => 'https://api.mockfactory.io/v1'
]);
```

### VPC

```php
// Create VPC
$vpc = $client->vpc->create([
    'cidr_block' => '10.0.0.0/16',
    'enable_dns_hostnames' => true,
    'enable_dns_support' => true,
    'tags' => ['Name' => 'my-vpc']
]);

// List VPCs
$vpcs = $client->vpc->list();

// Delete VPC
$client->vpc->delete('vpc-abc123');
```

### Lambda

```php
// Create Lambda function
$lambda = $client->lambda->create([
    'function_name' => 'my-function',
    'runtime' => 'python3.9',
    'handler' => 'index.handler',
    'memory_mb' => 256,
    'timeout' => 30,
    'environment_variables' => [
        'DEBUG' => 'true'
    ]
]);

// Invoke Lambda function
$result = $client->lambda->invoke('my-function', [
    'key' => 'value'
]);

// Delete Lambda function
$client->lambda->delete('my-function');
```

### DynamoDB

```php
// Create table
$table = $client->dynamodb->createTable([
    'table_name' => 'users',
    'partition_key' => 'user_id',
    'partition_key_type' => 'S',
    'sort_key' => 'created_at',
    'sort_key_type' => 'N'
]);

// Put item
$client->dynamodb->putItem('users', [
    'user_id' => '123',
    'name' => 'John Doe'
]);

// Get item
$item = $client->dynamodb->getItem('users', [
    'user_id' => '123'
]);

// Delete table
$client->dynamodb->deleteTable('users');
```

### SQS

```php
// Create queue
$queue = $client->sqs->createQueue([
    'queue_name' => 'my-queue',
    'visibility_timeout' => 30
]);

// Send message
$messageId = $client->sqs->sendMessage($queue['queue_url'], 'Hello, World!');

// Receive messages
$messages = $client->sqs->receiveMessages($queue['queue_url'], 10);

// Delete queue
$client->sqs->deleteQueue($queue['queue_url']);
```

### Storage (S3/GCS/Azure)

```php
// Create bucket
$bucket = $client->storage->createBucket([
    'bucket_name' => 'my-bucket',
    'provider' => 's3',  // or 'gcs', 'azure'
    'region' => 'us-east-1'
]);

// Upload object
$client->storage->uploadObject('my-bucket', 'file.txt', 'content');

// Delete bucket
$client->storage->deleteBucket('my-bucket');
```

## Error Handling

```php
use MockFactory\MockFactoryException;

try {
    $vpc = $client->vpc->create([
        'cidr_block' => '10.0.0.0/16'
    ]);
} catch (MockFactoryException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Requirements

- PHP >= 7.4
- Guzzle HTTP client

## License

MIT
