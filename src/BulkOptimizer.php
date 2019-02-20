<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 20/02/2019
 * Time: 09:45
 */

namespace Slick\Image;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * The BulkOptimizer Class
 *
 * Optimizes (resize and compress) a folder of images by the given config
 *
 * @package Slick\Image
 */
class BulkOptimizer
{
    /**
     * All Traits
     */
    use ConfigurableTrait,
        ProgressBarAwareTrait,
        LoggerTrait,
        LoggerAwareTrait;

    /**
     * @var array
     */
    protected $settings = [
        'width' => 1600,
        'height' => 1600,
        'source' => null,
        'dest' => null,
    ];

    /**
     * All required config files
     *
     * @var array
     */
    protected $required = [
        'source',
        'dest'
    ];

    /**
     * @var array
     */
    protected $allowedMimeTypes = [
        'image/png',
        'image/jpeg',
        'image/jpg'
    ];

    /**
     * BulkOptimizer constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->logger = new DummyLogger();
    }

    /**
     * @throws \Exception
     */
    public function optimize()
    {
        $source = $this->config('source');
        $dest = $this->config('dest');

        // Only copies the folder if the destination is different than the source
        if ($source !== $dest) {
            // Copy folder to destination
            $this->copyFolder();
        }

        $files = $this->files($dest);

        // Initializes the progress bar
        $this->progressBarMaxSteps($files->count());
        $this->progressBarStart();

        // Initializes the optimizer
        $optimizer = new Optimizer($this->config('width'), $this->config('height'));
        $optimizer->setLogger($this->logger);

        foreach ($files as $file) {
            try {
                $optimizer->optimize($file);
            } catch (\Exception $e) {
                $this->log('warning', sprintf(
                    '%s: skipped: %s',
                    __METHOD__,
                    $file->getRealPath()
                ));
            }

            unset($file);

            $this->progressBarAdvance();
        }

        $this->progressBarFinish();

        echo PHP_EOL;
    }

    /**
     * Copies the source to the destination folder
     */
    protected function copyFolder()
    {
        $source = $this->config('source');
        $dest = $this->config('dest');

        $filesystem = new Filesystem();
        $filesystem->mirror($source, $dest);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }

    /**
     * Returns a list of files from a given folder.
     *
     * @return Finder
     */
    protected function files(string $dest): Finder
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreDotFiles(true)
            ->filter($this->mimeTypeFilter($this->allowedMimeTypes))
            ->in($dest);

        return $finder;
    }

    /**
     * Returns a enclosed mime type filter for the Symfony Finder filters
     *
     * This will only return the files that correspond the supplied mime types
     *
     * @return \Closure
     */
    public function mimeTypeFilter(array $allowedTypes)
    {
        return function (\SplFileInfo $file) use ($allowedTypes) {
            $mime = image_type_to_mime_type(exif_imagetype($file));

            return in_array($mime, $allowedTypes);
        };
    }
}
