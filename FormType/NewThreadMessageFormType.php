<?php

namespace Awaresoft\MessageBundle\FormType;

use FOS\MessageBundle\FormType\NewThreadMessageFormType as BaseNewThreadMessageFormType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;

/**
 * Extended message form type for starting a new conversation
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class NewThreadMessageFormType extends BaseNewThreadMessageFormType
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $this->container->get('translator');

        $builder
            ->add('body', 'textarea', [
                'required' => true,
                'label' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function ($event) use ($translator) {
            $data = $event->getData();
            $form = $event->getForm();
            if (null === $data) {
                $form->get('body')->addError(new FormError('error'));

                return;
            }

            if (strlen($data['body']) < 5) {
                $form->get('body')->addError(new FormError($translator->trans('message.validation.min_length')));
            }
        });
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}