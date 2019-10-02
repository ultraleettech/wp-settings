<?php

namespace Ultraleet\WP\Settings\Fields;

class Password extends Text
{
    /**
     * @inheritDoc
     */
    public function getRenderParams(): array
    {
        $params = parent::getRenderParams();
        $params['attributes']['type'] = 'password';
        return $params;
    }
}
