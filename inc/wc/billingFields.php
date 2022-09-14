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

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$page        = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$countries   = new WC_Countries();
$euVat       = $countries->get_european_union_countries();
$userCountry = get_user_meta(get_current_user_id(), 'billing_country', true);

// Sdi required
$sdi           = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'billing_sdi_type', FILTER_UNSAFE_RAW);
$country       = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'billing_country', FILTER_UNSAFE_RAW);
$choiceDocType = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'billing_choice_type', FILTER_UNSAFE_RAW);
$action        = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'action', FILTER_UNSAFE_RAW);
$sdiRequired   = 'edit_address' !== $action &&
                 in_array($userCountry, $euVat, true) &&
                 ('IT' === $userCountry || 'IT' === $country) ? true : false;
// Disable Pec Unique code option
$disablePecSdi = $page->getOptions('invoice_disable_pec_sdi');
// Disable Tax code option
$disableTaxCode = $page->getOptions('invoice_disable_cf');
// Choice type
$choiceType = $page->getOptions('invoice_choice_type');
// Hide extra UE
$hideExtraUe = $page->getOptions('hide_outside_ue');

// Option required
$requiredOption = 'required' === $page->getOptions('invoice_required') ? true : false;
$type           = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'billing_invoice_type', FILTER_UNSAFE_RAW);

// Init VAT required
$required = isset($requiredOption) && true === $requiredOption &&
            ('freelance' === $type || 'company' === $type || '' === $type) ? true : false;

// Set required tax code
$requiredTaxCode = $required;
// Set not required if field disabled and private customer
if ('on' === $disableTaxCode && $type !== 'private') {
    $requiredTaxCode = false;
}

// Set required invoice type
$requiredInvoiceType = true;

// Set required choice type
$requiredChoiceType = true;

// Set not required vat and invoice type if no IT
// Used on process checkout
$requiredVat = $required;
if (false === $requiredOption && ($userCountry && 'IT' !== $userCountry || $country && 'IT' !== $country)) {
    $requiredVat         = false;
    $requiredInvoiceType = false;
}

// Set not required for doc type is receipt
if ('receipt' === $choiceDocType) {
    $requiredInvoiceType = $sdiRequired = $requiredVat = $requiredTaxCode = false;
}

// Set required vat for Extra UE vat required (freelance and company)
if (! in_array($country, $euVat) && $requiredOption && $type !== 'private') {
    $requiredVat = true;
}

// Set Extra UE tax code required (private)
if (! in_array($country, $euVat) && $requiredOption && $type === 'private') {
    $requiredTaxCode = true;
}

// Check doc type and set required invoice type
if ('invoice' === $choiceDocType) {
    $requiredInvoiceType = true;
}

// No UE and hide set all fields not required
// Used on process checkout
if ('hide' === $hideExtraUe && ! in_array($country, $euVat)) {
    $requiredInvoiceType = $sdiRequired = $requiredVat = $requiredTaxCode = $requiredChoiceType = false;
}

// No IT and hide set choice type field not required
// Used on process checkout
if ('hide' === $hideExtraUe && ! empty($country) && 'IT' !== $country) {
    $requiredChoiceType = false;
}

if('' === $type) {
    $sdiRequired = $requiredVat = $requiredTaxCode = false;
}

$fields = array(
    // Choice document type
    'billing_choice_type'  => array(
        'id'          => 'billing_choice_type',
        'type'        => 'select',
        'class'       => array(
            'woo_pop-' . WC_EL_INV_FREE_VERSION_CLASS,
            'wc_el_inv-type-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('Choose the type of document', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => '',
        'placeholder' => esc_html__('Choose the type of document', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $requiredChoiceType,
        'default'     => '',
        'options'     => array(
            ''        => esc_html__('Choose the type of document', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice' => esc_html_x('Invoice', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN),
            'receipt' => esc_html_x('Receipt', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN),
        ),
    ),
    // Invoice type
    'billing_invoice_type' => array(
        'id'          => 'billing_invoice_type',
        'type'        => 'select',
        'class'       => array(
            'woo_pop-' . WC_EL_INV_FREE_VERSION_CLASS,
            'wc_el_inv-invoice-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('Customer type', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please select Customer type', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Customer type', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $requiredInvoiceType,
        'default'     => '',
        'options'     => array(
            ''          => esc_html__('Customer type', WC_EL_INV_FREE_TEXTDOMAIN),
            'company'   => esc_html__('Company', WC_EL_INV_FREE_TEXTDOMAIN),
            'freelance' => esc_html__('Freelance', WC_EL_INV_FREE_TEXTDOMAIN),
            'private'   => esc_html__('Private', WC_EL_INV_FREE_TEXTDOMAIN),
        ),
    ),
    // Sdi
    'billing_sdi_type'     => array(
        'id'          => 'billing_sdi_type',
        'type'        => 'text',
        'class'       => array(
            'woo_pop-' . WC_EL_INV_FREE_VERSION_CLASS,
            'wc_el_inv-sdi-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('Certified e-mail (PEC) or the unique code', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please enter your certified e-mail (PEC) or the unique code',
            WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('E-mail (PEC) or the unique code', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $sdiRequired,
    ),
    // Vat
    'billing_vat_number'   => array(
        'id'          => 'billing_vat_number',
        'type'        => 'text',
        'class'       => array(
            'woo_pop-' . WC_EL_INV_FREE_VERSION_CLASS,
            'wc_el_inv-vat-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please enter your VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $requiredVat,
    ),
    // Tax code
    'billing_tax_code'     => array(
        'id'          => 'billing_tax_code',
        'type'        => 'text',
        'class'       => array(
            'woo_pop-' . WC_EL_INV_FREE_VERSION_CLASS,
            'wc_el_inv-taxcode-field',
            'form-row-wide',
        ),
        'label'       => esc_html__('Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
        'description' => esc_html__('Please enter your Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
        'required'    => $requiredTaxCode,
    ),
);

// Remove Choice type select
if ('on' !== $choiceType && ! is_admin()) {
    unset($fields['billing_choice_type']);
}

// Disable "billing_sdi_type" field only in front
if ('on' === $disablePecSdi && ! is_admin()) {
    $fields['billing_sdi_type']['required'] = '';
    $fields['billing_sdi_type']['class'][]  = 'hide';
    $fields['billing_sdi_type']['type']     = 'hidden';
}

return apply_filters('wc_el_inv-billing_fields', $fields);
