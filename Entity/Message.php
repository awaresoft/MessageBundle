<?php

namespace Awaresoft\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\MessageBundle\Entity\Message as BaseMessage;

/**
 * @ORM\MappedSuperclass
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Message extends BaseMessage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\MessageBundle\Entity\Thread", inversedBy="messages")
     *
     * @var \FOS\MessageBundle\Model\ThreadInterface
     */
    protected $thread;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     *
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $sender;

    /**
     * @ORM\OneToMany(targetEntity="Awaresoft\MessageBundle\Entity\MessageMetadata", mappedBy="message", cascade={"all"})
     *
     * @var MessageMetadata[]|\Doctrine\Common\Collections\Collection
     */
    protected $metadata;
}