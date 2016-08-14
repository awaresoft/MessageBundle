<?php

namespace Awaresoft\MessageBundle\Provider;

use Awaresoft\MessageBundle\EntityManager\ThreadManager;
use FOS\MessageBundle\Provider\Provider as BaseProvider;

/**
 * Provides threads for the current authenticated user
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Provider extends BaseProvider implements ProviderInterface
{
    /**
     * @var ThreadManager
     */
    protected $threadManager;

    /**
     * @inheritdoc
     */
    public function getAllThreads($limit = ThreadManager::DEFAULT_PANEL_LIMIT, $offset = 0)
    {
        $participant = $this->getAuthenticatedParticipant();

        return $this->threadManager->findParticipantAllThreads($participant, $limit, $offset);
    }

    public function countAllThreads()
    {
        $participant = $this->getAuthenticatedParticipant();

        return $this->threadManager->countParticipantAllThreads($participant);
    }

    public function countDeletedThreads()
    {
        $participant = $this->getAuthenticatedParticipant();

        return $this->threadManager->countParticipantDeletedThreads($participant);
    }

    public function getDeletedThreads($limit = ThreadManager::DEFAULT_PANEL_LIMIT, $offset = 0)
    {
        $participant = $this->getAuthenticatedParticipant();

        return $this->threadManager->findParticipantDeletedThreadsWithLimit($participant, $limit, $offset);
    }
}
