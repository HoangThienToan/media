<?php

namespace Edu2work\Media;

use Illuminate\Contracts\Config\Repository;

class Config
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $repository;

    /**
     * Config constructor.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     */
    public function __construct()
    {
        $repository = new Repository;
        $this->repository = $repository;
    }

    /**
     * Check if config uses wild card search.
     *
     * @return bool
     */
    public function EduKey()
    {
        return $this->repository->get('edu.key', false);
    }

    /**
     * Check if app is in debug mode.
     *
     * @return bool
     */
    public function isDebugging()
    {
        return $this->repository->get('app.debug', false);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->repository->get($key, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $this->repository->set($key, $value);
    }
}
