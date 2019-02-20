<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 20/02/2019
 * Time: 10:16
 */

namespace Slick\Image\CommandLoader;

class FactoryCommandLoader extends \Symfony\Component\Console\CommandLoader\FactoryCommandLoader
{
    /**
     * CommandLoader constructor.
     * @param array $commands
     * @throws \Exception
     */
    public function __construct(array $commands)
    {
        $commands = $this->buildAndValidateCommands($commands);

        parent::__construct($commands);
    }

    /**
     * @param array $commands
     * @throws \Exception
     */
    public function buildAndValidateCommands(array $commands)
    {
        $buildAndValidatedCommands = [];
        foreach ($commands as $key => $command) {
            $modelCommand = new ModelCommand($key, $command);

            if (!$modelCommand->validate()) {
                continue;
            }

            $buildAndValidatedCommands[$key] = $modelCommand->factory();
        }

        return $buildAndValidatedCommands;
    }
}
