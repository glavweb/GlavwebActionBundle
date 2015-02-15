<?php

namespace Glavweb\ActionBundle\Options;

/**
 * Class ActionOptions
 * @package Glavweb\ActionBundle\Options
 */
class ActionOptions
{
    const ACTION_NAME            = '_action_name';
    const TOKEN                  = '_token';
    const REDIRECT_URL           = '_redirect_url';
    const REDIRECT_URL_SUCCESS   = '_redirect_url_success';
    const REDIRECT_URL_FAILURE   = '_redirect_url_failure';
    const REDIRECT_ROUTE         = '_redirect_route';
    const REDIRECT_ROUTE_SUCCESS = '_redirect_route_success';
    const REDIRECT_ROUTE_FAILURE = '_redirect_route_failure';
    const MESSAGE_SUCCESS        = '_message_success';
    const MESSAGE_FAILURE        = '_message_failure';
    const CALLBACK_PRE_EXECUTE   = '_callback_pre_execute';
    const CALLBACK_SUCCESS       = '_callback_success';
    const CALLBACK_FAILURE       = '_callback_failure';
    const ACTIONS_PARAMS         = '_action_params';

    // Value for redirects
    const VALUE_CURRENT_URL = '_current_url';

    /**
     * @param string $optionName
     * @return bool
     */
    public static function optionExists($optionName)
    {
        return isset(self::$options[$optionName]);
    }

    /**
     * @return array
     */
    public static function getOptionNames()
    {
        return self::$options;
    }

    /**
     * @var array
     */
    protected static $options = array(
        self::ACTION_NAME,
        self::TOKEN,
        self::REDIRECT_URL,
        self::REDIRECT_URL_SUCCESS,
        self::REDIRECT_URL_FAILURE,
        self::REDIRECT_ROUTE,
        self::REDIRECT_ROUTE_SUCCESS,
        self::REDIRECT_ROUTE_FAILURE,
        self::MESSAGE_SUCCESS,
        self::MESSAGE_FAILURE,
        self::CALLBACK_PRE_EXECUTE,
        self::CALLBACK_SUCCESS,
        self::CALLBACK_FAILURE
    );
}