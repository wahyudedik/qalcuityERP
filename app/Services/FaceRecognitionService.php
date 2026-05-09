<?php

namespace App\Services;

use App\Models\TenantApiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    protected string $pythonServiceUrl;

    protected string $apiKey;

    public function __construct(protected ?int $tenantId = null)
    {
        // Read face recognition settings from tenant DB, fallback to config/.env
        $this->pythonServiceUrl = ($tenantId ? TenantApiSetting::get($tenantId, 'face_recognition_url') : null)
            ?? config('services.face_recognition.url', 'http://localhost:5000');
        $this->apiKey = ($tenantId ? TenantApiSetting::get($tenantId, 'face_recognition_api_key') : null)
            ?? config('services.face_recognition.api_key', '');
    }

    /**
     * Register employee face
     */
    public function registerFace(int $employeeId, string $imagePath): array
    {
        try {
            if (! file_exists($imagePath)) {
                throw new \Exception("Image file not found: {$imagePath}");
            }

            // Send to Python service for face encoding
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->pythonServiceUrl}/api/face/register", [
                    'employee_id' => $employeeId,
                ]);

            if (! $response->successful()) {
                throw new \Exception('Face registration failed: '.$response->body());
            }

            $result = $response->json();

            return [
                'success' => true,
                'message' => 'Face registered successfully',
                'encoding_id' => $result['encoding_id'] ?? null,
                'face_detected' => $result['face_detected'] ?? false,
            ];

        } catch (\Exception $e) {
            Log::error('Face Registration Error', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Recognize face from image
     */
    public function recognizeFace(string $imagePath): array
    {
        try {
            if (! file_exists($imagePath)) {
                throw new \Exception("Image file not found: {$imagePath}");
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->pythonServiceUrl}/api/face/recognize");

            if (! $response->successful()) {
                throw new \Exception('Face recognition failed: '.$response->body());
            }

            $result = $response->json();

            return [
                'success' => $result['recognized'] ?? false,
                'employee_id' => $result['employee_id'] ?? null,
                'confidence' => $result['confidence'] ?? 0,
                'message' => $result['recognized'] ? 'Face recognized' : 'Face not recognized',
            ];

        } catch (\Exception $e) {
            Log::error('Face Recognition Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify liveness (anti-spoofing)
     */
    public function verifyLiveness(string $imagePath): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->pythonServiceUrl}/api/face/liveness");

            if (! $response->successful()) {
                throw new \Exception('Liveness check failed: '.$response->body());
            }

            $result = $response->json();

            return [
                'success' => true,
                'is_live' => $result['is_live'] ?? false,
                'confidence' => $result['confidence'] ?? 0,
                'message' => ($result['is_live'] ?? false) ? 'Live person detected' : 'Spoofing detected',
            ];

        } catch (\Exception $e) {
            Log::error('Liveness Check Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process attendance via face recognition
     */
    public function processAttendance(string $imagePath, string $scanType = 'check_in'): array
    {
        try {
            // Step 1: Verify liveness
            $liveness = $this->verifyLiveness($imagePath);

            if (! $liveness['success'] || ! $liveness['is_live']) {
                return [
                    'success' => false,
                    'message' => 'Liveness verification failed: '.($liveness['message'] ?? 'Unknown error'),
                ];
            }

            // Step 2: Recognize face
            $recognition = $this->recognizeFace($imagePath);

            if (! $recognition['success'] || ! $recognition['employee_id']) {
                return [
                    'success' => false,
                    'message' => 'Face not recognized',
                ];
            }

            // Step 3: Record attendance
            $attendance = $this->recordAttendance(
                $recognition['employee_id'],
                $scanType,
                $recognition['confidence']
            );

            return [
                'success' => true,
                'employee_id' => $recognition['employee_id'],
                'confidence' => $recognition['confidence'],
                'attendance' => $attendance,
                'message' => 'Attendance recorded successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Face Attendance Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Detect multiple faces in image
     */
    public function detectFaces(string $imagePath): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->pythonServiceUrl}/api/face/detect");

            if (! $response->successful()) {
                throw new \Exception('Face detection failed: '.$response->body());
            }

            $result = $response->json();

            return [
                'success' => true,
                'faces_detected' => $result['count'] ?? 0,
                'faces' => $result['faces'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('Face Detection Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'faces_detected' => 0,
            ];
        }
    }

    /**
     * Remove employee face data
     */
    public function removeFace(int $employeeId): bool
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->delete("{$this->pythonServiceUrl}/api/face/remove", [
                    'employee_id' => $employeeId,
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Face Removal Error', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Record attendance in database
     */
    private function recordAttendance(int $employeeId, string $scanType, float $confidence): array
    {
        // This would integrate with your existing Attendance model
        // For now, return mock data

        return [
            'employee_id' => $employeeId,
            'scan_type' => $scanType,
            'timestamp' => now()->toDateTimeString(),
            'confidence' => $confidence,
            'method' => 'face_recognition',
        ];
    }

    /**
     * Capture image from webcam/camera
     */
    public function captureFromCamera(int $cameraIndex = 0): array
    {
        try {
            // Request Python service to capture from camera
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->post("{$this->pythonServiceUrl}/api/camera/capture", [
                    'camera_index' => $cameraIndex,
                ]);

            if (! $response->successful()) {
                throw new \Exception('Camera capture failed: '.$response->body());
            }

            $result = $response->json();

            return [
                'success' => true,
                'image_path' => $result['image_path'] ?? null,
                'image_data' => $result['image_data'] ?? null, // base64 encoded
            ];

        } catch (\Exception $e) {
            Log::error('Camera Capture Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
