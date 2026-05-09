<?php

namespace App\Services;

use App\Models\TenantStorageConfig;
use Aws\S3\S3Client;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Cloud Storage Service
 *
 * Multi-cloud storage integration (S3, Google Cloud Storage, Azure Blob Storage).
 *
 * Note: Requires optional packages:
 * - aws/aws-sdk-php (for S3)
 * - google/cloud-storage (for GCS)
 * - microsoft/azure-storage-blob (for Azure)
 */
class CloudStorageService
{
    public ?TenantStorageConfig $config = null;

    public function __construct(?int $tenantId = null)
    {
        if ($tenantId) {
            $this->config = TenantStorageConfig::where('tenant_id', $tenantId)
                ->active()
                ->default()
                ->first();
        }
    }

    /**
     * Upload file to cloud storage
     */
    public function upload(string $filePath, string $fileName, array $options = []): string
    {
        if (! $this->config) {
            // Use local storage
            return $this->uploadToLocal($filePath, $fileName, $options);
        }

        return match ($this->config->provider) {
            's3' => $this->uploadToS3($filePath, $fileName, $options),
            'gcs' => $this->uploadToGcs($filePath, $fileName, $options),
            'azure' => $this->uploadToAzure($filePath, $fileName, $options),
            default => throw new \Exception("Unsupported storage provider: {$this->config->provider}"),
        };
    }

    /**
     * Download file from cloud storage
     */
    public function download(string $filePath): string
    {
        if (! $this->config) {
            return Storage::get($filePath);
        }

        return match ($this->config->provider) {
            's3' => $this->downloadFromS3($filePath),
            'gcs' => $this->downloadFromGcs($filePath),
            'azure' => $this->downloadFromAzure($filePath),
            default => throw new \Exception("Unsupported storage provider: {$this->config->provider}"),
        };
    }

    /**
     * Delete file from cloud storage
     */
    public function delete(string $filePath): bool
    {
        if (! $this->config) {
            return Storage::delete($filePath);
        }

        return match ($this->config->provider) {
            's3' => $this->deleteFromS3($filePath),
            'gcs' => $this->deleteFromGcs($filePath),
            'azure' => $this->deleteFromAzure($filePath),
            default => throw new \Exception("Unsupported storage provider: {$this->config->provider}"),
        };
    }

    /**
     * Get file URL
     */
    public function getUrl(string $filePath, int $expiration = 3600): string
    {
        if (! $this->config) {
            return Storage::url($filePath);
        }

        return match ($this->config->provider) {
            's3' => $this->getS3Url($filePath, $expiration),
            'gcs' => $this->getGcsUrl($filePath, $expiration),
            'azure' => $this->getAzureUrl($filePath, $expiration),
            default => throw new \Exception("Unsupported storage provider: {$this->config->provider}"),
        };
    }

    /**
     * Upload to local storage
     */
    protected function uploadToLocal(string $filePath, string $fileName, array $options = []): string
    {
        $content = file_get_contents($filePath);
        $path = "documents/{$this->getTenantId()}/{$fileName}";

        Storage::put($path, $content);

        return $path;
    }

    /**
     * Upload to S3
     */
    protected function uploadToS3(string $filePath, string $fileName, array $options = []): string
    {
        if (! class_exists('\\Aws\\S3\\S3Client')) {
            throw new \Exception('AWS SDK not installed. Run: composer require aws/aws-sdk-php');
        }

        $s3Client = new S3Client($this->config->getStorageConfig());

        $key = "documents/{$fileName}";
        $content = file_get_contents($filePath);

        $s3Client->putObject([
            'Bucket' => $this->config->bucket_name,
            'Key' => $key,
            'Body' => $content,
            'ACL' => $options['acl'] ?? 'private',
        ]);

        return $key;
    }

    /**
     * Download from S3
     */
    protected function downloadFromS3(string $filePath): string
    {
        if (! class_exists('\\Aws\\S3\\S3Client')) {
            throw new \Exception('AWS SDK not installed. Run: composer require aws/aws-sdk-php');
        }

        $s3Client = new S3Client($this->config->getStorageConfig());

        $result = $s3Client->getObject([
            'Bucket' => $this->config->bucket_name,
            'Key' => $filePath,
        ]);

        return (string) $result['Body'];
    }

