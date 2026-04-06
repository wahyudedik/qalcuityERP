<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\CctvIntegrationService;
use Illuminate\Http\Request;

class CctvController extends Controller
{
    protected $cctvService;

    public function __construct(CctvIntegrationService $cctvService)
    {
        $this->cctvService = $cctvService;
    }

    /**
     * CCTV Dashboard
     */
    public function index()
    {
        $cameras = $this->cctvService->getAllCameras();

        return view('security.cctv.dashboard', compact('cameras'));
    }

    /**
     * View single camera
     */
    public function viewCamera(int $cameraId)
    {
        $stream = $this->cctvService->getLiveStream($cameraId);
        $status = $this->cctvService->getCameraStatus($cameraId);

        return view('security.cctv.camera', compact('stream', 'status', 'cameraId'));
    }

    /**
     * Take snapshot
     */
    public function takeSnapshot(int $cameraId)
    {
        $result = $this->cctvService->takeSnapshot($cameraId);

        return response()->json($result);
    }

    /**
     * View recordings
     */
    public function recordings(Request $request)
    {
        $cameraId = $request->input('camera_id');
        $startTime = $request->input('start_time', now()->subHours(1)->toDateTimeString());
        $endTime = $request->input('end_time', now()->toDateTimeString());

        $recordings = $this->cctvService->getRecording($cameraId, $startTime, $endTime);

        return view('security.cctv.recordings', compact('recordings', 'cameraId'));
    }

    /**
     * Detect motion
     */
    public function detectMotion(int $cameraId)
    {
        $result = $this->cctvService->detectMotion($cameraId);

        return response()->json($result);
    }
}
