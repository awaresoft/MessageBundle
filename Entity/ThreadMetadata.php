<?php

namespace Awaresoft\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\MessageBundle\Entity\ThreadMetadata as BaseThreadMetadata;

/**
 * @ORM\MappedSuperclass
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ThreadMetadata extends BaseThreadMetadata
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\MessageBundle\Entity\Thread", inversedBy="metadata")
     *
     * @var \FOS\MessageBundle\Model\ThreadInterface
     */
    protected $thread;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     *
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $participant;
}