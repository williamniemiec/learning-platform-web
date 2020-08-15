<?php
namespace models\util;


/**
 * Contains methods that perform file manipulation.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class FileUtil
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
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
        if (empty($photo))
            throw new \InvalidArgumentException("Photo cannot be empty");
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $photo['tmp_name']);
            finfo_close($finfo);
            
            return explode("/", $mime)[0] == "image";
    }
    
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
        if (empty($photo['tmp_name']) || !FileUtil::isPhoto($photo))
            throw new \InvalidArgumentException("Invalid photo");
            
        $extension = explode("/", $photo['type'])[1];
        
        // Checks if photo extension has an accepted extension or not
        if ($extension != "jpg" && $extension != "jpeg" && $extension != "png")
            throw new \InvalidArgumentException("Invalid photo extension - must be .jpg, .jpeg or .png");
            
        // Generates photo name
        $filename = md5(rand(1,9999).time().rand(1,9999));
        $filename = $filename."."."jpg";
        
        // Saves photo
        if (!$target[count($target)-1] == '/')
            $target .= "/";
        
        move_uploaded_file($photo['tmp_name'], $target.$filename);
        
        return $filename;
    }
}