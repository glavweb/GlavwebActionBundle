<?php

namespace Glavweb\ActionBundle\Action;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class FormAction
 *
 * @package Glavweb\ActionBundle\Action
 * @author     Andrey Nilov <nilov@glavweb.ru>
 * @copyright  Copyright (c) 2010-2014 Glavweb, Russia. (http://glavweb.ru)
 */
class FormAction extends StandardAction
{
    /**
     * Rules of parameters
     * @var array
     */
    protected $parameterRules = array(
        'form'    => '\Symfony\Component\Form\FormInterface',
        'request' => '\Symfony\Component\HttpFoundation\Request'
    );

    /**
     * An array default values for response
     * @var array
     */
    protected $defaultResponse = array(
        'model' => null
    );

    /**
     * @param Response     $response
     * @param ParameterBag $parameterBag
     * @return bool|void
     */
    protected function doExecute(Response $response, ParameterBag $parameterBag)
    {
        /** @var \Symfony\Component\Form\FormInterface $form */
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $form    = $parameterBag->get('form');
        $request = $parameterBag->get('request');

        $success = false;
        $form->handleRequest($request);
        if ($form->isValid()) {
            $model = $form->getConfig()->getData();
            if ($model) {
                $em = $this->doctrine->getManager();
                $em->persist($model);
                $em->flush();
            }

            $response->model = $model;
            $success = true;
        }

        return $success;
    }
}