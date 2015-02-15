<?php

namespace Glavweb\ActionBundle\Action;


class ParameterBag
{
    /**
     * Constants types of arguments
     */
    const PARAM_NULL    = ':null';
    const PARAM_STRING  = ':string';
    const PARAM_INT     = ':int';
    const PARAM_FLOAT   = ':float';
    const PARAM_NUMERIC = ':numeric';
    const PARAM_ARRAY   = ':array';
    const PARAM_OBJECT  = ':object';
    const PARAM_BOOL    = ':bool';

    /**
     * Array of types of parameters
     * @var array
     */
    protected $parameterTypes = array(
        self::PARAM_NULL,
        self::PARAM_STRING,
        self::PARAM_ARRAY,
        self::PARAM_BOOL,
        self::PARAM_INT,
        self::PARAM_FLOAT,
        self::PARAM_NUMERIC,
        self::PARAM_OBJECT
    );

    /**
     * Rules of parameters
     * @var array
     */
    private $parameterRules;

    /**
     * Array of default values of parameters
     * @var array
     */
    private $defaultParameters = array();    
    
    /**
     * Current number of parameters
     * @var int
     */
    private $parameterNumber = 0;

    /**
     * Array of parameters
     * @var array
     */
    private $parameters = array();

    /**
     * @param array $parameters
     * @param array $rules
     * @param array $defaultParameters
     */
    public function __construct(array $parameters = array(), array $rules = array(), array $defaultParameters = array())
    {
        $this->parameterRules    = $rules;
        $this->defaultParameters = $defaultParameters;
        
        $this->setParameters($parameters);
    }

    /**
     * Gets a service container parameter.
     *
     * @param string $name    The parameter name
     * @param mixed  $default Default value
     * @return mixed The parameter value
     * @throws Exception If the parameter is not defined
     */
    public function get($name, $default = null)
    {
//        if (!array_key_exists($name, $this->parameters) && !array_key_exists($name, $this->parameterRules)) {
//            throw new Exception('The parameter "' . $name . '" is not defined.');
//        }

        if (!array_key_exists($name, $this->parameters)) {
            return $default;
        }

        return $this->parameters[$name];
    }

    /**
     * Returns true if a parameter name is defined.
     *
     * @param string $name The parameter name
     * @return bool True if the parameter name is defined, false otherwise
     */
    public function has($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Gets the service container parameters.
     *
     * @return array An array of parameters
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Sets the parameters.
     *
     * @param array $parameters
     * @return void
     * @throws Exception
     */
    private function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        foreach ($this->parameterRules as $key => $rule) {
            $this->checkParameter($this->get($key), $rule, $key);

            if ((!isset($this->parameters[$key]) || $this->parameters[$key] === null) && isset($this->defaultParameters[$key])) {
                $this->parameters[$key] = $this->defaultParameters[$key];
            }
        }
    }

    /**
     * Checks parameter on conformity to type
     *
     * @param mixed  $parameter
     * @param string $rule
     * @param int    $parameterKey
     * @return boolean
     * @throws Exception
     */
    private function checkParameter($parameter, $rule, $parameterKey = null)
    {
        if (empty($rule)) {
            return true;
        }

        if (is_string($rule) && strpos($rule, '|') !== false) {
            $rule = explode('|', $rule);
        }

        if (is_array($rule)) {
            foreach ($rule as $subType) {
                try {
                    $this->checkParameter($parameter, $subType, $parameterKey);
                    $result = true;

                } catch (Exception $e) {
                    $result = false;
                    $lastException = $e;
                }

                if ($result) {
                    return true;
                }
            }
            throw $lastException;
        }

        if ($parameterKey === null) {
            $parameterKey = $this->parameterNumber;
        }

        if (!is_int($rule) && !is_string($rule)) {
            throw new Exception('The rule for the parametert "' . $parameterKey  . '"must be a number or a string.');
        }

        if (in_array($rule, $this->parameterTypes)) {
            $nameFunc = array(
                self::PARAM_NULL    => 'is_null',
                self::PARAM_STRING  => 'is_string',
                self::PARAM_ARRAY   => 'is_array',
                self::PARAM_BOOL    => 'is_bool',
                self::PARAM_INT     => 'is_int',
                self::PARAM_FLOAT   => 'is_float',
                self::PARAM_NUMERIC => 'is_numeric',
                self::PARAM_OBJECT  => 'is_object'
            );

            $result = false;
            eval('$result = ' . $nameFunc[$rule] . '($parameter);');

            if (!$result) {
                throw new Exception(get_class($this) . ': ' . $this->getErrorMessageForParameter($parameterKey, $rule));
            }

        } elseif (!is_a($parameter, $rule)) {
            throw new Exception(get_class($this) . ': ' . $this->getErrorMessageForParameter($parameterKey, $rule));
        }

        return true;
    }

    /**
     *  Returns an error message for an parameter
     * 
     * @param $parameterKey
     * @param $rule
     * @return string
     */
    private function getErrorMessageForParameter($parameterKey, $rule)
    {
        $words = array(
            self::PARAM_NULL    => 'a null',
            self::PARAM_STRING  => 'a string',
            self::PARAM_ARRAY   => 'an array',
            self::PARAM_BOOL    => 'a boolean type',
            self::PARAM_INT     => 'an integer',
            self::PARAM_FLOAT   => 'a float',
            self::PARAM_NUMERIC => 'a numeric',
            self::PARAM_OBJECT  => 'an object'
        );

        if (is_string($rule) && strpos($rule, '|') !== false) {
            $rule = explode('|', $rule);
        }
        $rule = (array)$rule;

        $mustBe = array();
        foreach ($rule as $parameterType) {
            if (in_array($parameterType, $this->parameterTypes)) {
                $mustBe[] = $words[$parameterType];
            } else {
                $mustBe[] = 'instance of class of type ' . $parameterType;
            }
        }

        return 'The parameter "' . $parameterKey  . '" must be ' . implode($mustBe, ' or ');
    }
}