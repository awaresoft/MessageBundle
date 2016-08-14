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

class AjaxController extends Controller
{
    /**
     * Displays a thread, also allows to reply to it
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $messageManager = $this->get('fos_message.message_manager');
        $threadManager = $this->get('fos_message.thread_manager');
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $unreadCount = $messageManager->getNbUnreadMessageByParticipant($user);
        $threads = $threadManager->findParticipantAllThreads($user, MessageManager::DEFAULT_PANEL_LIMIT);

        return new JsonResponse(array(
            'view' => $this->renderView('AwaresoftMessageBundle:Message\Helper:threads_short_list.html.twig', array(
                'threads' => $threads
            )),
            'count' => count($threads),
            'unreadCount' => $unreadCount,
        ), 200);

    }
}
