<?php

namespace Glavweb\ActionBundle\Action;

/**
 * Class Response
 * @package Glavweb\ActionBundle\Action
 */
class Response
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (!array_key_exists('success', $data)) {
            $data['success'] = true;
        }

        if (!array_key_exists('message', $data)) {
            $data['message'] = '';
        }

        if (!array_key_exists('errors', $data)) {
            $data['errors'] = array();
        }

        $this->data = $data;
    }

    /**
     * "Magic method" __get().
     *
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new Exception('The parameter "' . $name . '" is not defined.');
        }

        return $this->data[$name];
    }

    /**
     * "Magic method" __set().
     *
     * @param string $name
     * @param mixed  $value
     * @return void
     * @throws Exception
     */
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new Exception(get_class($this) . ': The argument "' . $name . '" is not defined.');
        }

        $this->data[$name] = $value;
    }

    /**
     * "Magic method" __isset().
     *
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }
}