<?php
namespace models\util;


/**
 * Contains methods that perform data manipulation.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class DataUtil
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
}