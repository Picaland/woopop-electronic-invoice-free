<?php
/**
 * scripts.php
 *
 * @since      1.0.0
 * @package    ${NAMESPACE}
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

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$scripts = array();

// Get the Environment.
$dev = ! ! ('dev' === WC_EL_INV_ENV);

// Get current lang.
$lang = substr(get_bloginfo('language'), 0, 2);

if (! is_admin()) {
    $scripts = array_merge($scripts, array(
        array(
            'handle'    => 'wc_el_inv_invoice',
            'file'      => \WcElectronInvoiceFree\Plugin::getPluginDirUrl('assets/js/invoiceFields.js'),
            'deps'      => array(),
            'ver'       => $dev ? time() : WC_EL_INV_FREE_VERSION,
            'in_footer' => true,
            'enqueue'   => is_account_page() || is_checkout() ?: false,
        ),
    ));
} else {
    $scripts = array_merge($scripts, array(
        array(
            'handle'    => 'wc_el_inv_admin',
            'file'      => \WcElectronInvoiceFree\Plugin::getPluginDirUrl('assets/js/admin.js'),
            'deps'      => array('underscore', 'jquery'),
            'ver'       => $dev ? time() : WC_EL_INV_FREE_VERSION,
            'in_footer' => true,
            'enqueue'   => true,
        ),

        array(
            'handle'    => 'wc_el_inv_datepicker',
            'file'      => \WcElectronInvoiceFree\Plugin::getPluginDirUrl('assets/js/datePicker.js'),
            'deps'      => array('underscore', 'jquery', 'jquery-ui-datepicker'),
            'ver'       => $dev ? time() : WC_EL_INV_FREE_VERSION,
            'in_footer' => true,
            'register'  => true,
        ),
    ));

    // DatePicker Language.
    if (file_exists(\WcElectronInvoiceFree\Plugin::getPluginDirPath("/assets/js/datepicker-lang/datepicker-{$lang}.js"))) {

        $scripts[] = array(
            'handle'    => 'datepicker-lang',
            'file'      => \WcElectronInvoiceFree\Plugin::getPluginDirUrl("/assets/js/datepicker-lang/datepicker-{$lang}.js"),
            'deps'      => array('jquery', 'jquery-ui-datepicker'),
            'ver'       => $dev ? time() : WC_EL_INV_FREE_VERSION,
            'in_footer' => true,
            'register'  => true,
        );
    }
}

return apply_filters('wc_el_inv-scripts_list', $scripts);
