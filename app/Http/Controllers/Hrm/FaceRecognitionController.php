<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;

class FaceRecognitionController extends Controller
{
    protected $faceService;

    public function __construct(FaceRecognitionService $faceService)
    {
        $this->faceService = $faceService;
    }

    /**
     * Face Recognition Dashboard
     */
    public function index()
    {
        $employees = Employee::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('hrm.face-recognition.index', compact('employees'));
    }

    /**
     * Register employee face
     */
    public function registerFace(Request $request, Employee $employee)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
        ]);

        $imagePath = $request->file('image')->store('face-recognition', 'public');
        $fullPath = storage_path('app/public/'.$imagePath);

        $result = $this->faceService->registerFace($employee->id, $fullPath);

        return response()->json($result);
    }

    /**
     * Scan attendance via face
     */
    public function scanAttendance(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
            'scan_type' => 'nullable|in:check_in,check_out',
        ]);

        $imagePath = $request->file('image')->store('face-scans', 'public');
        $fullPath = storage_path('app/public/'.$imagePath);

        $scanType = $request->input('scan_type', 'check_in');
        $result = $this->faceService->processAttendance($fullPath, $scanType);

        return response()->json($result);
    }

    /**
     * Capture from camera
     */
    public function captureFromCamera()
    {
        $cameraIndex = request()->input('camera_index', 0);
        $result = $this->faceService->captureFromCamera($cameraIndex);

        return response()->json($result);
    }

    /**
     * Remove face data
     */
    public function removeFace(Employee $employee)
    {
        $success = $this->faceService->removeFace($employee->id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Face data removed' : 'Failed to remove face data',
        ]);
    }
}
