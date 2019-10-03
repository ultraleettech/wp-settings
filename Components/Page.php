<?php

namespace Ultraleet\WP\Settings\Components;

use Ultraleet\WP\Settings\Renderer;
use Ultraleet\WP\Settings\SettingsAPI;

/**
 * Class Page
 *
 * Represent a page of settings (divided into sections).
 */
class Page extends AbstractComponent
{
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
