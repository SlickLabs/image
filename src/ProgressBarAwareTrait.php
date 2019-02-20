<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 20/02/2019
 * Time: 15:04
 */

namespace Slick\Image;


use Symfony\Component\Console\Helper\ProgressBar;

trait ProgressBarAwareTrait
{
    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * @param ProgressBar $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    /**
     * The maximum amount of steps the progress bar will take
     *
     * @param int $steps
     */
    public function progressBarMaxSteps(int $steps)
    {
        if (!$this->progressBar) {
            return null;
        }

        $this->progressBar->setMaxSteps($steps);
    }

    /**
     * Starts the progress output.
     *
     * @param int|null $max Number of steps to complete the bar (0 if indeterminate), null to leave unchanged
     */
    public function progressBarStart()
    {
        if (!$this->progressBar) {
            return null;
        }

        $this->progressBar->start();
    }

    /**
     * Finishes the progress output.
     */
    public function progressBarFinish()
    {
        if (!$this->progressBar) {
            return null;
        }

        $this->progressBar->finish();
    }

    /**
     * Advances the progress output X steps.
     *
     * @param int $step Number of steps to advance
     */
    public function progressBarAdvance()
    {
        if (!$this->progressBar) {
            return null;
        }

        $this->progressBar->advance();
    }
}
