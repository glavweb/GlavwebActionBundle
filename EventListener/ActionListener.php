<?php

namespace Glavweb\ActionBundle\EventListener;

use Glavweb\ActionBundle\Action\AbstractAction;
use Glavweb\ActionBundle\Options\ActionOptions;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * Class ActionListener
 * @package Glavweb\ActionBundle\EventListener
 */
class ActionListener extends ContainerAware
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->getMethod() == 'POST') {

            $postKeys = $request->request->keys();
            foreach ($postKeys as $postKey) {
                $requestData = (array)$request->request->get($postKey);

                $serviceId = isset($requestData[ActionOptions::ACTION_NAME]) ? $requestData[ActionOptions::ACTION_NAME] : null;
                if (!$serviceId || !$this->container->has($serviceId)) {
                    return;
                }

                // extract to: data, options
                $data    = array();
                $options = array();
                extract($this->separationOptionsAndData($requestData), EXTR_OVERWRITE);

                $actionService = $this->container->get($serviceId);
                if (!$actionService instanceof AbstractAction) {
                    return;
                }

                $token = isset($options[ActionOptions::TOKEN]) ? $options[ActionOptions::TOKEN] : null;
                $this->checkCsrfToken($token);

                $actionResponse = $actionService->execute($data);

                /** @var \Glavweb\ActionBundle\Helper\ActionHelper $actionHelper */
                $actionHelper = $this->container->get('glavweb_action.action_helper');
                $httpResponse = $actionHelper->createHttpResponse($actionResponse, $options);
                if ($httpResponse instanceof HttpResponse) {
                    $event->setResponse($httpResponse);
                }
            }
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function separationOptionsAndData(array $data)
    {
        $optionNames = ActionOptions::getOptionNames();

        $options = array();
        foreach ($optionNames as $optionName) {
            if (!isset($data[$optionName])) {
                continue;
            }

            $options[$optionName] = $data[$optionName];
            unset($data[$optionName]);
        }

        return array(
            'data'    => $data,
            'options' => $options
        );
    }

    /**
     * Check token
     *
     * @param string $token
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function checkCsrfToken($token)
    {
        /** @var CsrfTokenManager $csrfTokenManager */
        $csrfTokenManager = $this->container->get('security.csrf.token_manager');
        $currentToken     = $csrfTokenManager->getToken('action_button');

        $isValid =
            $token &&
            $csrfTokenManager->isTokenValid($currentToken) &&
            $currentToken->getValue() == $token
        ;

        if (!$isValid) {
            throw new AccessDeniedHttpException();
        }
    }
}