<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 20/02/2019
 * Time: 09:59
 */

namespace Slick\Image\Commands;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Slick\Image\BulkOptimizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkOptimizeCommand extends Command implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $this->output->writeln(sprintf('<%s>%s: %s</%s>', $level, $level, $message, $level));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        ini_set('memory_limit', '2G');

        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Bulk optimizes a set of images')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Provide a source and destination folder to optimize images')
            /**
             * The config options
             */
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'The source folder.')
            ->addOption('dest', null, InputOption::VALUE_REQUIRED, 'The destination folder.')
            ->addOption('height', null, InputOption::VALUE_OPTIONAL, 'The max height of the image. Defaults to: 1600px')
            ->addOption('width', null, InputOption::VALUE_OPTIONAL, 'The max width of the image. Defaults to: 1600px');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $this->output = $output;

        $optimizer = new BulkOptimizer([
            'source' => $input->getOption('source'),
            'dest' => $input->getOption('dest'),
            'width' => $input->getOption('width'),
            'height' => $input->getOption('height'),
        ]);

        $optimizer->setProgressBar(new ProgressBar($output));
        $optimizer->optimize();
    }
}