    /**
     * Delete from S3
     */
    protected function deleteFromS3(string $filePath): bool
    {
        if (! class_exists('\\Aws\\S3\\S3Client')) {
            throw new \Exception('AWS SDK not installed. Run: composer require aws/aws-sdk-php');
        }

        $s3Client = new S3Client($this->config->getStorageConfig());

        $s3Client->deleteObject([
            'Bucket' => $this->config->bucket_name,
            'Key' => $filePath,
        ]);

        return true;
    }

    /**
     * Get S3 URL
     */
    protected function getS3Url(string $filePath, int $expiration = 3600): string
    {
        if (! class_exists('\\Aws\\S3\\S3Client')) {
            throw new \Exception('AWS SDK not installed. Run: composer require aws/aws-sdk-php');
        }

        $s3Client = new S3Client($this->config->getStorageConfig());

        $command = $s3Client->getCommand('GetObject', [
            'Bucket' => $this->config->bucket_name,
            'Key' => $filePath,
        ]);

        $request = $s3Client->createPresignedRequest($command, "+{$expiration} seconds");

        return (string) $request->getUri();
    }

    /**
     * Upload to Google Cloud Storage
     */
    protected function uploadToGcs(string $filePath, string $fileName, array $options = []): string
    {
        if (! class_exists('\\Google\\Cloud\\Storage\\StorageClient')) {
            throw new \Exception('Google Cloud SDK not installed. Run: composer require google/cloud-storage');
        }

        $storageClient = new StorageClient([
            'keyFilePath' => config('services.gcs.key_file_path'),
        ]);

        $bucket = $storageClient->bucket($this->config->bucket_name);
        $objectName = "documents/{$fileName}";

        $bucket->upload(
            file_get_contents($filePath),
            ['name' => $objectName]
        );

        return $objectName;
    }

    /**
     * Download from Google Cloud Storage
     */
    protected function downloadFromGcs(string $filePath): string
    {
        if (! class_exists('\\Google\\Cloud\\Storage\\StorageClient')) {
            throw new \Exception('Google Cloud SDK not installed. Run: composer require google/cloud-storage');
        }

        $storageClient = new StorageClient([
            'keyFilePath' => config('services.gcs.key_file_path'),
        ]);

        $bucket = $storageClient->bucket($this->config->bucket_name);
        $object = $bucket->object($filePath);

        return $object->downloadAsString();
    }

    /**
     * Delete from Google Cloud Storage
     */
    protected function deleteFromGcs(string $filePath): bool
    {
        if (! class_exists('\\Google\\Cloud\\Storage\\StorageClient')) {
            throw new \Exception('Google Cloud SDK not installed. Run: composer require google/cloud-storage');
        }

        $storageClient = new StorageClient([
            'keyFilePath' => config('services.gcs.key_file_path'),
        ]);

        $bucket = $storageClient->bucket($this->config->bucket_name);
        $object = $bucket->object($filePath);
        $object->delete();

        return true;
    }

    /**
     * Get GCS URL
     */
    protected function getGcsUrl(string $filePath, int $expiration = 3600): string
    {
        if (! class_exists('\\Google\\Cloud\\Storage\\StorageClient')) {
            throw new \Exception('Google Cloud SDK not installed. Run: composer require google/cloud-storage');
        }

        $storageClient = new StorageClient([
            'keyFilePath' => config('services.gcs.key_file_path'),
        ]);

        $bucket = $storageClient->bucket($this->config->bucket_name);
        $object = $bucket->object($filePath);

        return $object->signedUrl(new \DateTime("+{$expiration} seconds"));
    }

    /**
     * Upload to Azure Blob Storage
     */
    protected function uploadToAzure(string $filePath, string $fileName, array $options = []): string
    {
        if (! class_exists('\\MicrosoftAzure\\Storage\\Blob\\BlobRestProxy')) {
            throw new \Exception('Azure SDK not installed. Run: composer require microsoft/azure-storage-blob');
        }

        $blobClient = BlobRestProxy::createBlobService(
            "DefaultEndpointsProtocol=https;AccountName={$this->config->access_key};AccountKey={$this->config->secret_key};EndpointSuffix=core.windows.net"
        );

        $blobName = "documents/{$fileName}";
        $content = file_get_contents($filePath);

        $blobClient->createBlockBlob($this->config->bucket_name, $blobName, $content);

        return $blobName;
    }

