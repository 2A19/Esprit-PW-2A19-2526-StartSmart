@echo off
REM Face Recognition Installation Script for Windows
REM This script installs all required Python packages

echo.
echo ===================================
echo Face Recognition Setup for Windows
echo ===================================
echo.

REM Python 3.11 path
set PYTHON311="C:\Users\moham\AppData\Local\Programs\Python\Python311\python.exe"

echo [Step 1] Checking Python 3.11 installation...
%PYTHON311% --version
if errorlevel 1 (
    echo ERROR: Python 3.11 not found at expected location
    echo Please update the PYTHON311 path in this script
    pause
    exit /b 1
)
echo ✓ Python 3.11 found
echo.

echo [Step 2] Installing numpy...
%PYTHON311% -m pip install numpy
if errorlevel 1 (
    echo ERROR: Failed to install numpy
    pause
    exit /b 1
)
echo ✓ numpy installed
echo.

echo [Step 3] Installing Pillow...
%PYTHON311% -m pip install Pillow
if errorlevel 1 (
    echo ERROR: Failed to install Pillow
    pause
    exit /b 1
)
echo ✓ Pillow installed
echo.

echo [Step 4] Installing dlib (required for face_recognition)...
echo Note: This may take a few minutes...
%PYTHON311% -m pip install dlib
if errorlevel 1 (
    echo ERROR: Failed to install dlib
    echo.
    echo IMPORTANT: dlib requires C++ build tools
    echo Please install Visual C++ Build Tools or Visual Studio
    echo Download from: https://visualstudio.microsoft.com/downloads/
    echo Then try again
    pause
    exit /b 1
)
echo ✓ dlib installed
echo.

echo [Step 5] Installing face_recognition...
echo Note: This is the main package and may take several minutes...
%PYTHON311% -m pip install face-recognition
if errorlevel 1 (
    echo ERROR: Failed to install face-recognition
    pause
    exit /b 1
)
echo ✓ face_recognition installed
echo.

echo [Step 6] Verifying installation...
%PYTHON311% -c "import face_recognition; import PIL; import numpy; print('SUCCESS: All packages installed correctly!')"
if errorlevel 1 (
    echo ERROR: Verification failed
    pause
    exit /b 1
)
echo ✓ Verification successful!
echo.

echo ===================================
echo Installation Complete!
echo ===================================
echo.
echo Next steps:
echo 1. Apply database migration:
echo    mysql -u root startsmart_db ^< migrate_add_face_recognition.sql
echo.
echo 2. Test the Python service:
echo    python python\face_recognition_service.py help
echo.
echo 3. Update face_recognition.js to use correct Python path:
echo    Change Python command to: C:\Users\moham\AppData\Local\Programs\Python\Python311\python.exe
echo.
pause
