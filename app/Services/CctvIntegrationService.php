<?php

namespace App\Services;

use App\Models\TenantApiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CctvIntegrationService
{
    protected string $nvrUrl;

    protected string $apiKey;

    protected array $cameras;

    public function __construct(protected ?int $tenantId = null)
    {
        // Resolve tenant ID from authenticated user if not provided
        if ($this->tenantId === null && auth()->check()) {
            $this->tenantId = auth()->user()->tenant_id;
        }

        // Read CCTV settings from tenant DB, fallback to config/.env
        $this->nvrUrl = ($this->tenantId ? TenantApiSetting::get($this->tenantId, 'cctv_nvr_url') : null)
            ?? config('services.cctv.nvr_url', 'http://192.168.1.100:8000');
        $this->apiKey = ($this->tenantId ? TenantApiSetting::get($this->tenantId, 'cctv_api_key') : null)
            ?? config('services.cctv.api_key', '');
        $this->cameras = config('services.cctv.cameras', []);
    }

    /**
     * Get live stream URL for camera
     */
    public function getLiveStream(int $cameraId): array
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            // Generate HLS stream URL
            $streamUrl = "{$this->nvrUrl}/stream/{$camera['channel']}/playlist.m3u8";

            return [
                'success' => true,
                'stream_url' => $streamUrl,
                'camera_name' => $camera['name'],
                'location' => $camera['location'] ?? null,
                'resolution' => $camera['resolution'] ?? '1920x1080',
            ];
        } catch (\Exception $e) {
            Log::error('CCTV Stream Error', [
                'camera_id' => $cameraId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recorded footage
     */
    public function getRecording(int $cameraId, string $startTime, string $endTime): array
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            $response = Http::timeout(60)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->get("{$this->nvrUrl}/api/recordings", [
                    'camera_id' => $camera['channel'],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to retrieve recordings');
            }

            $recordings = $response->json();

            return [
                'success' => true,
                'recordings' => $recordings,
                'total' => count($recordings),
            ];
        } catch (\Exception $e) {
            Log::error('CCTV Recording Error', [
                'camera_id' => $cameraId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Take snapshot from camera
     */
    public function takeSnapshot(int $cameraId): array
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            // Capture snapshot via NVR API
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->get("{$this->nvrUrl}/api/snapshot", [
                    'camera_id' => $camera['channel'],
                ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to capture snapshot');
            }

            // Save snapshot
            $filename = "cctv/snapshots/camera_{$cameraId}_".now()->format('Ymd_His').'.jpg';
            Storage::disk('public')->put($filename, $response->body());

            return [
                'success' => true,
                'snapshot_url' => Storage::url($filename),
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            Log::error('CCTV Snapshot Error', [
                'camera_id' => $cameraId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Detect motion in camera feed
     */
    public function detectMotion(int $cameraId): array
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->get("{$this->nvrUrl}/api/motion-detect", [
                    'camera_id' => $camera['channel'],
                ]);

            if (! $response->successful()) {
                throw new \Exception('Motion detection failed');
            }

            $result = $response->json();

            return [
                'success' => true,
                'motion_detected' => $result['motion_detected'] ?? false,
                'confidence' => $result['confidence'] ?? 0,
                'regions' => $result['regions'] ?? [],
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            Log::error('Motion Detection Error', [
                'camera_id' => $cameraId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get camera status
     */
    public function getCameraStatus(int $cameraId): array
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->get("{$this->nvrUrl}/api/camera/status", [
                    'camera_id' => $camera['channel'],
                ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to get camera status');
            }

            $status = $response->json();

            return [
                'success' => true,
                'online' => $status['online'] ?? false,
                'recording' => $status['recording'] ?? false,
                'storage_used' => $status['storage_used'] ?? null,
                'last_motion' => $status['last_motion'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Camera Status Error', [
                'camera_id' => $cameraId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all cameras
     */
    public function getAllCameras(): array
    {
        try {
            $cameras = [];

            foreach ($this->cameras as $id => $config) {
                $status = $this->getCameraStatus($id);

                $cameras[] = [
                    'id' => $id,
                    'name' => $config['name'],
                    'location' => $config['location'] ?? null,
                    'status' => $status,
                ];
            }

            return [
                'success' => true,
                'cameras' => $cameras,
                'total' => count($cameras),
            ];
        } catch (\Exception $e) {
            Log::error('Get Cameras Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * PTZ Control (Pan-Tilt-Zoom)
     */
    public function ptzControl(int $cameraId, string $command, float $speed = 0.5): bool
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            $validCommands = ['up', 'down', 'left', 'right', 'zoom_in', 'zoom_out', 'stop'];

            if (! in_array($command, $validCommands)) {
                throw new \Exception("Invalid PTZ command: {$command}");
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->post("{$this->nvrUrl}/api/ptz/control", [
                    'camera_id' => $camera['channel'],
                    'command' => $command,
                    'speed' => $speed,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('PTZ Control Error', [
                'camera_id' => $cameraId,
                'command' => $command,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Setup motion detection alert webhook
     */
    public function setupMotionAlert(int $cameraId, string $webhookUrl): bool
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->post("{$this->nvrUrl}/api/alerts/setup", [
                    'camera_id' => $camera['channel'],
                    'alert_type' => 'motion',
                    'webhook_url' => $webhookUrl,
                    'enabled' => true,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Motion Alert Setup Error', [
                'camera_id' => $cameraId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Export footage to file
     */
    public function exportFootage(int $cameraId, string $startTime, string $endTime, string $format = 'mp4'): array
    {
        try {
            $camera = $this->getCameraConfig($cameraId);

            if (! $camera) {
                throw new \Exception("Camera {$cameraId} not found");
            }

            $response = Http::timeout(300) // 5 minutes timeout for large exports
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->post("{$this->nvrUrl}/api/export", [
                    'camera_id' => $camera['channel'],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'format' => $format,
                ]);

            if (! $response->successful()) {
                throw new \Exception('Export failed');
            }

            $result = $response->json();

            return [
                'success' => true,
                'download_url' => $result['download_url'] ?? null,
                'file_size' => $result['file_size'] ?? null,
                'duration' => $result['duration'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Footage Export Error', [
                'camera_id' => $cameraId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Get camera configuration
     */
    private function getCameraConfig(int $cameraId): ?array
    {
        return $this->cameras[$cameraId] ?? null;
    }
}
