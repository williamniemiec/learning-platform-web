<?php
namespace panel\util;


/**
 * Contains methods that perform file manipulation.
 */
class FileUtil
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------    
    /**
     * Stores a submitted photo.
     *
     * @param       array $photo Submitted photo (from $_FILES)
     * @param       string $target Save path
     *
     * @return      string Generated filename for the photo
     *
     * @throws      \InvalidArgumentException If photo is invalid or its
     * extension is not .jpg, .jpeg or .png
     */
    public static function storePhoto(array $photo, string $target) : string
    {
        FileUtil::validateValidPhoto($photo);
        FileUtil::validateCompatiblePhoto($photo);

        $filename = FileUtil::generateUniquePhotoName();
        $output = FileUtil::normalizeOutput($target);        

        move_uploaded_file($photo['tmp_name'], $output.$filename);
        
        return $filename;
    }

    private static function validateValidPhoto($photo)
    {
        if (empty($photo['tmp_name']) || !FileUtil::isPhoto($photo)) {
            throw new \InvalidArgumentException("Invalid photo");
        }
    }

    private static function validateCompatiblePhoto($photo)
    {
        $extension = FileUtil::extractPhotoExtension($photo);
        
        if ($extension != "jpg" && $extension != "jpeg" && $extension != "png") {
            throw new \InvalidArgumentException("Invalid photo extension - ".
                                                "must be .jpg, .jpeg or .png");
        }
    }

    private static function extractPhotoExtension($photo)
    {
        return explode("/", $photo['type'])[1];
    }

    private static function normalizeOutput($path)
    {
        $normalizedPath = $path;

        if (!$path[count($path)-1] == '/') {
            $normalizedPath .= "/";
        }

        return $normalizedPath;
    }

    private static function generateUniquePhotoName()
    {
        $prefix = md5(rand(1,9999).time().rand(1,9999));
        $suffix = ".jpg";

        return $prefix.$suffix;
    }

    /**
     * Checks if a submitted photo is really a photo.
     *
     * @param       array $photo Submitted photo (from $_FILES)
     *
     * @return      boolean If the photo is really a photo
     *
     * @throws      \InvalidArgumentException If photo is empty
     */
    public static function isPhoto(array $photo) : bool
    {
        FileUtil::validatePhoto($photo);
            
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $photo['tmp_name']);
        finfo_close($finfo);
        
        return explode("/", $mime)[0] == "image";
    }

    private static function validatePhoto($photo)
    {
        if (empty($photo)) {
            throw new \InvalidArgumentException("Photo cannot be empty");
        }
    }
}