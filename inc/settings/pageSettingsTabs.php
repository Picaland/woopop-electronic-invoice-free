<?php
/**
 * pageSettingsTabs.php
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
$tabs = array(
    // General tab.
    'general'     => array(
        'header'     => array(
            '<i class="dashicons dashicons-admin-settings"></i>',
            esc_html__('General', WC_EL_INV_FREE_TEXTDOMAIN),
        ),
        'section_id' => 'setting_section_general',
        'submit'     => false,
    ),
    // WooCommerce Integration.
    'wc-integration' => array(
        'header'     => array(
            '<i class="dashicons dashicons-admin-plugins"></i>',
            esc_html__('WooCommerce Integration', WC_EL_INV_FREE_TEXTDOMAIN),
        ),
        'section_id' => 'setting_section_wc-integration',
        'submit'     => true,
    ),
    // Invoice.
    'invoice'     => array(
        'header'     => array(
            '<i class="dashicons dashicons-media-text"></i>',
            esc_html__('Invoice options', WC_EL_INV_FREE_TEXTDOMAIN),
        ),
        'section_id' => 'setting_section_invoice',
        'submit'     => true,
    ),
    // Xml tab.
    'xml'         => array(
        'header'     => array(
            '<i class="dashicons dashicons-media-code"></i>',
            esc_html__('Invoices', WC_EL_INV_FREE_TEXTDOMAIN),
        ),
        'section_id' => 'setting_section_xml',
        'submit'     => false,
    ),
);

return apply_filters('wc_el_inv-page_settings_tab', $tabs);
