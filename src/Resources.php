<?php
/**
 * Resurces.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree
 * @author     alfiopiccione <alfio.piccione@gmail.com>
 * @copyright  Copyright (c) 2018, alfiopiccione
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2
 *
 * Copyright (C) 2018 alfiopiccione <alfio.piccione@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace WcElectronInvoiceFree;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Resources
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class Resources
{
    /**
     * Get file
     *
     * @since 1.0.0
     *
     * @param $args
     * @param $ext
     *
     * @return string
     */
    private function getFile($args, $ext)
    {
        $pathInfo  = pathinfo($args['file']);
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : $ext;

        if('dev' === WC_EL_INV_ENV) {
            return $args['file'];
        }

        if (file_exists(Plugin::getPluginDirPath("/assets/{$ext}") . '/' . $pathInfo['filename'] . '.min.' . $extension)) {
            $file = Plugin::getPluginDirUrl("/assets/{$ext}") . '/' . $pathInfo['filename'] . '.min.' . $pathInfo['extension'];
        } else {
            $file = $args['file'];
        }

        return $file;
    }

    /**
     * Register
     *
     * @since 1.0.0
     */
    public function register()
    {
        // Styles
        $styles = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/styles.php');

        if (is_array($styles) && ! empty($styles)) {
            foreach ($styles as $style) {
                // Register.
                if (isset($style['register']) && true === $style['register']) {
                    wp_register_style(
                        $style['handle'],
                        $this->getFile($style, 'css'),
                        $style['deps'],
                        $style['ver'],
                        $style['media']
                    );
                }
            }
        }

        // Scripts
        $scripts = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/scripts.php');

        if (is_array($scripts) && ! empty($scripts)) {
            foreach ($scripts as $script) {
                // Register.
                if (isset($script['register']) && true === $script['register']) {
                    wp_register_script(
                        $script['handle'],
                        $this->getFile($script, 'js'),
                        $script['deps'],
                        $script['ver'],
                        $script['in_footer']
                    );
                }
            }
        }
    }

    /**
     * Enqueue
     *
     * @since 1.0.0
     */
    public function enqueue()
    {
        // Styles
        $styles = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/styles.php');

        if (is_array($styles) && ! empty($styles)) {
            foreach ($styles as $style) {
                if (isset($style['enqueue']) && true === $style['enqueue']) {
                    wp_enqueue_style(
                        $style['handle'],
                        $this->getFile($style, 'css'),
                        $style['deps'],
                        $style['ver'],
                        $style['media']
                    );
                } elseif (! isset($style['enqueue']) && ! isset($style['register'])) {
                    wp_enqueue_style(
                        $style['handle'],
                        $this->getFile($style, 'css'),
                        $style['deps'],
                        $style['ver'],
                        $style['media']
                    );
                }
            }
        }

        // Scripts
        $scripts = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/scripts.php');

        if (is_array($scripts) && ! empty($scripts)) {
            foreach ($scripts as $script) {
                if (isset($script['enqueue']) && true === $script['enqueue']) {
                    wp_enqueue_script(
                        $script['handle'],
                        $this->getFile($script, 'js'),
                        $script['deps'],
                        $script['ver'],
                        $script['in_footer']
                    );
                } elseif (! isset($script['enqueue']) && ! isset($script['register'])) {
                    wp_enqueue_script(
                        $script['handle'],
                        $this->getFile($script, 'js'),
                        $script['deps'],
                        $script['ver'],
                        $script['in_footer']
                    );
                }
            }
        }

        /**
         * After
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-after_admin_enqueue_scripts');
    }

    /**
     * Localize Scripts
     *
     * @since 1.0.0
     */
    public function localizeScript()
    {
        // Localized Scripts
        $scripts = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/localizeScripts.php');

        if (is_array($scripts) && ! empty($scripts)) {
            foreach ($scripts as $handle => $data) {
                if (wp_script_is($handle, 'registered') && wp_script_is($handle, 'enqueued')) {
                    wp_localize_script(
                        $handle,
                        $handle,
                        $data
                    );
                }
            }
        }
    }
}
