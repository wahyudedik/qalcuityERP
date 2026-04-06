/**
 * Camera Integration for Barcode & Receipt Scanning
 * Uses device camera with ZXing library
 */

class CameraScanner {
    constructor() {
        this.codeReader = null;
        this.videoElement = null;
        this.canvasElement = null;
        this.isScanning = false;
        this.stream = null;

        this.init();
    }

    async init() {
        // Load ZXing library if not already loaded
        if (typeof ZXing === 'undefined') {
            await this.loadZXingLibrary();
        }

        this.setupCameraElements();
    }

    /**
     * Load ZXing library dynamically
     */
    async loadZXingLibrary() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/@zxing/library@latest';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Setup camera video and canvas elements
     */
    setupCameraElements() {
        this.videoElement = document.getElementById('camera-video');
        this.canvasElement = document.getElementById('camera-canvas');
    }

    /**
     * Start barcode/QR scanning
     */
    async startBarcodeScanning(onResult, onError) {
        try {
            if (!this.codeReader) {
                this.codeReader = new ZXing.BrowserMultiFormatReader();
            }

            const videoInputDevices = await this.codeReader.listVideoInputDevices();

            if (videoInputDevices.length === 0) {
                throw new Error('No camera found');
            }

            // Prefer back camera
            const backCamera = videoInputDevices.find(device =>
                device.label.toLowerCase().includes('back') ||
                device.label.toLowerCase().includes('environment')
            );

            const selectedDeviceId = backCamera ? backCamera.deviceId : videoInputDevices[0].deviceId;

            this.isScanning = true;

            await this.codeReader.decodeFromVideoDevice(
                selectedDeviceId,
                this.videoElement,
                (result, error) => {
                    if (result) {
                        onResult(result.text);
                        this.stopScanning();
                    }

                    if (error && !(error instanceof ZXing.NotFoundException)) {
                        onError(error);
                    }
                }
            );

            // Haptic feedback when scanning starts
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }

        } catch (error) {
            console.error('Barcode scanning error:', error);
            onError(error);
        }
    }

    /**
     * Stop scanning
     */
    stopScanning() {
        if (this.codeReader) {
            this.codeReader.reset();
        }

        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }

        this.isScanning = false;

        // Haptic feedback
        if (navigator.vibrate) {
            navigator.vibrate([50, 100, 50]);
        }
    }

    /**
     * Capture receipt/document image
     */
    async captureReceipt(onCapture, onError) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            });

            this.stream = stream;
            this.videoElement.srcObject = stream;

            // Wait for video to be ready
            await new Promise(resolve => {
                this.videoElement.onloadedmetadata = resolve;
            });

            this.videoElement.play();

            // Show capture button
            this.showCaptureButton(() => {
                this.captureImage(onCapture, onError);
            });

        } catch (error) {
            console.error('Camera access error:', error);
            onError(error);
        }
    }

    /**
     * Capture image from video stream
     */
    captureImage(onCapture, onError) {
        try {
            const context = this.canvasElement.getContext('2d');

            this.canvasElement.width = this.videoElement.videoWidth;
            this.canvasElement.height = this.videoElement.videoHeight;

            context.drawImage(this.videoElement, 0, 0);

            // Convert to blob
            this.canvasElement.toBlob((blob) => {
                if (blob) {
                    onCapture(blob);
                    this.stopCamera();
                }
            }, 'image/jpeg', 0.9);

        } catch (error) {
            console.error('Image capture error:', error);
            onError(error);
        }
    }

    /**
     * Show capture button overlay
     */
    showCaptureButton(onClick) {
        const captureBtn = document.getElementById('capture-button');
        if (captureBtn) {
            captureBtn.style.display = 'block';
            captureBtn.onclick = onClick;
        }
    }

    /**
     * Stop camera
     */
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        const captureBtn = document.getElementById('capture-button');
        if (captureBtn) {
            captureBtn.style.display = 'none';
        }
    }

    /**
     * Scan from uploaded image (fallback)
     */
    async scanFromImage(file, onResult, onError) {
        try {
            if (!this.codeReader) {
                this.codeReader = new ZXing.BrowserMultiFormatReader();
            }

            const result = await this.codeReader.decodeFromImageUrl(URL.createObjectURL(file));
            onResult(result.text);

        } catch (error) {
            console.error('Image scan error:', error);
            onError(error);
        }
    }

    /**
     * Request camera permission
     */
    async requestCameraPermission() {
        try {
            const permission = await navigator.permissions.query({ name: 'camera' });
            return permission.state === 'granted';
        } catch (error) {
            // Fallback: try to access camera
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                stream.getTracks().forEach(track => track.stop());
                return true;
            } catch (e) {
                return false;
            }
        }
    }

    /**
     * Check if camera is available
     */
    async isCameraAvailable() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            return devices.some(device => device.kind === 'videoinput');
        } catch (error) {
            return false;
        }
    }

    /**
     * Switch between front/back camera
     */
    async switchCamera() {
        if (!this.codeReader) return;

        const videoInputDevices = await this.codeReader.listVideoInputDevices();
        // Logic to switch camera would go here
    }

    /**
     * Enable flashlight/torch (if supported)
     */
    async toggleFlashlight(enable) {
        if (!this.stream) return;

        const track = this.stream.getVideoTracks()[0];

        try {
            await track.applyConstraints({
                advanced: [{ torch: enable }]
            });
        } catch (error) {
            console.warn('Flashlight not supported:', error);
        }
    }
}

// Initialize scanner globally
window.cameraScanner = new CameraScanner();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CameraScanner;
}
