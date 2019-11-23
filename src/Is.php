<?php

/**
 * Helper functions to determine entry point.
 *
 * @package Toolkit4WP
 * @author  Viktor Szépe <viktor@szepe.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use WP_User;

/**
 * Various request helpers.
 */
class Is
{
    /**
     * Whether given user is an administrator.
     *
     * @param \WP_User $user The given user.
     * @return bool
     */
    public static function admin(WP_User $user): bool
    {
        return \is_multisite() ? \user_can($user, 'manage_network') : \user_can($user, 'manage_options');
    }

    /**
     * Whether the current user is not logged in.
     *
     * @return bool
     */
    public static function anonymousUsers(): bool
    {
        return (! \is_user_logged_in());
    }

    /**
     * Whether the current user is a comment author.
     *
     * @return bool
     */
    public static function commentAuthor(): bool
    {
        // phpcs:ignore WordPress.VIP.RestrictedVariables.cache_constraints___COOKIE
        return isset($_COOKIE['comment_author_' . \COOKIEHASH]);
    }

    /**
     * Whether current webserver interface is CLI.
     *
     * @return bool
     */
    public static function cli(): bool
    {
        return ('cli' === \php_sapi_name());
    }

    /**
     * Whether current request is of the given type.
     *
     * All of them are available even before 'muplugins_loaded' action,
     * exceptions are commented.
     *
     * @param string $type Type of request.
     * @return bool
     */
    public static function request(string $type): bool
    {
        switch ($type) {
            case 'installing':
                return (\defined('WP_INSTALLING') && \WP_INSTALLING === true);
            case 'index':
                return (\defined('WP_USE_THEMES') && \WP_USE_THEMES === true);
            case 'frontend':
                // Use !frontend for admin pages.
                return ((! \is_admin() || \wp_doing_ajax() ) && ! \wp_doing_cron());
            case 'admin':
                // Includes admin-ajax :(
                return \is_admin();
            case 'async-upload':
                return (
                    isset($_SERVER['SCRIPT_FILENAME'])
                    && \ABSPATH . 'wp-admin/async-upload.php' === $_SERVER['SCRIPT_FILENAME']
                );
            // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
            case 'preview': // in 'parse_query' action if(is_main_query())
                return \is_preview() || \is_customize_preview();
            case 'autosave': // after 'heartbeat_received', 500 action
                // Autosave post while editing and Heartbeat.
                return (\defined('DOING_AUTOSAVE') && \DOING_AUTOSAVE === true);
            case 'rest': // after 'parse_request' action
                return (\defined('REST_REQUEST') && \REST_REQUEST === true);
            case 'ajax':
                return \wp_doing_ajax();
            case 'xmlrpc':
                return (\defined('XMLRPC_REQUEST') && \XMLRPC_REQUEST === true);
            case 'trackback': // in 'parse_query'
                return \is_trackback();
            case 'search': // in 'parse_query'
                return \is_search();
            case 'feed': // in 'parse_query'
                return \is_feed();
            case 'robots': // in 'parse_query'
                return \is_robots();
            case 'cron':
                return \wp_doing_cron();
            case 'wp-cli':
                return (\defined('WP_CLI') && \WP_CLI === true);
            default:
                \_doing_it_wrong(__METHOD__, \esc_html(\sprintf('Unknown request type: %s', $type)), '0.1.0');
                return false;
        }
    }
}
