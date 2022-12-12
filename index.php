<?php
/**
 * WooPop
 *
 * Plugin Name: WooPop Free -> (Fattura Elettronica)
 * Plugin URI: https://woopop.it
 * Description: <code><strong>WooPop Electronic Invoice (Versione Gratuita)</strong></code>, è integrato con woocommerce, Raccoglie i dati per la generazione del file XML per la fatturazione elettronica, ed inserisce in backend e in frontend i campi necessari alla fatturazione elettronica. Passa alla <strong><a href="https://woopop.it/?ref=1&free_desc">VERSIONE PREMIUM</a></strong>
 * Version: 3.1.3
 * Author: alfiopiccione <alfio.piccione@gmail.com>
 * Author URI: https://alfiopiccione.com
 * WC requires at least: 3.2.4
 * WC tested up to: 7.1.x
 * License GPL 2 Text
 * Domain: el-inv
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

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (! defined('WC_EL_INV_ENV')) {
    define('WC_EL_INV_ENV', 'prod');
}

// Define constant.
define('WC_EL_INV_PREMIUM', '(Premium Version)');
define('WC_EL_INV_FREE_NAME', 'Electronic Invoice');
define('WC_EL_INV_FREE_TEXTDOMAIN', 'el-inv');
define('WC_EL_INV_FREE_VERSION', '3.1.3');
define('WC_EL_INV_FREE_VERSION_CLASS', str_replace('.', '_', WC_EL_INV_FREE_VERSION));
// Plugin DIR
define('WC_EL_INV_FREE_PLUGIN_DIR', basename(plugin_dir_path(__FILE__)));
// Dirs
define('WC_EL_INV_FREE_DIR', plugin_dir_path(__FILE__));
// Uri
define('WC_EL_INV_FREE_URL', plugin_dir_url(__FILE__));

// Base Requirements.
require_once untrailingslashit(WC_EL_INV_FREE_DIR . '/src/Plugin.php');
require_once untrailingslashit(WC_EL_INV_FREE_DIR . '/requires.php');

// Register the activation hook.
register_activation_hook(__FILE__, array('WcElectronInvoiceFree\\Activate', 'activate'));
register_deactivation_hook(__FILE__, array('WcElectronInvoiceFree\\Deactivate', 'deactivate'));

// Init
add_action('plugins_loaded', function () {
    // Prevent init plugin in other ajax action
    // WooPop use only "markInvoice" ajax action
    if (! is_admin() && defined('DOING_AJAX') && DOING_AJAX &&
        isset($_REQUEST['action']) && 'markInvoice' !== $_REQUEST['action']
    ) {
        return;
    }

    // Load plugin text-domain.
    load_plugin_textdomain('el-inv', false, '/' . WC_EL_INV_FREE_PLUGIN_DIR . '/languages/');
    // Check for the dependency.
    if (\WcElectronInvoiceFree\Functions\isWooCommerceActive()) :
        $filters = array();

        // Global filter
        $filters = array_merge(
            $filters,
            include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/filters.php')
        );
        // Admin filter
        if (is_admin()) {
            $filters = array_merge(
                $filters,
                include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/filtersAdmin.php')
            );
        } // Front filter
        else {
            $filters = array_merge(
                $filters,
                include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/filtersFront.php')
            );
        }

        // Loader init.
        $init = new WcElectronInvoiceFree\Init(new WcElectronInvoiceFree\Loader(), $filters);
        $init->init();

        // Settings plugin init.
        \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
    else :
        // WooCommerce not active, lets disable the plugin.
        \WcElectronInvoiceFree\Functions\disablePlugin();
    endif;
}, 10);
