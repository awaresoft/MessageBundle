<?php

namespace Awaresoft\MessageBundle\Provider;
use Awaresoft\MessageBundle\EntityManager\ThreadManager;

/**
 * Provides threads for the current authenticated user
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
interface ProviderInterface
{
    /**
     * Gets the thread in the inbox of the current user
     *
     * @return ThreadInterface[]
     */
    function getInboxThreads();

    /**
     * Gets the thread in the sentbox of the current user
     *
     * @return ThreadInterface[]
     */
    function getSentThreads();

    /**
     * Gets the deleted threads of the current user
     *
     * @param int $limit
     * @param int $offset
     *
     * @return ThreadInterface[]
     */
    function getDeletedThreads($limit = ThreadManager::DEFAULT_PANEL_LIMIT, $offset = 0);

    /**
     * Return count of deleted threads
     *
     * @return mixed
     */
    function countDeletedThreads();

    /**
     * Gets all threads, not deleted
     *
     * @param int $limit
     * @param int $offset
     *
     * @return ThreadInterface[]
     */
    function getAllThreads($limit = ThreadManager::DEFAULT_PANEL_LIMIT, $offset = 0);

    /**
     * Return count of threads in inbox
     *
     * @return mixed
     */
    function countAllThreads();

    /**
     * Gets a thread by its ID
     * Performs authorization checks
     * Marks the thread as read
     *
     * @return ThreadInterface
     */
    function getThread($threadId);

    /**
     * Tells how many unread messages the authenticated participant has
     *
     * @return int the number of unread messages
     */
    function getNbUnreadMessages();
}
