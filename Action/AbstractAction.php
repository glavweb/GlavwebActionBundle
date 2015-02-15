<?php

/**
 * @namespace
 */
namespace Glavweb\ActionBundle\Action;

/**
 * Class AbstractAction
 *
 * @package Glavweb\ActionBundle\Action
 * @author     Andrey Nilov <nilov@glavweb.ru>
 * @copyright  Copyright (c) 2010-2014 Glavweb, Russia. (http://glavweb.ru)
 */
abstract class AbstractAction
{
    /**
     * Rules of parameters
     * @var array
     */
    protected $parameterRules = array();

    /**
     * Array of default values of parameters
     * @var array
     */
    protected $defaultParameters = array();

    /**
     * An array default values for response
     * @var array
     */
    protected $defaultResponse = array();

    /**
     * Use transaction?
     * @var boolean
     */
    private $useTransaction = false;

    /**
     * Is exists transaction
     * @var boolean
     */
    private $startTransaction = false;

    /**
     * Cancel transactions?
     * @var boolean
     */
    private $cancelTransactions = false;

    /**
     * Execute action
     *
     * @param array $parameters
     * @param bool $validate
     * @param null $useTransaction
     * @return Response
     * @throws \Exception
     */
    public function execute(array $parameters = array(), $validate = true, $useTransaction = null)
    {
        $parameterBag = new ParameterBag($parameters, $this->parameterRules);
        $response     = new Response($this->defaultResponse);

        $this->preExecute($response, $parameterBag);

        if ($validate) {
            $this->validateAction($response, $parameterBag);
        }

        if ($response->success) {
            if ($this->cancelTransactions) {
                $useTransaction = false;
            } else {
                $useTransaction = $useTransaction !== null ?
                    (bool)$useTransaction :
                    $this->useTransaction;
            }

            try {
                if ($useTransaction) {
                    $this->beginTransaction();
                }

                $success = $this->doExecute($response, $parameterBag);
                if ($success !== null) {
                    $response->success = $success;
                }

                if ($useTransaction) {
                    $response->success ? $this->commitTransaction() : $this->rollBackTransaction();
                }

            } catch (\Exception $e) {
                $response->success = false;

                if ($useTransaction) {
                    $this->rollBackTransaction();
                }

                throw $e;
            }
        }

        return $response;
    }

    /**
     * Validate the action
     *
     * @param Response     $response
     * @param ParameterBag $parameterBag
     */
    protected function validateAction(Response $response, ParameterBag $parameterBag)
    {
        if ($response->success) {
            $success = $this->validate($response, $parameterBag);

            if ($success !== null) {
                $response->success = $success;
            }
        }
    }

    /**
     * preExecute()
     *
     * @param Response $response
     * @param ParameterBag $parameterBag
     */
    protected function preExecute(Response $response, ParameterBag $parameterBag)
    {}

    /**
     * @param Response $response
     * @param ParameterBag $parameterBag
     */
    protected function validate(Response $response, ParameterBag $parameterBag)
    {}

    /**
     * Execute the action
     *
     * @param Response $response
     * @param ParameterBag $parameterBag
     */
    protected function doExecute(Response $response, ParameterBag $parameterBag)
    {}

    /**
     * Executes the action
     *
     * @param AbstractAction $command
     * @param array          $parameters
     * @param bool           $validate
     * @param bool|null      $useTransaction
     * @return AbstractAction
     */
    public function executeAction(self $command, array $parameters = array(), $validate = true, $useTransaction = null)
    {
        if ($this->startTransaction || $this->cancelTransactions) {
            $command->cancelTransactions();
        }

        $command->execute($parameters, $validate, $useTransaction);
        return $command;
    }

    /**
     * Cancel all transactions (including nested)
     *
     * @param boolean $cancelTransactions
     * @return void
     */
    public function cancelTransactions($cancelTransactions = true)
    {
        $this->cancelTransactions = $cancelTransactions;
    }

    /**
     * Cancel all transactions (including nested)?
     *
     * @return boolean
     */
    public function isCancelTransactions()
    {
        return $this->cancelTransactions;
    }

    /**
     * Start transaction?
     *
     * @return boolean
     */
    public function isStartTransaction()
    {
        return $this->startTransaction;
    }

    /**
     * Is use transaction?
     *
     * @return boolean
     */
    public function isUseTransaction()
    {
        return $this->useTransaction;
    }

    /**
     * Returns object of the connection
     *
     * @return PDO
     * @throws Exception
     */
    protected function getConnection()
    {
        $connection = $this->connection();
        if ($connection instanceof \PDO) {
            return $connection;
        }

        throw new Exception('The connection must be an instance of class \PDO.');
    }

    /**
     * Returns object of the connection (for user)
     *
     * @return PDO
     */
    protected function connection()
    {}

    /**
     * Starts a transaction
     *
     * @return boolean
     * @throws Exception
     */
    protected function beginTransaction()
    {
        if (!$this->getConnection()->beginTransaction()) {
            throw new Exception('Error when running the transaction.');
        }

        $this->startTransaction = true;
        return true;
    }

    /**
     * Commits the current transaction
     *
     * @return boolean
     * @throws Exception
     */
    protected function commitTransaction()
    {
        if (!$this->getConnection()->commit()) {
            throw new Exception('Error when commit the transaction.');
        }

        return true;
    }

    /**
     * Rollback a transaction
     *
     * @return boolean
     * @throws Exception
     */
    protected function rollBackTransaction()
    {
        if ($this->startTransaction && !$this->getConnection()->rollBack()) {
            throw new Exception('Error when rollback the transaction.');
        }

        return true;
    }
}