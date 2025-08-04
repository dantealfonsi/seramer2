<?php
// app/utils/FileUpload.php

class FileUpload {
    // La carpeta de destino debe existir
    private const UPLOAD_DIR = __DIR__ . '/../public/uploads/';
    
    // Mimes permitidos (puedes añadir más si es necesario)
    private const ALLOWED_MIMES = [
        'image/jpeg', 
        'image/png', 
        'video/mp4',
        'video/quicktime' // Para archivos .mov
    ];

    public static function upload($file, $folder) {
        $errors = [];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        
        // Validar el tipo MIME del archivo
        $file_type = mime_content_type($file_tmp);
        if (!in_array($file_type, self::ALLOWED_MIMES)) {
            $errors[] = "Tipo de archivo no permitido. Los formatos aceptados son: JPEG, PNG, MP4, MOV.";
        }
        
        // Validar el tamaño del archivo (ejemplo: 10MB máximo)
        $max_size = 10 * 1024 * 1024;
        if ($file_size > $max_size) {
            $errors[] = "El tamaño del archivo no puede exceder los 10MB.";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        $target_dir = self::UPLOAD_DIR . $folder . '/';
        
        // Crear la carpeta si no existe
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generar un nombre de archivo único para evitar colisiones
        $new_file_name = uniqid('proof_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $destination = $target_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            return ['success' => true, 'file_name' => $new_file_name];
        } else {
            return ['success' => false, 'message' => 'Error al mover el archivo subido.'];
        }
    }

    public static function delete($folder, $file_name) {
        $file_path = self::UPLOAD_DIR . $folder . '/' . $file_name;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
}