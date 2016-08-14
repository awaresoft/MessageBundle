<?php

namespace Awaresoft\MessageBundle\EntityManager;

use Application\UserBundle\Entity\User;
use Awaresoft\MessageBundle\Entity\Thread;
use FOS\MessageBundle\EntityManager\ThreadManager as BaseThreadManager;
use FOS\MessageBundle\Model\ParticipantInterface;

/**
 * Extended ORM ThreadManager.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ThreadManager extends BaseThreadManager
{
    /**
     * Default panel limit
     */
    const DEFAULT_PANEL_LIMIT = 10;

    /**
     * Finds not deleted threads for a participant,
     * containing at least one message not written by this participant,
     * ordered by last message not written by this participant in reverse order.
     * In one word: an inbox.
     *
     * @param ParticipantInterface $participant
     * @param int $limit
     * @param int $offset
     *
     * @return \FOS\MessageBundle\Model\ThreadInterface[]
     */
    public function findParticipantAllThreads(ParticipantInterface $participant, $limit = self::DEFAULT_PANEL_LIMIT, $offset = 0)
    {
        return $this->getParticipantAllThreadsQueryBuilder($participant, $limit, $offset)
            ->getQuery()
            ->execute();
    }

    /**
     * Finds not deleted threads from a participant,
     * containing at least one message written by this participant,
     * ordered by last message written by this participant in reverse order.
     * In one word: an sentbox.
     *
     * @param ParticipantInterface $participant
     * @param $limit
     * @param $offset
     *
     * @return Builder a query builder suitable for pagination
     */
    public function getParticipantAllThreadsQueryBuilder(ParticipantInterface $participant, $limit, $offset)
    {
        return $this->repository->createQueryBuilder('t')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')
            // the participant is in the thread participants
            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $participant->getId())
            // the thread does not contain spam or flood
            ->andWhere('t.isSpam = :isSpam')
            ->setParameter('isSpam', false, \PDO::PARAM_BOOL)
            // the thread is not deleted by this participant
            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false, \PDO::PARAM_BOOL)
            // sort by date of last message written by this participant
            ->orderBy('tm.lastParticipantMessageDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function findParticipantDeletedThreadsWithLimit(ParticipantInterface $participant, $limit, $offset)
    {
        return $this->getParticipantDeletedThreadsQueryBuilderWithLimit($participant, $limit, $offset)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getParticipantDeletedThreadsQueryBuilderWithLimit(ParticipantInterface $participant, $limit, $offset)
    {
        return $this->repository->createQueryBuilder('t')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')
            // the participant is in the thread participants
            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $participant->getId())
            // the thread is deleted by this participant
            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', true, \PDO::PARAM_BOOL)
            // sort by date of last message
            ->orderBy('tm.lastMessageDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
    }

    /**
     * Return count of threads
     *
     * @param $participant
     *
     * @return int
     */
    public function countParticipantAllThreads($participant)
    {
        return $this->repository->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')
            // the participant is in the thread participants
            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $participant->getId())
            // the thread does not contain spam or flood
            ->andWhere('t.isSpam = :isSpam')
            ->setParameter('isSpam', false, \PDO::PARAM_BOOL)
            // the thread is not deleted by this participant
            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false, \PDO::PARAM_BOOL)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Return count of deleted threads
     *
     * @param $participant
     *
     * @return int
     */
    public function countParticipantDeletedThreads($participant)
    {
        return $this->repository->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')
            // the participant is in the thread participants
            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $participant->getId())
            // the thread is deleted by this participant
            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', true, \PDO::PARAM_BOOL)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find thread by sender and receiver
     *
     * @param User $creator
     * @param User $participant
     *
     * @return Thread
     */
    public function findThreadByParticipants(User $creator, User $participant)
    {
        $qb = $this->repository->createQueryBuilder('t');
        $qb2 = $this->repository->createQueryBuilder('t2');
        $qb
            ->join('t.metadata', 'm')
            ->where(
                $qb->expr()->in(
                    'm.thread',
                    $qb2
                        ->join('t2.metadata', 'm2')
                        ->where('m2.participant = :participant OR m2.participant = :creator')
                        ->groupBy('m2.thread')
                        ->having('COUNT(m2.thread) = :count')
                        ->getDQL()
                )
            )
            ->andWhere('m.participant = :participant OR m.participant = :creator')
            ->setParameters(array(
                'participant' => $participant,
                'creator' => $creator,
                'count' => '2'
            ))
            ->groupBy('m.thread');

        return $qb->getQuery()->getOneOrNullResult();
    }
}
