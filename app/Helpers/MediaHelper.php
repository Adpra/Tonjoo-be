<?php

namespace App\Helpers;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaHelper
{
    public static function isBase64Image($str)
    {
        try {
            $str = preg_replace('#^data:image/\w+;base64,#i', '', $str);

            $decoded = base64_decode($str, true);

            if (base64_encode($decoded) !== $str) {

                return false;
            }

            return true;
        } catch (Exception $e) {
            // If exception is caught, then it is not a base64 encoded string
            return false;
        }
    }

    public static function handleUploadImage(string|null $image, string $directory = 'images', string $oldFile = null)
    {
        if (!$image) {
            return null;
        }

        if ($oldFile) {
            // Check if the file already exists
            if (Storage::disk('public')->exists("$directory", $oldFile)) {
                // Delete the existing file
                Storage::disk('public')->delete("$directory/$oldFile");
            }
        }

        if (self::isBase64Image($image)) {
            $base64ImageDecoded = base64_decode(trim(preg_replace('#^data:w+/\w+;base64,#i', '', $image)), true);

            $mimeType = getimagesize($image)['mime'];
            $extension = explode('/', $mimeType)[1];

            if (!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                throw new Exception('Invalid image');
            }

            $fileName = Str::random(40) . ".$extension";

            Storage::disk('public')->put("$directory/$fileName", $base64ImageDecoded);

            return $fileName;
        }


        return null;
    }
}
