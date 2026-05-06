/**
 * Face Recognition Module
 * Handles webcam capture and face recognition UI interactions
 */

class FaceRecognitionModule {
    constructor() {
        this.video = null;
        this.canvas = null;
        this.stream = null;
        this.isCameraActive = false;
    }

    /**
     * Initialize webcam video element
     */
    async initCamera(videoElementId) {
        try {
            this.video = document.getElementById(videoElementId);
            if (!this.video) {
                throw new Error('Video element not found');
            }

            // Request camera access
            const constraints = {
                video: { 
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            };

            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.video.srcObject = this.stream;
            this.isCameraActive = true;

            // Wait for video to be ready
            return new Promise((resolve) => {
                this.video.onloadedmetadata = () => {
                    this.video.play();
                    resolve(true);
                };
            });

        } catch (error) {
            console.error('Camera access error:', error);
            throw new Error('Unable to access camera: ' + error.message);
        }
    }

    /**
     * Stop the webcam
     */
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.isCameraActive = false;
        }
    }

    /**
     * Capture frame from video and convert to canvas
     */
    captureFrame() {
        if (!this.video || !this.isCameraActive) {
            throw new Error('Camera is not active');
        }

        // Create canvas from video frame
        const canvas = document.createElement('canvas');
        canvas.width = this.video.videoWidth;
        canvas.height = this.video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.video, 0, 0);

        return canvas;
    }

    /**
     * Convert canvas to blob for upload
     */
    canvasToBlob(canvas) {
        return new Promise((resolve, reject) => {
            canvas.toBlob(blob => {
                if (blob) resolve(blob);
                else reject(new Error('Failed to create blob'));
            }, 'image/jpeg', 0.9);
        });
    }

    /**
     * Safe fetch — handles non-JSON responses, timeouts, and PHP warnings
     * Never hangs indefinitely; always resolves or throws a clean Error
     */
    async safeFetch(url, options = {}, timeoutMs = 60000) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeoutMs);
        try {
            const response = await fetch(url, { ...options, signal: controller.signal });
            clearTimeout(timer);
            const text = await response.text();
            // Skip any PHP warnings/notices printed before the JSON
            const jsonStart = text.indexOf('{');
            if (jsonStart === -1) {
                throw new Error('Réponse serveur invalide: ' + text.slice(0, 300));
            }
            let data;
            try { data = JSON.parse(text.slice(jsonStart)); }
            catch (e) { throw new Error('Réponse non-JSON: ' + text.slice(jsonStart, jsonStart + 200)); }
            if (!data.success) {
                throw new Error(data.error || 'Erreur serveur (' + response.status + ')');
            }
            return data;
        } catch (err) {
            clearTimeout(timer);
            if (err.name === 'AbortError') {
                throw new Error('Délai dépassé — vérifiez que Python et opencv-python sont installés');
            }
            throw err;
        }
    }

    /**
     * Capture face from camera and send to server for setup
     */
    async captureAndSetupFace() {
        if (!this.isCameraActive) throw new Error('Camera not active');
        const canvas = this.captureFrame();
        const blob = await this.canvasToBlob(canvas);
        const formData = new FormData();
        formData.append('face_image', blob, 'face.jpg');
        return this.safeFetch('/startsmart/api/face/setup.php?action=upload_face', {
            method: 'POST', body: formData
        });
    }

    /**
     * Capture face for login verification
     */
    async captureAndVerifyFace(email, role) {
        if (!this.isCameraActive) throw new Error('Camera not active');
        const canvas = this.captureFrame();
        const blob = await this.canvasToBlob(canvas);
        const formData = new FormData();
        formData.append('face_image', blob, 'face.jpg');
        formData.append('email', email);
        formData.append('role', role);
        return this.safeFetch('/startsmart/api/face/verify.php', {
            method: 'POST', body: formData
        });
    }

    /**
     * Upload image file for face recognition
     */
    async uploadFaceImage(file) {
        const formData = new FormData();
        formData.append('face_image', file);
        return this.safeFetch('/startsmart/api/face/setup.php?action=upload_face', {
            method: 'POST', body: formData
        });
    }

    /**
     * Enable face recognition for user
     */
    async enableFaceRecognition() {
        return this.safeFetch('/startsmart/api/face/setup.php?action=enable', { method: 'POST' });
    }

    /**
     * Disable face recognition for user
     */
    async disableFaceRecognition() {
        return this.safeFetch('/startsmart/api/face/setup.php?action=disable', { method: 'POST' });
    }

    /**
     * Get face recognition status for current user
     */
    async getFaceStatus() {
        return this.safeFetch('/startsmart/api/face/setup.php?action=status');
    }
}

// Initialize globally if needed
const faceRecognition = new FaceRecognitionModule();
