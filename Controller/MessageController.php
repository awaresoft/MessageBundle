<?php

namespace Awaresoft\MessageBundle\Controller;

use Application\UserBundle\Entity\User;
use Awaresoft\MessageBundle\Entity\Thread;
use Awaresoft\MessageBundle\EntityManager\MessageManager;
use Awaresoft\MessageBundle\EntityManager\ThreadManager;
use Awaresoft\MessageBundle\Provider\ProviderInterface;
use Application\UserBundle\Entity\SettingManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageController extends Controller
{
    /**
     * Displays the authenticated participant inbox
     *
     * @param Request $request
     *
     * @return Response
     */
    public function inboxAction(Request $request)
    {
        $offset = 0;
        if ($page = $request->get('page')) {
            $offset = ($page - 1) * ThreadManager::DEFAULT_PANEL_LIMIT;
        } else {
            $page = 1;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $threads = $this->getProvider()->getAllThreads(ThreadManager::DEFAULT_PANEL_LIMIT, $offset);
        $countAll = $this->getProvider()->countAllThreads();

        $return = [
            'threads' => $threads,
            'count' => $offset + ThreadManager::DEFAULT_PANEL_LIMIT,
            'countAll' => $countAll,
            'currentPage' => $page,
            'route' => 'fos_message_inbox',
            'type' => 'message-inbox',
        ];

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'view' => $this->renderView('AwaresoftMessageBundle:Message\Helper:threads_list.html.twig', $return),
                'type' => 'message-inbox',
            ], 200);
        }

        // SEO
        $translator = $this->get('translator');
        $seoPage = $this->get('sonata.seo.page');
        $seoPage
            ->setTitle($translator->trans('seo.user.messages.inbox.title'));

        return $this->container->get('templating')->renderResponse('AwaresoftMessageBundle:Message:inbox.html.twig', $return);
    }

    /**
     * Displays the authenticated participant deleted threads
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deletedAction(Request $request)
    {
        $offset = 0;
        if ($page = $request->get('page')) {
            $offset = ($page - 1) * ThreadManager::DEFAULT_PANEL_LIMIT;
        } else {
            $page = 1;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $threads = $this->getProvider()->getDeletedThreads(ThreadManager::DEFAULT_PANEL_LIMIT, $offset);
        $countAll = $this->getProvider()->countDeletedThreads();

        $return = [
            'threads' => $threads,
            'count' => $offset + ThreadManager::DEFAULT_PANEL_LIMIT,
            'countAll' => $countAll,
            'currentPage' => $page,
            'route' => 'fos_message_deleted',
            'type' => 'message-deleted',
        ];

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'view' => $this->renderView('AwaresoftMessageBundle:Message/Helper:threads_list.html.twig', $return),
                'type' => 'message-deleted',
            ], 200);
        }

        // SEO
        $translator = $this->get('translator');
        $seoPage = $this->get('sonata.seo.page');
        $seoPage
            ->setTitle($translator->trans('seo.user.messages.deleted.title'));

        return $this->container->get('templating')->renderResponse('AwaresoftMessageBundle:Message:deleted.html.twig', $return);
    }

    /**
     * Displays a thread, also allows to reply to it
     *
     * @param Request $request
     * @param string $threadId the thread id
     *
     * @return Response
     */
    public function threadAction(Request $request, $threadId)
    {
        /**
         * @var Thread $thread
         */
        $thread = $this->getProvider()->getThread($threadId);
        $form = $this->container->get('fos_message.reply_form.factory')->create($thread);
        $formHandler = $this->container->get('fos_message.reply_form.handler');
        $messageManager = $this->get('fos_message.message_manager');
        $user = $this->getUser();
        $recipient = $thread->getOtherParticipants($user)[0];

        // secure reply
        if ($request->isMethod('POST')) {

            if ($recipient->getBlockedUsers()->contains($user)) {
                throw new HttpException(Response::HTTP_FORBIDDEN);
            }

            if ($recipient->getSetting(SettingManager::SETTING_BLOCK_SEND_MESSAGES) &&
                $recipient->getSetting(SettingManager::SETTING_BLOCK_SEND_MESSAGES)->getValue() === true
            ) {
                throw new HttpException(Response::HTTP_FORBIDDEN);
            }
        }

        $countAll = $messageManager->countMessagesByThread($thread);
        $limit = MessageManager::DEFAULT_PANEL_LIMIT;
        $offset = $countAll - MessageManager::DEFAULT_PANEL_LIMIT;
        if ($page = $request->get('page')) {
            $offset = $countAll - $page * MessageManager::DEFAULT_PANEL_LIMIT;
        } else {
            $page = 1;
        }

        if ($offset <= 0) {
            $limit = MessageManager::DEFAULT_PANEL_LIMIT + $offset;
            $offset = 0;
        }

        $messages = $messageManager->findMessagesByThread($thread, $limit, $offset);

        if ($message = $formHandler->process($form)) {
            return new RedirectResponse($this->container->get('router')->generate('fos_message_thread_view', [
                'threadId' => $message->getThread()->getId(),
            ]));
        }

        $return = [
            'form' => $form->createView(),
            'thread' => $thread,
            'messages' => $messages,
            'count' => ($page - 1) * MessageManager::DEFAULT_PANEL_LIMIT + MessageManager::DEFAULT_PANEL_LIMIT,
            'countAll' => $countAll,
            'currentPage' => $page,
            'route' => 'fos_message_thread_view',
            'routeParams' => json_encode(['threadId' => $threadId]),
            'type' => 'messages-in-thread',
        ];

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'view' => $this->renderView('AwaresoftMessageBundle:Message/Helper:messages_list.html.twig', $return),
                'type' => 'messages-in-thread',
            ], 200);
        }

        // SEO
        $translator = $this->get('translator');
        $seoPage = $this->get('sonata.seo.page');
        $seoPage
            ->setTitle($translator->trans('seo.user.messages.show.title', [
                '%username%' => $recipient,
            ]));

        return $this->container->get('templating')->renderResponse('AwaresoftMessageBundle:Message:thread.html.twig', $return);
    }

    /**
     * Create a new message thread
     *
     * @param Request $request
     * @param $recipientId
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function newThreadAction(Request $request, $recipientId)
    {
        $user = $this->getUser();

        /**
         * @var User $recipient
         */
        $recipient = $this->get('sonata.user.orm.user_manager')->findUserBy(['id' => $recipientId]);

        if (!$recipient) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        if ($recipient->getBlockedUsers()->contains($user)) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        if ($recipient === $user) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        if ($recipient->getSetting(SettingManager::SETTING_BLOCK_SEND_MESSAGES) &&
            $recipient->getSetting(SettingManager::SETTING_BLOCK_SEND_MESSAGES)->getValue() === true
        ) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $thread = $this->get('fos_message.thread_manager')->findThreadByParticipants($user, $recipient);

        if ($thread) {
            return $this->redirect($this->generateUrl('fos_message_thread_view', ['threadId' => $thread->getId()]));
        }

        /**
         * @var Form $form
         */
        $form = $this->container->get('fos_message.new_thread_form.factory')->create();
        $formHandler = $this->container->get('fos_message.new_thread_form.handler');

        if ($message = $formHandler->process($form)) {
            return new RedirectResponse($this->container->get('router')->generate('fos_message_thread_view', [
                'threadId' => $message->getThread()->getId(),
            ]));
        }

        return $this->container->get('templating')->renderResponse('AwaresoftMessageBundle:Message:newThread.html.twig', [
            'form' => $form->createView(),
            'data' => $form->getData(),
            'recipient' => $recipient,
        ]);
    }

    /**
     * Deletes a thread
     *
     * @param Request $request
     * @param string $threadId the thread id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $threadId)
    {
        $thread = $this->getProvider()->getThread($threadId);
        $this->container->get('fos_message.deleter')->markAsDeleted($thread);
        $this->container->get('fos_message.thread_manager')->saveThread($thread);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'message' => $this->get('translator')->trans('message.delete_success'),
                'action' => 'delete',
                'id' => $threadId,
            ], 200);
        }

        return new RedirectResponse($this->container->get('router')->generate('fos_message_inbox'));
    }

    /**
     * Undeletes a thread
     *
     * @param Request $request
     * @param string $threadId
     *
     * @return RedirectResponse
     */
    public function undeleteAction(Request $request, $threadId)
    {
        $thread = $this->getProvider()->getThread($threadId);
        $this->container->get('fos_message.deleter')->markAsUndeleted($thread);
        $this->container->get('fos_message.thread_manager')->saveThread($thread);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'message' => $this->get('translator')->trans('message.undelete_success'),
                'action' => 'undelete',
                'id' => $threadId,
            ], 200);
        }

        return new RedirectResponse($this->container->get('router')->generate('fos_message_inbox'));
    }

    /**
     * Searches for messages in the inbox and sentbox
     *
     * @return Response
     */
    public function searchAction()
    {
        $query = $this->container->get('fos_message.search_query_factory')->createFromRequest();
        $threads = $this->container->get('fos_message.search_finder')->find($query);

        return $this->container->get('templating')->renderResponse('AwaresoftMessageBundle:Message:search.html.twig', [
            'query' => $query,
            'threads' => $threads,
        ]);
    }

    /**
     * Gets the provider service
     *
     * @return ProviderInterface
     */
    protected function getProvider()
    {
        return $this->container->get('fos_message.provider');
    }
}
