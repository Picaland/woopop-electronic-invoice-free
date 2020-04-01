<?php
/**
 * localizeScripts.php
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

$page      = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$options   = $page->getOptions();
$countries = new \WC_Countries();

$country = is_user_logged_in() ?
    get_user_meta(get_current_user_id(), 'billing_country', true) :
    $countries->get_base_country();

$euVatCountry = $countries->get_european_union_countries();

$scripts = array();

if (! is_admin()) {
    $scripts = array_merge($scripts, array(
        'wc_el_inv_invoice' => array(
            'ajax_url'                => untrailingslashit(admin_url('admin-ajax.php')),
            'ajax_nonce'              => wp_create_nonce('wc_el_inv_ajax-ajax_nonce'),
            'required_text'           => esc_html__('required', WC_EL_INV_FREE_TEXTDOMAIN),
            'not_required_text'       => esc_html__('not required', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice_required'        => 'required' === $page->getOptions('invoice_required') ? 1 : 0,
            'hide_outside_ue'         => $page->getOptions('hide_outside_ue'),
            'disable_pec_sdi'         => $page->getOptions('invoice_disable_pec_sdi'),
            'disable_cf'              => $page->getOptions('invoice_disable_cf'),
            'country'                 => $country ?: '',
            'user_country'            => get_user_meta(get_current_user_id(), 'billing_country', true) ?: '',
            'eu_vat_country'          => $euVatCountry ?: '',
            'sdi_label'               => esc_html__('Certified e-mail (PEC) or the unique code', WC_EL_INV_FREE_TEXTDOMAIN),
            'sdi_placeholder'         => esc_html__('E-mail (PEC) or the unique code', WC_EL_INV_FREE_TEXTDOMAIN),
            'sdi_description'         => esc_html__('Please enter your certified e-mail (PEC) or the unique code',
                WC_EL_INV_FREE_TEXTDOMAIN),
        ),
    ));
} else {
    $scripts = array_merge($scripts, array(
        'wc_el_inv_admin' => array(
            'ajax_url'                          => untrailingslashit(admin_url('admin-ajax.php')),
            'ajax_nonce'                        => wp_create_nonce('wc_el_inv_ajax-ajax_nonce'),
            'required_text'                     => esc_html__('required', WC_EL_INV_FREE_TEXTDOMAIN),
            'not_required_text'                 => esc_html__('not required', WC_EL_INV_FREE_TEXTDOMAIN),
            'text_edit'                         => esc_html__('Edit', WC_EL_INV_FREE_TEXTDOMAIN),
            'text_save'                         => esc_html__('Save', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice_sent_confirm'              => esc_html__(
                'WARNING: You are about to set the "SENT to invoice" status, do you want to confirm?',
                WC_EL_INV_FREE_TEXTDOMAIN
            ),
            'invoice_undo_confirm'              => esc_html__(
                'WARNING: You are about to set the "NOT SENT to invoice" status, do you want to confirm?',
                WC_EL_INV_FREE_TEXTDOMAIN
            ),
            'bulk_invoice_cb'                   => esc_html__(
                'First you must select one or more checkbox',
                WC_EL_INV_FREE_TEXTDOMAIN
            ),
            'refund_item_disabled_text'         => esc_html__(
                'Refund disabled: first you need to send the generated credit note',
                WC_EL_INV_FREE_TEXTDOMAIN
            ),
            'refund_amount_read_only_info_text' => esc_html__(
                'Before sending the invoice you cannot make a refund, on the total but only on the lines of the order',
                WC_EL_INV_FREE_TEXTDOMAIN
            ),
        ),
    ));
}

return apply_filters('wc_el_inv-scripts_localize_list', $scripts);