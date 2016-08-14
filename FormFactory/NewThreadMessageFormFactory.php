<?php

namespace Awaresoft\MessageBundle\FormFactory;

use FOS\MessageBundle\FormFactory\NewThreadMessageFormFactory as BaseNewThreadMessageFormFactory;

/**
 * Extended message forms factory
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class NewThreadMessageFormFactory extends BaseNewThreadMessageFormFactory
{
    /**
     * @inheritdoc
     */
    public function create()
    {
        return $this->formFactory->createNamed($this->formName, $this->formType);
    }
}
