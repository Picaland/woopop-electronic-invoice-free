<?php
/**
 * billingFields.php
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
$page        = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$countries   = new WC_Countries();
$euVat       = $countries->get_european_union_countries('eu_vat');
$userCountry = get_user_meta(get_current_user_id(), 'billing_country', true);

// Sdi required
$sdi         = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'billing_sdi_type', FILTER_SANITIZE_STRING);
$country     = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'billing_country', FILTER_SANITIZE_STRING);
$action      = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'action', FILTER_SANITIZE_STRING);
$sdiRequired = 'edit_address' !== $action &&
               in_array($userCountry, $euVat, true) &&
               ('IT' === $userCountry || 'IT' === $sdi) ? true : false;

// Disable Pec Unique code option
$disablePecSdi = $page->getOptions('invoice_disable_pec_sdi');
// Disable Tax code option
$disableTaxCode = $page->getOptions('invoice_disable_cf');

// Option required
$requiredOption = 'required' === $page->getOptions('invoice_required') ? true : false;
$type           = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'billing_invoice_type', FILTER_SANITIZE_STRING);

// VAT required
$required = isset($requiredOption) && true === $requiredOption &&
            ('freelance' === $type || 'company' === $type || false === $type) ? true : false;

$requiredTaxCode = $required;
if ('on' === $disableTaxCode && $type !== 'private') {
    $requiredTaxCode = false;
}

$requiredVat = $required;
if (false === $requiredOption && 'IT' !== $userCountry || 'IT' !== $country) {
    $requiredVat = false;
}

$fields = apply_filters('wc_el_inv-billing_fields', array(
    'billing_invoice_type' => array(
        'id'          => 'billing_invoice_type',
        'type'        => 'select',
        'class'       => array(
            'wc_el_inv-invoice-type',
            'form-row-wide',
        ),
        'label'       => esc_html__('Customer type', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please select Customer type', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Customer type', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $requiredOption,
        'options'     => array(
            'company'   => esc_html__('Company', WC_EL_INV_FREE_TEXTDOMAIN),
            'freelance' => esc_html__('Freelance', WC_EL_INV_FREE_TEXTDOMAIN),
            'private'   => esc_html__('Private', WC_EL_INV_FREE_TEXTDOMAIN),
        ),
    ),
    'billing_sdi_type'     => array(
        'id'          => 'billing_sdi_type',
        'type'        => 'text',
        'class'       => array(
            'wc_el_inv-sdi-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('Certified e-mail (PEC) or the unique code', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please enter your certified e-mail (PEC) or the unique code',
            WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('E-mail (PEC) or the unique code', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $sdiRequired,
    ),
    'billing_vat_number'   => array(
        'id'          => 'billing_vat_number',
        'type'        => 'text',
        'class'       => array(
            'wc_el_inv-vat-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please enter your VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $requiredVat,
    ),
    'billing_tax_code'     => array(
        'id'          => 'billing_tax_code',
        'type'        => 'text',
        'class'       => array(
            'wc_el_inv-taxcode-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please enter your Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $requiredTaxCode,
    ),
));

// Disable "billing_sdi_type" field only in front
if ('on' === $disablePecSdi && ! is_admin()) {
    $fields['billing_sdi_type']['required'] = '';
    $fields['billing_sdi_type']['class'][]  = 'hide';
    $fields['billing_sdi_type']['type']     = 'hidden';
}

return $fields;