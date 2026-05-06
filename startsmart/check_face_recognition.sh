#!/bin/bash
# Face Recognition Installation Checker
# Run this script to verify all components are installed correctly

echo "=== Face Recognition Installation Check ==="
echo ""

# Check 1: Python Installation
echo "[1/6] Checking Python installation..."
if command -v python3 &> /dev/null; then
    VERSION=$(python3 --version)
    echo "✓ Python installed: $VERSION"
else
    echo "✗ Python3 not found. Install from https://www.python.org/"
    exit 1
fi

echo ""
echo "[2/6] Checking Python packages..."

# Check face_recognition
python3 -c "import face_recognition" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✓ face_recognition installed"
else
    echo "✗ face_recognition not installed"
    echo "  Run: pip3 install face-recognition"
fi

# Check pillow
python3 -c "import PIL" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✓ Pillow installed"
else
    echo "✗ Pillow not installed"
    echo "  Run: pip3 install pillow"
fi

# Check numpy
python3 -c "import numpy" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✓ numpy installed"
else
    echo "✗ numpy not installed"
    echo "  Run: pip3 install numpy"
fi

echo ""
echo "[3/6] Checking project files..."

# Check required files
FILES=(
    "python/face_recognition_service.py"
    "python/requirements.txt"
    "api/face/setup.php"
    "api/face/verify.php"
    "public/js/face-recognition.js"
    "views/auth/login.php"
    "views/front/dashboard.php"
    "migrate_add_face_recognition.sql"
)

MISSING=0
for FILE in "${FILES[@]}"; do
    if [ -f "$FILE" ]; then
        echo "✓ $FILE"
    else
        echo "✗ $FILE (missing)"
        MISSING=$((MISSING + 1))
    fi
done

if [ $MISSING -eq 0 ]; then
    echo "✓ All required files present"
else
    echo "✗ $MISSING file(s) missing"
fi

echo ""
echo "[4/6] Testing Python service..."

# Test Python script
python3 python/face_recognition_service.py help > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✓ Python service is accessible"
else
    echo "✗ Python service error - check python/face_recognition_service.py"
fi

echo ""
echo "[5/6] Checking PHP files..."

# Check if PHP files are readable
if [ -r "api/face/setup.php" ] && [ -r "api/face/verify.php" ]; then
    echo "✓ PHP files are readable"
else
    echo "✗ PHP files not readable - check permissions"
fi

echo ""
echo "[6/6] Database check..."

# Check if migration file exists and is readable
if [ -r "migrate_add_face_recognition.sql" ]; then
    echo "✓ Migration file ready"
    echo "  Run in MySQL/phpMyAdmin to apply:"
    echo "  mysql -u root startsmart_db < migrate_add_face_recognition.sql"
else
    echo "✗ Migration file not found"
fi

echo ""
echo "=== Installation Check Complete ==="
echo ""
echo "Next steps:"
echo "1. If any ✗ items exist above, fix them"
echo "2. Apply database migration: mysql -u root startsmart_db < migrate_add_face_recognition.sql"
echo "3. Test by logging in and visiting Profile > Reconnaissance Faciale"
echo ""
echo "For more info, see FACE_RECOGNITION_SETUP.md"
