<?php

namespace Awaresoft\MessageBundle\FormHandler;

use FOS\MessageBundle\Composer\ComposerInterface;
use FOS\MessageBundle\FormHandler\ReplyMessageFormHandler as BaseReplyMessageFormHandler;
use FOS\MessageBundle\FormModel\NewThreadMessage;
use FOS\MessageBundle\FormModel\ReplyMessage;
use FOS\MessageBundle\Security\ParticipantProviderInterface;
use FOS\MessageBundle\Sender\SenderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReplyMessageFormHandler
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ReplyMessageFormHandler extends BaseReplyMessageFormHandler
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
        $threadId = $this->request->get('threadId');

        if (!$threadId) {
            throw new \Exception(sprintf('threadIt not found in request'));
        }

        $thread = $this->container->get('fos_message.provider')->getThread($threadId);;
        if (!$thread) {
            throw new \Exception(sprintf('Thread with id: %d not found', $thread));
        }

        $message = new ReplyMessage();
        $message->setThread($thread);
        $message->setBody($data['body']);
        $message = $this->composeMessage($message);

        $this->sender->send($message);

        return $message;
    }
}