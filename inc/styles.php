<?php
/**
 * style.php
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

$styles = array();

// Get the Environment.
$dev = ! ! ('dev' === WC_EL_INV_ENV);

if (! is_admin()) {
    $styles = array_merge($styles, array(
        array(
            'handle' => 'wc_el_inv-front_style',
            'file'   => \WcElectronInvoiceFree\Plugin::getPluginDirUrl('assets/css/wc-inv-front.css'),
            'deps'   => array(),
            'ver'    => $dev ? time() : WC_EL_INV_FREE_VERSION,
            'media'  => 'all',
        ),
    ));
} else {
    $styles = array_merge($styles, array(
        array(
            'handle' => 'jquery-ui',
            'file'   => '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
            'deps'   => array(),
            'ver'    => $dev ? time() : WC_EL_INV_FREE_VERSION,
            'media'  => 'all',
        ),
        array(
            'handle' => 'wc_el_inv-admin_style',
            'file'   => \WcElectronInvoiceFree\Plugin::getPluginDirUrl('assets/css/wc-inv-admin.css'),
            'deps'   => array(),
            'ver'    => $dev ? time() : WC_EL_INV_FREE_VERSION,
            'media'  => 'all',
        ),
    ));
}

return apply_filters('wc_el_inv-styles_list', $styles);
