<?php

namespace Glavweb\ActionBundle\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Glavweb\ActionBundle\Options\ActionOptions;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Request;
use Glavweb\ActionBundle\Action\AbstractAction;

/**
 * Class ActionHandler
 * @package Glavweb\ActionBundle\Handler
 */
class ActionHandler extends ContainerAware
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request   = $container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @param FormInterface $form
     * @param array $options
     * @return HttpResponse|null
     * @throws \RuntimeException
     */
    public function handleForm(FormInterface $form, array $options = array())
    {
        if ($this->request === null) {
            return null;
        }

        $response = null;

        $requestKeys = $this->request->request->keys();
        $formMethod  = $form->getConfig()->getMethod();
        if (in_array($form->getName(), $requestKeys) && $this->request->isMethod($formMethod)) {
            $serviceId = isset($options[ActionOptions::ACTION_NAME]) ?
                $options[ActionOptions::ACTION_NAME] :
                'glavweb_action.action.form'
            ;

            if (!$this->container->has($serviceId)) {
                return;
            }

            $actionService = $this->container->get($serviceId);
            if (!$actionService instanceof AbstractAction) {
                return;
            }

            if (isset($options[ActionOptions::CALLBACK_PRE_EXECUTE])) {
                call_user_func($options[ActionOptions::CALLBACK_PRE_EXECUTE], $form, $this->request);
            }

            $actionParams = array_merge(array(
                'form'    => $form,
                'request' => $this->request
            ), isset($options[ActionOptions::ACTIONS_PARAMS]) ? $options[ActionOptions::ACTIONS_PARAMS] : array());

            $actionResponse = $actionService->execute($actionParams);

            if ($actionResponse->success && isset($options[ActionOptions::CALLBACK_SUCCESS])) {
                call_user_func($options[ActionOptions::CALLBACK_SUCCESS], $actionResponse);
            }

            if (!$actionResponse->success && isset($options[ActionOptions::CALLBACK_FAILURE])) {
                call_user_func($options[ActionOptions::CALLBACK_FAILURE], $actionResponse);
            }

            /** @var \Glavweb\ActionBundle\Helper\ActionHelper $actionHelper */
            $actionHelper = $this->container->get('glavweb_action.action_helper');
            $response = $actionHelper->createHttpResponse($actionResponse, $options);
        }

        return $response;
    }
}