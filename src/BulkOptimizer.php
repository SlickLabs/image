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
use Slick\Image\Finder\Filters\MimeTypeFilter;
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
        'dest'
    ];

    /**
     * @var array
     */
    protected $allowedMimeTypes = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/svg+xml',
        'image/gif',
    ];

    /**
     * The source folder that will copied to destination and be optimized and resized
     *
     * @var string
     */
    protected $source;

    /**
     * The destination folder. Where all the images will be optimized.
     *
     * @var string
     */
    protected $dest;

    /**
     * BulkOptimizer constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->logger = new DummyLogger();
        $this->source = $this->config('source', $this->config('dest'));
        $this->dest = $this->config('dest');
    }

    /**
     * @throws \Exception
     */
    public function optimize()
    {
        // Only copies the folder if the destination is different than the source
        if ($this->source !== $this->dest) {
            // Copy folder to destination
            $this->copyFolder();
        }

        $files = $this->files($this->dest);

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
        $filesystem = new Filesystem();
        $filesystem->mirror($this->source, $this->dest);
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
            ->filter(MimeTypeFilter::get(($this->allowedMimeTypes)))
            ->in($dest);

        return $finder;
    }
}
