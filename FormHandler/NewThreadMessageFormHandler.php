<?php

namespace Awaresoft\MessageBundle\FormHandler;

use FOS\MessageBundle\Composer\ComposerInterface;
use FOS\MessageBundle\FormHandler\NewThreadMessageFormHandler as BaseNewThreadMessageFormHandler;
use FOS\MessageBundle\FormModel\NewThreadMessage;
use FOS\MessageBundle\Security\ParticipantProviderInterface;
use FOS\MessageBundle\Sender\SenderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NewThreadMessageFormHandler
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class NewThreadMessageFormHandler extends BaseNewThreadMessageFormHandler
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(Request $request, ComposerInterface $composer, SenderInterface $sender, ParticipantProviderInterface $participantProvider, ContainerInterface $container)
    {
        parent::__construct($request, $composer, $sender, $participantProvider);

        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function processValidForm(Form $form)
    {
        $data = $form->getData();
        $recipientId = $this->request->get('recipientId');

        if (!$recipientId) {
            throw new \Exception(sprintf('RecipientId not found in request'));
        }

        $recipient = $this->container->get('sonata.user.orm.user_manager')->findUserBy(['id' => $recipientId]);
        if (!$recipient) {
            throw new \Exception(sprintf('Recipient with id: %d not found', $recipientId));
        }

        $message = new NewThreadMessage();
        $message->setSubject('default');
        $message->setRecipient($recipient);
        $message->setBody($data['body']);
        $message = $this->composeMessage($message);

        $this->sender->send($message);

        return $message;
    }
}