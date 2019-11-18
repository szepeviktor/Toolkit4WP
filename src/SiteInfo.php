<?php

/**
 * Helper functions for site information.
 *
 * @package Toolkit4WP
 * @author  Viktor Szépe <viktor@szepe.net>
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use LogicException;

/**
 * Provide information on core paths and URLs.
 */
class SiteInfo
{
    /**
     * Site info.
     *
     * @var array
     */
    protected $info = [];

    /**
     * Set paths and URLs.
     *
     * @see https://codex.wordpress.org/Determining_Plugin_and_Content_Directories
     */
    protected function init(): void
    {
        $uploadPathAndUrl = wp_upload_dir();
        $this->info = [
            // Core
            'site_path'     => ABSPATH,
            'site_url'      => trailingslashit(site_url()),
            'home_path'     => get_home_path(),
            'home_url'      => trailingslashit(get_home_url()),
            'includes_path' => trailingslashit(ABSPATH . WPINC),
            'includes_url'  => includes_url(),

            // Content
            'content_path' => trailingslashit(WP_CONTENT_DIR),
            'content_url'  => trailingslashit(content_url()),
            'uploads_path' => trailingslashit($uploadPathAndUrl['basedir']),
            'uploads_url'  => trailingslashit($uploadPathAndUrl['baseurl']),

            // Plugins
            'plugins_path'    => trailingslashit(WP_PLUGIN_DIR),
            'plugins_url'     => trailingslashit(plugins_url()),
            'mu_plugins_path' => trailingslashit(WPMU_PLUGIN_DIR),
            'mu_plugins_url'  => trailingslashit(WPMU_PLUGIN_URL),

            // Themes
            'themes_root_path'  => trailingslashit(get_theme_root()),
            'themes_root_url'   => trailingslashit(get_theme_root_uri()),
            'parent_theme_path' => trailingslashit(get_template_directory()),
            'parent_theme_url'  => trailingslashit(get_template_directory_uri()),
            'child_theme_path'  => trailingslashit(get_stylesheet_directory()),
            'child_theme_url'   => trailingslashit(get_stylesheet_directory_uri()),
        ];
    }

    protected function setInfo(): void
    {
        if ($this->info !== []) {
            return;
        }
        if (! \did_action('init')) {
            throw new LogicException('SiteInfo must be used in "init" action and later.');
        }

        $this->init();
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getPath(string $name)
    {
        $this->setInfo();

        $key = $name . '_path';
        if (! \array_key_exists($key, $this->info)) {
            return null;
        }
        return $this->info[$key];
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getUrl(string $name)
    {
        $this->setInfo();

        $key = $name . '_url';
        if (! \array_key_exists($key, $this->info)) {
            return null;
        }
        return $this->info[$key];
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getUrlBasename(string $name)
    {
        $url = $this->getUrl($name);
        if (null === $url) {
            return null;
        }
        return \basename($url);
    }

    public function usingChildTheme(): bool
    {
        $this->setInfo();

        return ($this->info['parent_theme_path'] !== $this->info['child_theme_path']);
    }

    public function isUploadsWritable(): bool
    {
        $this->setInfo();

        $uploadsDir = $this->info['uploads_path'];

        return \file_exists($uploadsDir) && \is_writeable($uploadsDir);
    }
}
