<?php

namespace Awaresoft\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\MessageBundle\Entity\Thread as BaseThread;

/**
 * @ORM\MappedSuperclass
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Thread extends BaseThread
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     *
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $createdBy;

    /**
     * @ORM\OneToMany(targetEntity="Awaresoft\MessageBundle\Entity\Message", mappedBy="thread")
     *
     * @var Message[]|\Doctrine\Common\Collections\Collection
     */
    protected $messages;

    /**
     * @ORM\OneToMany(targetEntity="Awaresoft\MessageBundle\Entity\ThreadMetadata", mappedBy="thread", cascade={"all"})
     *
     * @var ThreadMetadata[]|\Doctrine\Common\Collections\Collection
     */
    protected $metadata;
}