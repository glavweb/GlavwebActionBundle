<?php

namespace Glavweb\ActionBundle\Helper;

use Glavweb\ActionBundle\Action\Response as ActionResponse;
use Glavweb\ActionBundle\Options\ActionOptions;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ActionHelper
 * @package Glavweb\ActionBundle\Helper
 */
class ActionHelper
{
    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * @param RequestStack $requestStack
     * @param Session $session
     * @param Router $router
     * @param TranslatorInterface $translator
     */
    public function __construct(RequestStack $requestStack, Session $session, Router $router,  TranslatorInterface $translator)
    {
        $this->request    = $requestStack->getCurrentRequest();
        $this->session    = $session;
        $this->router     = $router;
        $this->translator = $translator;
    }

    /**
     * @param ActionResponse $actionResponse
     * @param array          $options
     * @return JsonResponse|RedirectResponse
     */
    public function createHttpResponse(ActionResponse $actionResponse, array $options)
    {
        $success = $actionResponse->success;

        // Define message
        $messageFromOptions = null;
        if ($success) {
            $defaultMessage = 'glavweb_action.message_success';

            if (isset($options[ActionOptions::MESSAGE_SUCCESS])) {
                $messageFromOptions = $options[ActionOptions::MESSAGE_SUCCESS];
            }

        } else {
            $defaultMessage = 'glavweb_action.message_failure';

            if (isset($options[ActionOptions::MESSAGE_FAILURE])) {
                $messageFromOptions = $options[ActionOptions::MESSAGE_FAILURE];
            }
        }

        $translator = $this->translator;

        if ($actionResponse->message) {
            if ($messageFromOptions) {
                $message = sprintf('%s. %s',
                    $translator->trans($messageFromOptions),
                    $translator->trans($actionResponse->message)
                );
            } else {
                $message = $actionResponse->message;
            }
        } else {
            $message = $messageFromOptions ? $messageFromOptions : $defaultMessage;
        }

        // Define redirect URL
        $redirectUrl = isset($options[ActionOptions::REDIRECT_URL]) ? $options[ActionOptions::REDIRECT_URL] : null;
        $redirectUrl = $success && isset($options[ActionOptions::REDIRECT_URL_SUCCESS]) ? $options[ActionOptions::REDIRECT_URL_SUCCESS] : $redirectUrl;
        $redirectUrl = !$success && isset($options[ActionOptions::REDIRECT_URL_FAILURE]) ? $options[ActionOptions::REDIRECT_URL_FAILURE] : $redirectUrl;

        if (!$redirectUrl) {
            $redirectRoute = isset($options[ActionOptions::REDIRECT_ROUTE]) ? $options[ActionOptions::REDIRECT_ROUTE] : null;
            $redirectRoute = $success && isset($options[ActionOptions::REDIRECT_ROUTE_SUCCESS]) ? $options[ActionOptions::REDIRECT_ROUTE_SUCCESS] : $redirectRoute;
            $redirectRoute = !$success && isset($options[ActionOptions::REDIRECT_ROUTE_FAILURE]) ? $options[ActionOptions::REDIRECT_ROUTE_FAILURE] : $redirectRoute;

            if ($redirectRoute) {
                $redirectUrl = $this->router->generate($redirectRoute);
            }
        } elseif ($redirectUrl == ActionOptions::VALUE_CURRENT_URL) {
            $redirectUrl = $this->request->getRequestUri();
        }

        // Returns http response
        if ($this->request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'success' => $success,
                'message' => $message
            ));

        } else {
            $messageType = 'action_' . ($success ? 'success' : 'false');
            $this->session->getFlashBag()->add($messageType, $message);

            if ($redirectUrl) {
                return new RedirectResponse($redirectUrl, 302);
            }
        }
    }
}