# Face Recognition Integration Guide

## Overview
This guide explains how to set up and use the face recognition feature in your StartSmart application.

## What's Included

The face recognition integration adds:
- **Face Registration**: Users can register their face via webcam or photo upload
- **Face-based Login**: Users can login using face recognition instead of password
- **Profile Management**: Users can enable/disable face recognition in their profile
- **Secure Encoding**: Face encodings are stored safely in the database

## Installation Steps

### 1. Prerequisites
- PHP 7.4+
- Python 3.6+
- Webcam (optional, but required for webcam capture)
- XAMPP with MySQL

### 2. Python Dependencies

First, install the required Python packages:

```bash
# Windows
pip install -r python/requirements.txt

# macOS/Linux
pip3 install -r python/requirements.txt
```

**Required packages:**
- `face_recognition` - Core face recognition library
- `pillow` - Image processing
- `numpy` - Numerical computing

**Note**: The `face_recognition` library requires:
- dlib (installed automatically with face_recognition)
- cmake (may need to be installed separately on some systems)

### 3. Database Migration

Run the database migration to add face recognition columns:

```bash
# Via MySQL CLI:
mysql -u root startsmart_db < migrate_add_face_recognition.sql

# Or via phpMyAdmin:
1. Go to phpMyAdmin
2. Select startsmart_db
3. Click "Import"
4. Select migrate_add_face_recognition.sql
5. Click "Import"
```

This adds three columns to the `users` table:
- `face_encoding` (LONGBLOB): Stores the face encoding vector
- `face_recognition_enabled` (BOOLEAN): Whether face login is enabled
- `face_setup_date` (DATETIME): When the face was registered

### 4. File Structure

New files created:

```
startsmart/
├── api/face/
│   ├── setup.php       # Setup and manage face recognition
│   └── verify.php      # Verify face during login
├── python/
│   ├── face_recognition_service.py  # Python service for face processing
│   └── requirements.txt              # Python dependencies
├── public/js/
│   └── face-recognition.js          # Client-side face recognition module
└── views/
    ├── auth/login.php               # Updated with face login tab
    └── front/dashboard.php          # Updated with face setup section
```

### 5. Configuration

No additional configuration needed! The system uses default settings:
- **Face tolerance**: 0.6 (distance threshold for face matching)
- **Max file size**: 10MB for uploaded images
- **Supported formats**: JPEG, PNG

## How to Use

### For Users: Setting Up Face Recognition

1. **Go to Profile**
   - Log in to your account
   - Navigate to "Mon Profil" section
   - Scroll to "Reconnaissance Faciale"

2. **Upload or Capture Face**
   - Choose "Importer une photo" or "Utiliser la caméra"
   - Ensure your face is clearly visible
   - Submit the image

3. **Enable Face Login**
   - Once registered, toggle "Connexion par reconnaissance faciale"
   - Now you can login with your face!

4. **Manage Settings**
   - Re-register: Click "Réenregistrer mon visage"
   - Disable: Click "Supprimer" (doesn't delete, just disables)

### For Users: Logging In with Face

1. **Go to Login Page**
   - Navigate to the login page
   - Click the "👤 Visage" tab

2. **Provide Information**
   - Enter your email
   - Select your role (User, Startup, Admin)

3. **Scan Your Face**
   - Click "📷 Activer caméra"
   - Allow camera access
   - Position your face in the frame
   - Click "📸 Vérifier mon visage"

4. **Success**
   - If face matches, you'll be logged in
   - If not, you can retry or use password login

## API Endpoints

### 1. Setup Face Recognition
**POST** `/startsmart/api/face/setup.php?action=upload_face`

Upload and encode a user's face.

**Request:**
```
Content-Type: multipart/form-data
face_image: [image file, JPEG or PNG]
```

**Response:**
```json
{
  "success": true,
  "message": "Face successfully registered...",
  "setup_complete": true
}
```

### 2. Enable Face Recognition
**POST** `/startsmart/api/face/setup.php?action=enable`

Enable face recognition login for the user.

### 3. Disable Face Recognition
**POST** `/startsmart/api/face/setup.php?action=disable`

Disable face recognition login.

### 4. Get Face Status
**GET** `/startsmart/api/face/setup.php?action=status`

Get current face recognition status.

**Response:**
```json
{
  "success": true,
  "enabled": false,
  "setup_complete": true,
  "setup_date": "2024-01-15 10:30:00"
}
```

### 5. Verify Face for Login
**POST** `/startsmart/api/face/verify.php`

Verify a face image during login.

**Request:**
```
Content-Type: multipart/form-data
face_image: [image file]
email: [user email]
role: [user|startup|admin]
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Face recognized successfully",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "role": "user",
    "name": "Ahmed Ben Ali"
  }
}
```

**Response (Failure):**
```json
{
  "success": false,
  "error": "Face does not match. Authentication failed."
}
```

## JavaScript Module: FaceRecognitionModule

The `face-recognition.js` file provides a client-side module for handling face recognition operations.

### Key Methods

```javascript
// Initialize camera
await faceRecognition.initCamera('video-element-id');

// Stop camera
faceRecognition.stopCamera();

// Capture and setup face (for profile)
await faceRecognition.captureAndSetupFace();

// Capture and verify face (for login)
await faceRecognition.captureAndVerifyFace(email, role);

// Upload face image
await faceRecognition.uploadFaceImage(file);

// Enable face recognition
await faceRecognition.enableFaceRecognition();

// Disable face recognition
await faceRecognition.disableFaceRecognition();

// Get face status
await faceRecognition.getFaceStatus();
```

## Security Considerations

1. **Encoding Storage**: Face encodings are stored as JSON in the database
2. **No Face Images Stored**: Only the mathematical encoding is stored, not the actual image
3. **Distance Tolerance**: Set to 0.6 for good balance between security and usability
4. **Temporary Files**: Uploaded images are processed and deleted after encoding
5. **Session-Based Auth**: Face recognition uses standard PHP sessions for security

## Troubleshooting

### Issue: "No face detected in image"
- **Solution**: Ensure face is clearly visible, well-lit, and centered
- Make sure the image is JPEG or PNG format

### Issue: "Multiple faces detected"
- **Solution**: Use an image with only one face
- Face should be the main subject of the image

### Issue: "Face does not match during login"
- **Solution**: Faces may change due to lighting, angle, or appearance
- Try re-registering your face from profile
- Adjust distance tolerance if needed

### Issue: Camera access denied
- **Solution**: Allow camera permissions in browser settings
- Check browser's privacy settings
- Use HTTPS (some browsers require it for camera access)

### Issue: Python script errors
- **Solution**: Verify Python installation: `python3 --version`
- Check dependencies: `pip list | grep face-recognition`
- Ensure Python path is correct in the system

## Performance Notes

- Face encoding takes ~0.5-1.5 seconds per image
- Face comparison is very fast (<50ms)
- First load of face_recognition library takes longer (face model loading)

## Future Enhancements

Possible improvements:
- Multi-face support (multiple registered faces)
- Liveness detection (prevent spoofing with photos)
- Face quality assessment before registration
- Failed login attempt tracking
- Adaptive tolerance (user-specific thresholds)
- Admin dashboard for face recognition management

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the API endpoint responses
3. Check browser console for JavaScript errors
4. Verify Python service is running correctly
5. Check application logs

## License & Credits

- **face_recognition**: Built on top of dlib's state-of-the-art face recognition
- **Python Libraries**: PIL, numpy for image processing
- **StartSmart**: Integration by your development team
