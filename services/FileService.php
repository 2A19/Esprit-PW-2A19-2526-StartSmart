<?php
class FileService {
    private static $uploadDir = 'uploads/forum/';
    private static $maxSize = 5242880; // 5MB
    private static $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
    ];

    public static function processUploads($filesArray) {
        $uploadedFiles = [];
        
        if (!isset($filesArray['name']) || empty($filesArray['name'][0])) {
            return $uploadedFiles;
        }

        $baseDir = __DIR__ . '/../' . self::$uploadDir;
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $count = is_array($filesArray['name']) ? count($filesArray['name']) : 1;
        for ($i = 0; $i < $count; $i++) {
            $tmpName = is_array($filesArray['tmp_name']) ? $filesArray['tmp_name'][$i] : $filesArray['tmp_name'];
            $originalName = is_array($filesArray['name']) ? $filesArray['name'][$i] : $filesArray['name'];
            $size = is_array($filesArray['size']) ? $filesArray['size'][$i] : $filesArray['size'];
            $error = is_array($filesArray['error']) ? $filesArray['error'][$i] : $filesArray['error'];

            if ($error !== UPLOAD_ERR_OK) continue;
            if ($size > self::$maxSize) continue;

            $mime = mime_content_type($tmpName);
            if (!array_key_exists($mime, self::$allowedMimes)) continue;

            // Generate safe filename to prevent execution
            $ext = self::$allowedMimes[$mime];
            $safeName = uniqid('file_', true) . '.' . $ext;
            $destination = $baseDir . $safeName;

            if (move_uploaded_file($tmpName, $destination)) {
                $uploadedFiles[] = [
                    'nom_fichier' => htmlspecialchars($originalName),
                    'chemin_fichier' => self::$uploadDir . $safeName,
                    'type_fichier' => $mime,
                    'taille_fichier' => $size
                ];
            }
        }

        return $uploadedFiles;
    }
}
?>
