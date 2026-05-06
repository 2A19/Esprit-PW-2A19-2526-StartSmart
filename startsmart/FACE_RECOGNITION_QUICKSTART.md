# Face Recognition - Quick Start Guide

## Installation (5 minutes)

### Step 1: Install Python Dependencies
```bash
# Windows
pip install face-recognition pillow numpy

# macOS
brew install python3  # if needed
pip3 install face-recognition pillow numpy

# Linux
sudo apt-get install python3-pip
pip3 install face-recognition pillow numpy
```

### Step 2: Update Database
Run this SQL migration in phpMyAdmin or MySQL:
```sql
ALTER TABLE users
ADD COLUMN IF NOT EXISTS face_encoding LONGBLOB DEFAULT NULL,
ADD COLUMN IF NOT EXISTS face_recognition_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS face_setup_date DATETIME DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_face_enabled ON users(face_recognition_enabled);
```

### Step 3: Verify Python Service
Test the Python script:
```bash
# Navigate to your project directory
cd c:\xampp\htdocs\startsmart

# Test the Python service (replace with your image path)
python3 python/face_recognition_service.py encode path/to/test/image.jpg
```

You should see JSON output with face encoding.

## Usage

### For End Users

#### Setup Face Recognition (in Profile)
1. Log in to StartSmart
2. Go to Profile → Reconnaissance Faciale
3. Click "Utiliser la caméra" or "Importer une photo"
4. Allow camera access (if using webcam)
5. Submit your face image
6. Enable "Connexion par reconnaissance faciale"

#### Login with Face
1. Go to login page
2. Click "👤 Visage" tab
3. Enter email and select role
4. Click "📷 Activer caméra"
5. Position your face and click "📸 Vérifier mon visage"
6. You're logged in!

## File Structure

```
startsmart/
├── api/face/
│   ├── setup.php              ← Handle face registration
│   └── verify.php             ← Verify face during login
├── python/
│   ├── face_recognition_service.py  ← Python ML service
│   └── requirements.txt
├── public/js/
│   └── face-recognition.js    ← Client-side JavaScript
├── views/
│   ├── auth/login.php         ← Updated with face tab
│   └── front/dashboard.php    ← Updated with face section
├── migrate_add_face_recognition.sql  ← Database migration
└── FACE_RECOGNITION_SETUP.md  ← Full documentation
```

## Testing Checklist

- [ ] Python dependencies installed
- [ ] Database migration applied
- [ ] Can access login page with face tab
- [ ] Can access profile with face recognition section
- [ ] Can upload face image
- [ ] Can enable face recognition
- [ ] Can login with face recognition

## Common Issues

| Issue | Solution |
|-------|----------|
| "Module not found" | Run: `pip install face-recognition` |
| "No face detected" | Ensure face is clear, centered, well-lit |
| Camera not working | Allow camera permissions in browser |
| "Python script error" | Check Python is installed: `python3 --version` |

## Next Steps

1. Read [FACE_RECOGNITION_SETUP.md](FACE_RECOGNITION_SETUP.md) for detailed documentation
2. Test with real users
3. Monitor face recognition success rates
4. Adjust tolerance (0.6) if needed for your users
5. Consider security implications

## API Reference

- **Setup**: `POST /api/face/setup.php?action=upload_face`
- **Enable**: `POST /api/face/setup.php?action=enable`
- **Disable**: `POST /api/face/setup.php?action=disable`
- **Status**: `GET /api/face/setup.php?action=status`
- **Verify**: `POST /api/face/verify.php` (for login)

See FACE_RECOGNITION_SETUP.md for full API documentation.

## Support Files

- `python/face_recognition_service.py` - Face encoding/comparison
- `public/js/face-recognition.js` - Webcam and API handling
- `api/face/setup.php` - Face registration and management
- `api/face/verify.php` - Face verification for login

All files are production-ready and documented.
