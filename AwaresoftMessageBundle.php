<?php

namespace Awaresoft\MessageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AwaresoftMessageBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return 'FOSMessageBundle';
    }
}
