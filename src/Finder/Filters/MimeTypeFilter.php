<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 21/02/2019
 * Time: 13:57
 */

namespace Slick\Image\Finder\Filters;

/**
 * The Mime Type Filter
 *
 * Returns an enclosed mime type filter for the Symfony Finder filters
 *
 * This will only return the files that correspond the supplied mime types
 *
 * @return \Closure
 */
class MimeTypeFilter
{
    public static function get(array $allowedTypes)
    {
        return function (\SplFileInfo $file) use ($allowedTypes) {
            $mime = image_type_to_mime_type(exif_imagetype($file));

            return in_array($mime, $allowedTypes);
        };
    }
}
