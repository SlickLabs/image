<?php
/**
 * Created by SlickLabs - Wefabric.
 * User: nathanjansen <nathan@wefabric.nl>
 * Date: 20/02/2019
 * Time: 12:07
 */

namespace Slick\Image;


use Tightenco\Collect\Support\Collection;

trait ConfigurableTrait
{
    /**
     * @var Collection
     */
    protected $config;

    /**
     * @var \Exception
     */
    private $exception;

    public function config(string $key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    public function setConfig(array $config = [])
    {
        $this->config = collect($this->settings)->merge($config);

        if (!$this->configValidate()) {
            throw $this->exception;
        }
    }

    public function requiredConfig()
    {
        return $this->config->intersectByKeys(array_flip($this->required))->all();
    }

    protected function configValidate()
    {
        foreach ($this->requiredConfig() as $key => $value) {
            if (!$value) {
                $this->exception = new \Exception(sprintf(
                    '%s: Required config value for key `%s` not set',
                    __METHOD__,
                    $key
                ));

                return false;
            }
        }

        return true;
    }
}
