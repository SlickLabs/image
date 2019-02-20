<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 20/02/2019
 * Time: 10:18
 */

namespace Slick\Image\CommandLoader;


class ModelCommand
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $command;

    /**
     * ModelCommand constructor.
     * @param string $key
     * @param string $command
     */
    public function __construct(string $key, string $command)
    {
        $this->key = $key;
        $this->command = $command;
     }

    /**
     * @throws \Exception
     */
    public function validate()
    {
        if (class_exists($this->command)) {
            return true;
        }

        throw new \Exception(sprintf(
            '%s: class `%s` does not exist',
            __METHOD__,
            $this->command
        ));
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function factory()
    {
        return function() {
            return new $this->command($this->key);
        };
    }
}