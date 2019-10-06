<?php

namespace Ultraleet\WP\Settings\Components;

use Ultraleet\WP\Settings\Renderer;
use Ultraleet\WP\Settings\SettingsAPI;
use Ultraleet\WP\Settings\Exceptions\MissingArgumentException;
use Ultraleet\WP\Settings\Traits\SupportsOptionalCallbacksAndFilters;

/**
 * Class Page
 *
 * Represent a page of settings (divided into sections).
 */
class Page extends AbstractComponent
{
    use SupportsOptionalCallbacksAndFilters;

    protected $id;
    protected $title;
    protected $config;
    protected $prefix;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var SettingsAPI
     */
    protected $api;

    /**
     * @var Section[]
     */
    protected $sections;

    /**
     * Page constructor.
     *
     * @param string $id
     * @param array $config
     * @param string $prefix
     * @param Renderer $renderer
     * @param SettingsAPI $api
     */
    public function __construct(string $id, array $config, string $prefix, Renderer $renderer, SettingsAPI $api)
    {
        $this->id = $id;
        $this->title = $config['title'];
        $this->config = $config;
        $this->prefix = "{$prefix}_$id";
        $this->renderer = $renderer;
        $this->api = $api;
    }

    /**
     * Register configured admin assets for rendering the page.
     */
    public function registerAssets()
    {
        if (!empty($this->config['assets']) && $this->api->isCurrentPage($this->id)) {
            $assets = $this->config['assets'];
            $deps = $this->api->getScriptDependencies();
            if (! empty($assets['styles'])) {
                $this->enqueueStyles($assets['styles']);
            }
            if (! empty($assets['scripts'])) {
                $this->enqueueScripts($assets['scripts']);
            }
            if (isset($assets['json'])) {
                $defaults = [
                    'position' => 'after',
                ];
                $config = array_merge($defaults, $assets['json']);
                if (empty($config['handle'])) {
                    throw new MissingArgumentException("JSON configuration for page '{$this->id}' is missing a 'handle' argument.");
                }
                $data = $this->printJsonScript($config['data']);
                wp_add_inline_script($config['handle'], $data, $config['position']);
            }
        }
    }

    /**
     * Enqueues stylesheets configured for this page.
     *
     * @param array $styles
     */
    protected function enqueueStyles(array $styles)
    {
        $defaultConfig = [
            'dependencies' => [],
            'media' => 'screen',
        ];
        foreach ($styles as $handle => $config) {
            $config = array_merge($defaultConfig, $config);
            $dependencies = $this->api->getStyleDependencies() + $config['dependencies'];
            wp_enqueue_style(
                $handle,
                $this->api->getAssetsPath($config['path']),
                $dependencies,
                WP_DEBUG ? time() : '',
                $config['media']
            );
        }
    }

    /**
     * Enqueues scripts configured for this page.
     *
     * @param array $scripts
     */
    protected function enqueueScripts(array $scripts)
    {
        $defaultConfig = [
            'dependencies' => [],
            'inFooter' => false,
        ];
        foreach ($scripts as $handle => $config) {
            $config = array_merge($defaultConfig, $config);
            $dependencies = $this->api->getScriptDependencies() + $config['dependencies'];
            wp_enqueue_script(
                $handle,
                $this->api->getAssetsPath($config['path']),
                $dependencies,
                WP_DEBUG ? time() : '',
                $config['inFooter']
            );
        }
    }

    /**
     * Render JSON configuration used by scripts on this page.
     *
     * @param $data
     * @return string
     */
    protected function printJsonScript($data)
    {
        return sprintf($this->api->getJsonSetter(), $this->filterJsonData($data));
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function filterJsonData($data)
    {
        return json_encode(self::filterIfCallbackOrFilter($data));
    }

    /**
     * Renders the settings page.
     *
     * @return string
     */
    public function render(): string
    {
        $content = [];
        foreach ($this->getSections() as $id => $section) {
            $content[$id] = $section->render();
        }
        return $this->renderer->render('page', [
            'title' => $this->title,
            'pages' => $this->api->getPages(),
            'currentPageId' => $this->id,
            'sectionContent' => $content,
        ]);
    }

    /**
     * @param string $id
     * @return Section|null
     */
    public function getSection(string $id)
    {
        $sections = $this->getSections();
        return $sections[$id] ?? null;
    }

    /**
     * @return Section[]
     */
    public function getSections()
    {
        if (!isset($this->sections)) {
            $this->sections = [];
            foreach ($this->config['sections'] as $id => $config) {
                if ($this->isSectionEnabled($id)) {
                    $this->sections[$id] = new Section($id, $config, $this->prefix, $this->renderer);
                }
            }
        }
        return $this->sections;
    }

    /**
     * @param string $section
     * @return bool
     */
    protected function isSectionEnabled(string $section): bool
    {
        $value = $this->config['sections'][$section]['enabled'] ?? true;
        if (is_bool($value)) {
            return $value;
        } elseif (is_callable($value)) {
            return (bool) call_user_func($value);
        } elseif (is_string($value) && has_filter($value)) {
            return (bool) apply_filters($value, true);
        }
        return (bool) $value;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}
