<?php

namespace Awaresoft\MessageBundle\FormFactory;

use FOS\MessageBundle\Model\ThreadInterface;
use FOS\MessageBundle\FormFactory\ReplyMessageFormFactory as BaseReplyMessageFormFactory;

/**
 * Extended message forms
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ReplyMessageFormFactory extends BaseReplyMessageFormFactory
{
    /**
     * @inheritdoc
     */
    public function create(ThreadInterface $thread)
    {
        return $this->formFactory->createNamed($this->formName, $this->formType, null, [
            'thread' => $thread,
        ]);
    }
}
