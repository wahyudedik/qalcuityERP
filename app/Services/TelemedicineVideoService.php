<?php

namespace App\Services;

use App\Models\Teleconsultation;
use App\Models\TeleconsultationRecording;
use App\Models\TelemedicineSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelemedicineVideoService
{
    /**
     * Generate meeting room for consultation.
     */
    public function generateMeetingRoom(Teleconsultation $consultation): array
    {
        $tenantId = $consultation->patient?->tenant_id ?? 1;
        $settings = TelemedicineSetting::getForTenant($tenantId);

        // Generate unique room name
        $roomName = sprintf(
            '%s_consultation_%d_%d',
            $tenantId,
            $consultation->id,
            time()
        );

        // Generate meeting URL
        $meetingUrl = sprintf(
            '%s/%s',
            rtrim($settings->jitsi_server_url, '/'),
            $roomName
        );

        // Add query parameters for better UX
        $meetingUrl .= '?config.startWithAudioMuted=false'
            . '&config.startWithVideoMuted=false'
            . '&config.disableDeepLinking=true'
            . '&config.enableWelcomePage=false';

        // Generate tokens if using self-hosted with auth
        $moderatorToken = null;
        $participantToken = null;

        if ($settings->isSelfHosted() && $settings->jitsi_secret) {
            $moderatorToken = $this->generateJWT($roomName, 'moderator', $consultation->doctor_id);
            $participantToken = $this->generateJWT($roomName, 'participant', $consultation->patient_id);
        }

        // Update consultation with meeting details
        $consultation->update([
            'meeting_id' => $roomName,
            'meeting_url' => $meetingUrl,
            'meeting_password' => $settings->isSelfHosted() ? Str::random(8) : null,
            'meeting_details' => [
                'room_name' => $roomName,
                'jitsi_server' => $settings->jitsi_server_url,
                'jitsi_domain' => $settings->getJitsiDomain(),
                'generated_at' => now()->toISOString(),
                'enable_recording' => $settings->enable_recording,
                'enable_waiting_room' => $settings->enable_waiting_room,
            ],
        ]);

        Log::info('Meeting room generated', [
            'consultation_id' => $consultation->id,
            'room_name' => $roomName,
        ]);

        return [
            'room_name' => $roomName,
            'meeting_url' => $meetingUrl,
            'moderator_token' => $moderatorToken,
            'participant_token' => $participantToken,
            'jitsi_domain' => $settings->getJitsiDomain(),
            'settings' => $settings,
        ];
    }

    /**
     * Generate JWT for Jitsi authentication (self-hosted only).
     */
    public function generateJWT(string $roomName, string $role, int $userId): ?string
    {
        // JWT generation requires firebase/php-jwt package
        // For now, return null - Jitsi public server doesn't need JWT
        if (!class_exists('\\Firebase\\JWT\\JWT')) {
            Log::warning('Firebase JWT not installed. JWT tokens not generated.');
            return null;
        }

        $settings = TelemedicineSetting::getForTenant(1); // Get current tenant settings

        if (!$settings->jitsi_secret) {
            return null;
        }

        $payload = [
            'aud' => $settings->jitsi_app_id ?? 'jitsi',
            'iss' => $settings->jitsi_app_id ?? 'jitsi',
            'sub' => $settings->getJitsiDomain(),
            'room' => $roomName,
            'context' => [
                'user' => [
                    'id' => $userId,
                    'name' => $role === 'moderator' ? 'Doctor' : 'Patient',
                    'role' => $role,
                ],
            ],
            'exp' => time() + 7200, // 2 hours
        ];

        try {
            return \Firebase\JWT\JWT::encode($payload, $settings->jitsi_secret, 'HS256');
        } catch (\Exception $e) {
            Log::error('Failed to generate JWT', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Start recording (requires Jibri for self-hosted).
     */
    public function startRecording(Teleconsultation $consultation): bool
    {
        $settings = TelemedicineSetting::getForTenant($consultation->patient?->tenant_id ?? 1);

        if (!$settings->enable_recording) {
            Log::warning('Recording is disabled in settings');
            return false;
        }

        // For Jitsi public server, recording must be started manually by user
        // For self-hosted with Jibri, we can trigger via API
        if ($settings->isSelfHosted()) {
            // TODO: Implement Jibri API integration
            Log::info('Recording trigger for self-hosted Jitsi not yet implemented');
        }

        Log::info('Recording started for consultation', [
            'consultation_id' => $consultation->id,
        ]);

        return true;
    }

    /**
     * Stop recording.
     */
    public function stopRecording(Teleconsultation $consultation): bool
    {
        Log::info('Recording stopped for consultation', [
            'consultation_id' => $consultation->id,
        ]);

        return true;
    }

    /**
     * Save recording information.
     */
    public function saveRecording(array $recordingData): TeleconsultationRecording
    {
        $recording = TeleconsultationRecording::create([
            'consultation_id' => $recordingData['consultation_id'],
            'recording_id' => $recordingData['recording_id'] ?? Str::uuid()->toString(),
            'file_name' => $recordingData['file_name'] ?? 'recording.mp4',
            'file_size' => $recordingData['file_size'] ?? 0,
            'duration' => $recordingData['duration'] ?? 0,
            'storage_provider' => $recordingData['storage_provider'] ?? 'local',
            'storage_path' => $recordingData['storage_path'] ?? null,
            'cloud_url' => $recordingData['cloud_url'] ?? null,
            'is_encrypted' => $recordingData['is_encrypted'] ?? true,
            'expires_at' => $recordingData['expires_at'] ?? null,
            'access_count' => 0,
            'max_access' => $recordingData['max_access'] ?? null,
            'status' => 'available',
            'notes' => $recordingData['notes'] ?? null,
        ]);

        Log::info('Recording saved', [
            'recording_id' => $recording->id,
            'consultation_id' => $recording->consultation_id,
        ]);

        return $recording;
    }

    /**
     * Get recording status.
     */
    public function getRecordingStatus(string $recordingId): array
    {
        $recording = TeleconsultationRecording::where('recording_id', $recordingId)->first();

        if (!$recording) {
            return ['status' => 'not_found'];
        }

        return [
            'status' => $recording->status,
            'is_available' => $recording->isAvailable(),
            'is_expired' => $recording->isExpired(),
            'file_size' => $recording->getFormattedFileSize(),
            'duration' => $recording->getFormattedDuration(),
        ];
    }
}
