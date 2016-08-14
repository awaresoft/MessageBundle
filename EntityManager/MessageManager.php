<?php

namespace Awaresoft\MessageBundle\EntityManager;

use FOS\MessageBundle\EntityManager\MessageManager as BaseMessageManager;
use FOS\MessageBundle\Model\MessageInterface;
use FOS\MessageBundle\Model\ThreadInterface;

/**
 * Extended ORM MessageManager.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class MessageManager extends BaseMessageManager
{
    /**
     * Default panel limit
     */
    const DEFAULT_PANEL_LIMIT = 10;

    /**
     * Return messages for selected thread
     *
     * @param ThreadInterface $thread
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     *
     * @return MessageInterface[]
     */
    public function findMessagesByThread(ThreadInterface $thread, $limit = self::DEFAULT_PANEL_LIMIT, $offset = 0, $orderBy = ['createdAt' => 'ASC'])
    {
        $orderKey = array_keys($orderBy)[0];

        return $this->repository->createQueryBuilder('m')
            ->where('m.thread = :thread')
            ->setParameters(array(
                'thread' => $thread,
            ))
            ->orderBy('m.' . $orderKey, $orderBy[$orderKey])
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Return count of thread's messages
     *
     * @param ThreadInterface $thread
     *
     * @return int
     */
    public function countMessagesByThread(ThreadInterface $thread)
    {
        return $this->repository->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.thread = :thread')
            ->setParameters(array(
                'thread' => $thread,
            ))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
