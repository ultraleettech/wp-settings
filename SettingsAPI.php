<?php

namespace Ultraleet\WP\Settings;

use Ultraleet\WP\Settings\Components\Page;

/**
 * Ultraleet Wordpress settings API library main class.
 *
 * @package ultraleet/wp-settings
 */
class SettingsAPI
{
    protected $prefix;
    protected $config;
    protected $optionNames = [];
    protected $options = [];

    /** @var Renderer */
    protected $renderer;

    /**
     * @var Page[]
     */
    private $pages;

    /**
     * Library constructor.
     *
     * @param string $prefix The identifier to prepend to option names. Usually a plugin name.
     * @param array $config Configuration array for all pages, sections, and individual fields.
     */
    public function __construct(string $prefix, array $config)
    {
        $this->prefix = "{$prefix}_settings";
        $this->config = $config;
    }

    /**
     * Return the value of a specified setting.
     *
     * @param string $field
     * @param string $section
     * @param string $page
     * @return mixed|string
     *
     * @todo Fetch default value from actual field object.
     */
    public function getSetting(string $field, string $section, string $page = '')
    {
        $optionName = $this->getPage($page)->getSection($section)->getOptionName();
        $option = $this->getOption($optionName);
        return $option[$field] ?? $this->config[$this->getPageIndex($page)]['sections'][$section]['fields'][$field]['default'];
    }

    /**
     * Fetch the settings section value.
     *
     * @param string $name
     * @return mixed
     */
    protected function getOption(string $name)
    {
        if (!isset($this->options[$name])) {
            $this->options[$name] = get_option($name, []);
        }
        return $this->options[$name];
    }

    /**
     * Resolve the name of an option of a specific settings page section.
     *
     * @param string $page
     * @param string $section
     * @return string
     */
    protected function getOptionName(string $page, string $section): string
    {
        if (!isset($this->optionNames[$page])) {
            $this->optionNames[$page] = [];
        }
        if (!isset($this->optionNames[$page][$section])) {
            $name = $this->prefix;
            $name .= $page ? "_$page" : '';
            $name .= "_$section" . '_options';
            $this->optionNames[$page][$section] = $name;
        }
        return $this->optionNames[$page][$section];
    }

    /**
     * Return the index of the first page in config in case page is not specified.
     *
     * @param string $page
     * @return string
     */
    protected function getPageIndex(string $page = ''): string
    {
        return $page ?: current(array_keys($this->config));
    }

    /**
     * Save settings for a specified section.
     *
     * @param string $page
     * @param string $section
     * @param array $values
     */
    public function saveSettingsSection(string $page, string $section, array $values)
    {
        $option = $this->getOption($this->getOptionName($page, $section));
        update_option($option, $values, false);
    }

    /**
     * Render settings page.
     *
     * @param string $pageId
     * @return string
     */
    public function renderPage(string $pageId)
    {
        $renderer = $this->getRenderer();
        return $renderer->render('settings', [
            'pages' => $this->getPages(),
            'content' => $this->getPage($pageId)->render(),
        ]);
    }

    /**
     * Get view renderer.
     *
     * @return Renderer
     */
    protected function getRenderer(): Renderer
    {
        if (!isset($this->renderer)) {
            $this->renderer = new Renderer(__DIR__ . DIRECTORY_SEPARATOR . 'Views');
        }
        return $this->renderer;
    }

    /**
     * Get settings page objects.
     *
     * @return Page[]
     */
    public function getPages(): array
    {
        foreach ($this->config as $pageId => $config) {
            $this->pages[$pageId] ?? $this->getPage($pageId);
        }
        return $this->pages;
    }

    /**
     * @param string $pageId
     * @return Page
     */
    protected function getPage(string $pageId = '')
    {
        $pageId = $pageId ?: $this->getPageIndex();
        if (!isset($this->pages[$pageId])) {
            $config = $this->config[$pageId];
            $this->pages[$pageId] = new Page($pageId, $config, $this->prefix, $this->getRenderer(), $this);
        }
        return $this->pages[$pageId];
    }
}
