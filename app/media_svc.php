<?php
// includes/media_svc.php

class MediaService
{
    private $upload_dir;
    private $db;

    public function __construct()
    {
        $this->upload_dir = __DIR__ . '/../public/uploads/';
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
        $this->db = DB::getInstance()->getConnection();
    }

    /**
     * Handle single file upload and compress
     */
    public function uploadImage($file, $alt_text, $folder = 'general', $uploader_id = null)
    {
        $error = $this->validateUpload($file);
        if ($error)
            return ['success' => false, 'error' => $error];

        if (empty(trim($alt_text))) {
            return ['success' => false, 'error' => 'Alt text is required for SEO.'];
        }

        // Generate unique name
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Normalize JPEG
        if ($ext === 'jpg')
            $ext = 'jpeg';

        $filename = uniqid('img_') . '_' . time() . '.' . $ext;

        $folder_dir = $this->upload_dir . $folder . '/';
        if (!is_dir($folder_dir)) {
            mkdir($folder_dir, 0755, true);
        }

        $filepath = $folder_dir . $filename;

        // Compress and save
        $saved = $this->compressImage($file['tmp_name'], $filepath, 80, $ext);

        if ($saved) {
            // Insert into DB
            $stmt = $this->db->prepare("INSERT INTO media (filename, folder, alt_text, uploader_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$filename, $folder, trim($alt_text), $uploader_id]);
            $media_id = $this->db->lastInsertId();

            return [
                'success' => true,
                'media_id' => $media_id,
                'path' => '/assets/uploads/' . $folder . '/' . $filename // Example path
            ];
        }

        return ['success' => false, 'error' => 'Failed to process image.'];
    }

    private function validateUpload($file)
    {
        if (!isset($file['error']) || is_array($file['error']))
            return 'Invalid parameters.';
        if ($file['error'] !== UPLOAD_ERR_OK)
            return 'Upload error code: ' . $file['error'];
        if ($file['size'] > 5000000)
            return 'Exceeded filesize limit (5MB).';

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $ext = array_search(
            $finfo->file($file['tmp_name']),
            array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            ),
            true
        );
        if (false === $ext)
            return 'Invalid file format. Only JPG, PNG, WEBP allowed.';
        return null;
    }

    private function compressImage($source, $destination, $quality, $ext)
    {
        $info = getimagesize($source);
        if (!$info)
            return false;

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        } elseif ($info['mime'] == 'image/webp') {
            $image = imagecreatefromwebp($source);
        } else {
            return false;
        }

        // We can do cropping right here if needed, but for now just compress
        $width = imagesx($image);
        $height = imagesy($image);

        // Scale down if > 1920
        $max_dim = 1920;
        if ($width > $max_dim || $height > $max_dim) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $new_width = $max_dim;
                $new_height = $max_dim / $ratio;
            } else {
                $new_height = $max_dim;
                $new_width = $max_dim * $ratio;
            }
            $new_image = imagecreatetruecolor(round($new_width), round($new_height));
            if ($ext === 'png') {
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, round($new_width), round($new_height), $transparent);
            }
            imagecopyresampled($new_image, $image, 0, 0, 0, 0, round($new_width), round($new_height), $width, $height);
            $image = $new_image;
        }

        $result = false;
        if ($ext == 'jpeg') {
            $result = imagejpeg($image, $destination, $quality);
        } elseif ($ext == 'png') {
            // scale quality 0-100 to 0-9 for png
            $png_quality = round((100 - $quality) / 100 * 9);
            $result = imagepng($image, $destination, $png_quality);
        } elseif ($ext == 'webp') {
            $result = imagewebp($image, $destination, $quality);
        }

        imagedestroy($image);
        return $result;
    }
}