    /**
     * Download from Azure Blob Storage
     */
    protected function downloadFromAzure(string $filePath): string
    {
        if (! class_exists('\\MicrosoftAzure\\Storage\\Blob\\BlobRestProxy')) {
            throw new \Exception('Azure SDK not installed. Run: composer require microsoft/azure-storage-blob');
        }

        $blobClient = BlobRestProxy::createBlobService(
            "DefaultEndpointsProtocol=https;AccountName={$this->config->access_key};AccountKey={$this->config->secret_key};EndpointSuffix=core.windows.net"
        );

        $blob = $blobClient->getBlob($this->config->bucket_name, $filePath);

        return stream_get_contents($blob->getContentStream());
    }

    /**
     * Delete from Azure Blob Storage
     */
    protected function deleteFromAzure(string $filePath): bool
    {
        if (! class_exists('\\MicrosoftAzure\\Storage\\Blob\\BlobRestProxy')) {
            throw new \Exception('Azure SDK not installed. Run: composer require microsoft/azure-storage-blob');
        }

        $blobClient = BlobRestProxy::createBlobService(
            "DefaultEndpointsProtocol=https;AccountName={$this->config->access_key};AccountKey={$this->config->secret_key};EndpointSuffix=core.windows.net"
        );

        $blobClient->deleteBlob($this->config->bucket_name, $filePath);

        return true;
    }

    /**
     * Get Azure URL
     */
    protected function getAzureUrl(string $filePath, int $expiration = 3600): string
    {
        if (! class_exists('\\MicrosoftAzure\\Storage\\Blob\\BlobRestProxy')) {
            throw new \Exception('Azure SDK not installed. Run: composer require microsoft/azure-storage-blob');
        }

        // Azure SAS URL generation - simplified approach
        $accountName = $this->config->access_key;
        $containerName = $this->config->bucket_name;
        $blobName = $filePath;

        // Build base URL
        $baseUrl = "https://{$accountName}.blob.core.windows.net/{$containerName}/{$blobName}";

        // For now, return the direct URL (SAS token generation requires additional setup)
        Log::warning('Azure SAS URL generation not fully implemented. Returning direct URL.');

        return $baseUrl;
    }

    /**
     * Test connection
     */
    public function testConnection(): bool
    {
        if (! $this->config) {
            return Storage::exists('/');
        }

        try {
            return match ($this->config->provider) {
                's3' => $this->testS3Connection(),
                'gcs' => $this->testGcsConnection(),
                'azure' => $this->testAzureConnection(),
                default => false,
            };
        } catch (\Exception $e) {
            Log::error('Cloud storage connection test failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Test S3 connection
     */
    protected function testS3Connection(): bool
    {
        if (! class_exists('\\Aws\\S3\\S3Client')) {
            return false;
        }

        $s3Client = new S3Client($this->config->getStorageConfig());
        $s3Client->listBuckets();

        return true;
    }

    /**
     * Test GCS connection
     */
    protected function testGcsConnection(): bool
    {
        if (! class_exists('\\Google\\Cloud\\Storage\\StorageClient')) {
            return false;
        }

        $storageClient = new StorageClient([
            'keyFilePath' => config('services.gcs.key_file_path'),
        ]);
        $storageClient->buckets(); // Corrected method name

        return true;
    }

    /**
     * Test Azure connection
     */
    protected function testAzureConnection(): bool
    {
        if (! class_exists('\\MicrosoftAzure\\Storage\\Blob\\BlobRestProxy')) {
            return false;
        }

        $blobClient = BlobRestProxy::createBlobService(
            "DefaultEndpointsProtocol=https;AccountName={$this->config->access_key};AccountKey={$this->config->secret_key};EndpointSuffix=core.windows.net"
        );
        $blobClient->listBlobs($this->config->bucket_name);

        return true;
    }

    /**
     * Get tenant ID
     */
    protected function getTenantId(): int
    {
        return Auth::user()?->tenant_id ?? 1;
    }

    /**
     * Get storage usage statistics
     */
    public function getStorageUsage(): array
    {
        if (! $this->config) {
            return [
                'provider' => 'local',
                'total_size' => 0,
                'file_count' => 0,
            ];
        }

        return [
            'provider' => $this->config->provider,
            'bucket' => $this->config->bucket_name,
            'region' => $this->config->region,
            'is_active' => $this->config->is_active,
        ];
    }
}
