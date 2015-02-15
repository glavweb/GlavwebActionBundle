<?php

namespace Glavweb\ActionBundle\Factory;

use Glavweb\ActionBundle\Options\ActionOptions;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;

class ActionFormFactory
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param string|FormTypeInterface $type          The type of the form
     * @param mixed                    $data          The initial data
     * @param array                    $options       The options
     * @param array                    $actionOptions The action options
     * @return FormInterface
     */
    public function create($type, $data = null, array $options = array(), array $actionOptions = array())
    {
        if (!isset($options['intention'])) {
            $options['intention'] = 'action_button';
        }

        $form = $this->formFactory->create($type, $data, $options);

        foreach ($actionOptions as $actionOptionName => $actionOptionValue) {
            if (ActionOptions::optionExists($actionOptionName)) {
                continue;
            }

            $form->add($actionOptionName, 'hidden', array(
                'mapped' => false,
                'data'   => $actionOptionValue
            ));
        }

        return $form;
    }
}