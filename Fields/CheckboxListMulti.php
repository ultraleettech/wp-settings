<?php

namespace Ultraleet\WP\Settings\Fields;

class CheckboxListMulti extends AbstractField
{
    /**
     * Get the template name to load when rendering this field.
     *
     * @return string
     */
    protected function getTemplateName(): string
    {
        return 'checkbox-list-multi';
    }

    /**
     * @inheritDoc
     */
    protected function valueType()
    {
        return 'array';
    }

    /**
     * @inheritDoc
     */
    protected function getRenderParams(): array
    {
        $params = parent::getRenderParams();
        $params['options'] = self::filterIfCallbackOrFilter($this->config['options'], 'array');
        unset($params['attributes']['id']);
        return $params;
    }
}
