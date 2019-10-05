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

        add_action('wp_loaded', [$this, 'savePage']);
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
    public function getSettingValue(string $field, string $section, string $page = '')
    {
        $page = $this->getPageIndex($page);
        $optionName = $this->getOptionName($page, $section);
        $option = $this->getOption($optionName);
        return $option[$field] ?? $this->getPage($page)->getSection($section)->getField($field)->getDefaultValue();
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
     * Render settings page.
     *
     * @param string $pageId
     * @return string
     */
    public function renderPage(string $pageId)
    {
        $renderer = $this->getRenderer();
        return $renderer->render('settings', [
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
        $pages = [];
        foreach ($this->config as $pageId => $config) {
            $pages[$pageId] = $this->pages[$pageId] ?? $this->getPage($pageId);
        }
        return $this->pages = $pages;
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

    /**
     * Save all settings sections when a settings page form is submitted.
     */
    public function savePage()
    {
        if (! isset($_REQUEST['ultraleet_save_settings'])) {
            return;
        }
        $pageId = $this->getPageIndex($_GET['tab'] ?? '');
        check_admin_referer("save_settings_$pageId");
        foreach ($this->getPage($pageId)->getSections() as $sectionId => $section) {
            $section->saveSettings();
        }
        wp_safe_redirect($_SERVER['HTTP_REFERER']);
        exit;
    }

    public function getOptionName(string $page, string $section): string
    {
        return $this->getPage($page)->getSection($section)->getOptionName();
    }
}
