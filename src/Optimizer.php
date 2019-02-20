<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 20/02/2019
 * Time: 11:06
 */

namespace Slick\Image;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Class Optimizer
 * @package Slick\Image
 */
class Optimizer
{
    use LoggerAwareTrait;

    /**
     * An array with the allowed image mime types
     * @var array
     */
    protected $allowedMimeTypes = [
        'image/png',
        'image/jpeg',
        'image/jpg'
    ];

    /**
     * @var int The maximum width in pixels of a single image
     */
    protected $maxWidth = 0;
    protected $maxHeight = 0;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SplFileInfo
     */
    protected $image;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $imageLoading;

    /**
     * Initializes the Image Optimizer class
     *
     * @param $postId
     */
    public function __construct(int $width = 1600, int $height = 1600)
    {
        $this->logger = new DummyLogger();
        $this->maxHeight = $width;
        $this->maxWidth = $height;
    }

    /**
     * @throws \Exception
     */
    public function optimize(string $path)
    {
        $this->loadImage($path);
        $this->logger->info(static::class . ": Starting the image optimizer");

        if ($this->needsResize($path)) {
            $memoryLimit = ini_get('memory_limit');
            ini_set('memory_limit', '2G');
            $this->resize($path);
            ini_set('memory_limit', $memoryLimit);
        }

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            if ($this->needsCompress()) {
                $this->compress($path);
            }
        }

        $this->logger->info(static::class . ": Optimized the image");

        return true;
    }

    /**
     * @param $postId
     * @return bool
     * @throws \Exception
     */
    public function isImage(string $path)
    {
        if (!$this->hasAllowedMimeType(new SplFileInfo($path))) {

            $this->logger->alert("Post mime type {$this->getMimeType($path)} is not in allowed mime types list {$this->allowedMimeTypesAsString()}");

            return false;
        }

        return true;
    }

    /**
     * @param $postId
     * @return bool
     * @throws \Exception
     */
    public function hasAllowedMimeType(SplFileInfo $file)
    {
        return isset(array_flip($this->getAllowedMimeTypes())[$this->getMimeType($file)]);

    }

    /**
     * Retrieves the size of the image.
     *
     * @return array
     */

    /**
     * @return array
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * @param $postId
     * @return false|string
     * @throws \Exception
     */
    public function getMimeType(SplFileInfo $file)
    {
        return image_type_to_mime_type(exif_imagetype($file));
    }

    /**
     * @return string
     */
    public function allowedMimeTypesAsString()
    {
        return implode(',', $this->getAllowedMimeTypes());
    }

    /**
     * Checks if the image needs to be resized and updates the meta of the image.
     *
     * @return bool
     * @throws \Exception
     */

    public function needsResize($path)
    {
        $this->loadImage($path);

        $needsResize = false;

        if ($this->getWidth() > $this->getMaxWidth()) {
            $needsResize = true;
        }

        if ($this->getImageHeight() > $this->getImageMaxHeight()) {
            $needsResize = true;
        }

        $this->logger->info(self::class . ": Image needs to be resized");

        return $needsResize;

    }

    /**
     * Retrieves the width of the image.
     *
     * @return mixed
     */

    public function getWidth()
    {
        list($width) = getimagesize($this->image);

        return $width;
    }

    /**
     * Retrieves all the information of the image.
     *
     * @return SplFileInfo
     * @throws \Exception
     */

    public function loadImage($path)
    {
        if ($this->image || $this->imageLoading) {
            if ($this->path === $path) {
                return $this->image;
            }
        }

        $this->imageLoading = true;
        $this->path = $path;

        if (!file_exists($path)) {
            $this->logger->info(self::class . ": Error. File does not exist");

            throw new \Exception(sprintf(
                '%s: Error. File `%s` does not exist',
                __METHOD__,
                $path
            ));
        }

        if (!$this->isImage($path)) {
            throw new \Exception(sprintf(
                '%s: Error. File `%s` is not of type image',
                __METHOD__,
                $path
            ));
        }

        $this->image = new SplFileInfo($path);
        $this->imageLoading = false;

        return $this->image;
    }

    /**
     * Retrieves the allowed max width of the image.
     *
     * @return array|false|int|string
     */

    public function getMaxWidth()
    {
        return $this->maxWidth;

    }

    /**
     * Retrieves the height of the image.
     *
     * @return mixed
     */

    public function getImageHeight()
    {
        list($width, $height) = getimagesize($this->image);

        return $height;
    }

    /**
     * Retrieves the allowed max height of the image.
     *
     * @return array|false|int|string
     */

    public function getImageMaxHeight()
    {
        return $this->maxHeight;
    }

    /**
     * Resizes the image(images) using the Intervention\Image package
     *
     * Updates the meta of the post.
     *
     * @return bool
     * @throws \Exception
     */
    public function resize(string $path)
    {
        $this->loadImage($path);

        if (!$this->needsResize($path)) {
            return false;
        }

        Image::make($this->image->getRealPath())
            ->resize($this->getMaxWidth(), $this->getImageMaxHeight(), function ($constraint) {
                $constraint->aspectRatio();
            })
            ->save($this->image->getRealPath())
            ->destroy();

        $this->logger->info(static::class . ": Resized the image");

        return true;
    }

    /**
     * checks if the image needs to be compressed.
     *
     * @return bool
     */

    public function needsCompress()
    {
        $this->logger->info(static::class . ": Image needs to be compressed");
        return true;
    }

    /**
     * Compresses the image(images) using the spatie/image-optimizer package.
     *
     * The meta of the image will change, if the image is successfully compressed.
     *
     * @throws \Exception
     */

    public function compress($path)
    {
        $this->loadImage($path);

        OptimizerChainFactory::create()
            ->optimize($path);

        $this->logger->info(static::class . ": Compressed the image");
    }
}
