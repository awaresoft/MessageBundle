<?php

namespace Awaresoft\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\MessageBundle\Entity\MessageMetadata as BaseMessageMetadata;

/**
 * @ORM\MappedSuperclass
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class MessageMetadata extends BaseMessageMetadata
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\MessageBundle\Entity\Message", inversedBy="metadata")
     *
     * @var \FOS\MessageBundle\Model\MessageInterface
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     *
     * @var \FOS\MessageBundle\Model\ParticipantInterface
     */
    protected $participant;
}