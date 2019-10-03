<?php

namespace Ultraleet\WP\Settings\Fields;

use Ultraleet\WP\Settings\Renderer;
use Ultraleet\WP\Settings\Exceptions\NoValueException;
use Ultraleet\WP\Settings\Exceptions\InvalidTypeException;

/**
 * Option field base class.
 */
abstract class AbstractField
{
    protected $id;
    protected $config;
    protected $name;
    protected $value;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * AbstractField constructor.
     *
     * @param string $id
     * @param array $config
     * @param string $prefix
     * @param Renderer $renderer
     */
    public function __construct(string $id, array $config, string $prefix, Renderer $renderer)
    {
        $this->id = $id;
        $this->config = $config;
        $this->name = "{$prefix}[$id]";
        $this->renderer = $renderer;
    }

    /**
     * Get the template name to load when rendering this field.
     *
     * @return string
     */
    abstract protected function getTemplateName(): string;

    /**
     * Override for fields that have different value types (such as array for multiple choice fields).
     *
     * @return string
     */
    protected function valueType()
    {
        return 'string';
    }

    /**
     * Override for field types that need a different default value than an empty string/array/object.
     *
     * @return mixed
     */
    protected function default()
    {
        $default = null;
        settype($default, $this->valueType());
        return $default;
    }

    /**
     * Override and return false for valueless fields (such as section headings).
     *
     * @return bool
     */
    public function hasValue(): bool
    {
        return true;
    }

    /**
     * Set parameters to send to the render function.
     *
     * Adds as much available data from configuration as possible.
     * Field classes should extend this and add their own parameters.
     *
     * @return array
     */
    protected function getRenderParams(): array
    {
        $params = [];
        if ($this->hasValue()) {
            $params['label'] = $this->config['label'] ?? str_replace('_', ' ', ucfirst($this->id));
            $params['name'] = $this->name;
            $params['attributes'] = [
                'id' => $this->config['id'] ?? str_replace('_', '-', $this->id),
                'class' => "setting_{$this->config['type']}",
            ];
            $params['value'] = $this->getValue();
        }
        return $params;
    }

    /**
     * Renders the field and returns the HTML string.
     *
     * @return string
     */
    final public function render(): string
    {
        $templateName = $this->getTemplateName();
        return $this->renderer->render("fields/$templateName", $this->getRenderParams());
    }

    /**
     * @return mixed|string
     */
    public function getValue()
    {
        if (! $this->hasValue()) {
            $name = str_replace('_', ' ', ucfirst($this->config['type']));
            throw new NoValueException("$name field does not support values.");
        }
        return $this->value ?? $this->getDefaultValue();
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        if (! isset($this->config['default'])) {
            return $this->default();
        }
        return static::filterIfCallbackOrFilter($this->config['default'], $this->valueType());
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        if (! $this->hasValue()) {
            $name = str_replace('_', ' ', ucfirst($this->config['type']));
            throw new NoValueException("$name field does not support values.");
        }
        $this->value = $value;
    }

    /**
     * Filters a variable through a callable or WP filter if one exists. Otherwise returns it unchanged.
     *
     * Type checks are performed at every stage. In the end, the function will attempt to cast the value
     * to the provided type (currently only works for values supported by settype() and not class names).
     * In case of failure, throws an exception.
     *
     * @param $value
     * @param string $type
     * @param bool $strict
     * @return mixed|void
     *
     * @todo Move to a more global space so it can be reused in other projects.
     */
    protected static function filterIfCallbackOrFilter($value, $type = 'array', $strict = false)
    {
        $error = false;
        $varType = gettype($value);
        if (is_callable($value)) {
            $value = call_user_func($value);
            if (static::isCorrectType($value, $type)) {
                return $value;
            }
            $error = "Provided callable did not return the correct type ($type).";
        } elseif (has_filter($value)) {
            $value = apply_filters($value, null);
            if (static::isCorrectType($value, $type)) {
                return $value;
            }
            $error = "Provided filter '$value' did not return the correct type ($type).";
        }
        if (($error || !static::isCorrectType($value, $type)) && ($strict || !settype($value, $type))) {
            $error = $error ?: "Provided value is not of and cannot be cast to the correct type ($type).";
        }
        if ($error) {
            throw new InvalidTypeException($error);
        }
        return $value;
    }

    private static function isCorrectType($variable, $type): bool
    {
        $resultType = gettype($variable);
        return $type == $resultType || ('object' == $resultType && static::isInstanceOf($variable, $type));
    }

    /**
     * Check whether or not we have an instance or descendant of a given class or interface.
     *
     * @param object $object
     * @param string $classOrInterface
     * @return bool
     */
    private static function isInstanceOf(object $object, string $classOrInterface): bool
    {
        if (is_a($object, $classOrInterface)) {
            return true;
        }
        return is_subclass_of($object, $classOrInterface, false);
    }
}
