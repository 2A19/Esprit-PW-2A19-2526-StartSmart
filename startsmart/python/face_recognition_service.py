#!/usr/bin/env python3
"""
Face Recognition Service for StartSmart
Uses OpenCV LBPH (Local Binary Patterns Histograms) for actual face recognition.
LBPH is lighting-tolerant, rotation-tolerant, and distinguishes between people.
"""

import sys
import json
import os
import base64

try:
    import cv2
    import numpy as np
except ImportError:
    print(json.dumps({
        "success": False,
        "error": "Missing dependencies. Install with: pip install opencv-python numpy"
    }))
    sys.exit(1)

CASCADE_PATH = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
face_cascade = cv2.CascadeClassifier(CASCADE_PATH)

# LBPH confidence threshold — lower = stricter match
# Typical values: <50 = same person, >80 = different person
CONFIDENCE_THRESHOLD = 55.0


def detect_and_crop_face(image: np.ndarray):
    """Detect face in image, return cropped grayscale face or None."""
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    # Try multiple scale factors for better detection
    for scale in [1.1, 1.2, 1.3]:
        faces = face_cascade.detectMultiScale(
            gray,
            scaleFactor=scale,
            minNeighbors=5,
            minSize=(60, 60),
            flags=cv2.CASCADE_SCALE_IMAGE
        )
        if len(faces) > 0:
            break

    if len(faces) == 0:
        return None, "Aucun visage détecté. Assurez-vous que votre visage est bien visible et éclairé."

    if len(faces) > 1:
        return None, "Plusieurs visages détectés. Utilisez une image avec un seul visage."

    (x, y, w, h) = faces[0]

    # Add 20% padding around the face
    pad = int(0.2 * min(w, h))
    x1 = max(0, x - pad)
    y1 = max(0, y - pad)
    x2 = min(image.shape[1], x + w + pad)
    y2 = min(image.shape[0], y + h + pad)

    face_crop = gray[y1:y2, x1:x2]
    if face_crop.size == 0:
        return None, "Région du visage invalide."

    # Normalize size and apply histogram equalization for lighting robustness
    face_resized = cv2.resize(face_crop, (200, 200))
    face_equalized = cv2.equalizeHist(face_resized)

    return face_equalized, None


def extract_lbph_histogram(face_gray: np.ndarray) -> list:
    """
    Extract LBPH histogram from a grayscale face image.
    This is what makes faces distinguishable regardless of lighting.
    """
    # LBP parameters
    radius = 1
    n_points = 8 * radius
    grid_x = 8
    grid_y = 8

    h, w = face_gray.shape
    cell_h = h // grid_y
    cell_w = w // grid_x

    histogram = []

    for gy in range(grid_y):
        for gx in range(grid_x):
            # Extract cell
            y_start = gy * cell_h
            x_start = gx * cell_w
            cell = face_gray[y_start:y_start+cell_h, x_start:x_start+cell_w]

            # Compute LBP for this cell
            lbp = np.zeros_like(cell, dtype=np.uint8)
            center = cell[1:-1, 1:-1]

            # 8 neighbors
            neighbors = [
                cell[0:-2, 0:-2], cell[0:-2, 1:-1], cell[0:-2, 2:],
                cell[1:-1, 2:],   cell[2:,   2:],   cell[2:,   1:-1],
                cell[2:,   0:-2], cell[1:-1, 0:-2],
            ]

            lbp_center = np.zeros(center.shape, dtype=np.uint8)
            for i, neighbor in enumerate(neighbors):
                lbp_center += ((neighbor >= center).astype(np.uint8)) << i

            # Compute histogram for this cell (256 bins)
            hist, _ = np.histogram(lbp_center.flatten(), bins=256, range=(0, 256))
            # Normalize
            hist = hist.astype(np.float32)
            norm = np.sum(hist)
            if norm > 0:
                hist /= norm
            histogram.extend(hist.tolist())

    return histogram


def chi_square_distance(h1: list, h2: list) -> float:
    """
    Chi-square distance between two histograms.
    Better than Euclidean/cosine for histogram comparison.
    Returns 0 for identical histograms, higher = more different.
    """
    a = np.array(h1, dtype=np.float64)
    b = np.array(h2, dtype=np.float64)
    denom = a + b
    mask = denom > 0
    diff = (a[mask] - b[mask]) ** 2
    dist = float(np.sum(diff / denom[mask]))
    return dist


def extract_embedding(image_path: str) -> dict:
    """Load image, detect face, return LBPH histogram embedding."""
    if not os.path.exists(image_path):
        return {"success": False, "error": "Image file not found: " + image_path}

    image = cv2.imread(image_path)
    if image is None:
        return {"success": False, "error": "Failed to load image (unsupported format or corrupt file)"}

    face, error = detect_and_crop_face(image)
    if face is None:
        return {"success": False, "error": error}

    embedding = extract_lbph_histogram(face)

    return {
        "success": True,
        "embedding": embedding,
        "embedding_size": len(embedding),
        "message": "Visage détecté et encodé avec succès"
    }


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "Usage: python face_recognition_service.py <command> [args]"}))
        sys.exit(1)

    command = sys.argv[1]

    if command == "encode":
        if len(sys.argv) < 3:
            print(json.dumps({"success": False, "error": "Image path required"}))
            sys.exit(1)
        result = extract_embedding(sys.argv[2])
        print(json.dumps(result))

    elif command == "compare":
        if len(sys.argv) < 4:
            print(json.dumps({"success": False, "error": "Two image paths required"}))
            sys.exit(1)
        r1 = extract_embedding(sys.argv[2])
        if not r1["success"]:
            print(json.dumps(r1)); sys.exit(1)
        r2 = extract_embedding(sys.argv[3])
        if not r2["success"]:
            print(json.dumps(r2)); sys.exit(1)

        dist = chi_square_distance(r1["embedding"], r2["embedding"])
        is_match = dist < CONFIDENCE_THRESHOLD
        print(json.dumps({
            "success": True,
            "match": is_match,
            "distance": dist,
            "threshold": CONFIDENCE_THRESHOLD
        }))

    elif command == "help":
        print(json.dumps({"commands": {"encode": "encode <image>", "compare": "compare <img1> <img2>"}}))

    else:
        print(json.dumps({"success": False, "error": "Unknown command: " + command}))
        sys.exit(1)


if __name__ == "__main__":
    main()
