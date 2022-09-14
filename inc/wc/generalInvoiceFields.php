<?php
/**
 * generalInvoiceFields.php
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

// @codingStandardsIgnoreLine
$taxRegime = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/wc/taxRegime.php');

return apply_filters('wc_el_inv-general_shop_fields', array(
    array(
        'title' => esc_html__('General Options Invoice', WC_EL_INV_FREE_TEXTDOMAIN),
        'type'  => 'title',
        'desc'  => esc_html__(
            'This data will be used in the XML and PDF invoices',
            WC_EL_INV_FREE_TEXTDOMAIN
        ),
        'id'    => 'store_invoice',
    ),
    array(
        'id'          => 'wc_el_inv-general_store_your_name',
        'type'        => 'text',
        'title'       => esc_html__('Name:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => '',
        'placeholder' => esc_html__('Your Name', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
    ),
    array(
        'id'          => 'wc_el_inv-general_store_your_surname',
        'type'        => 'text',
        'title'       => esc_html__('Surname:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => '',
        'placeholder' => esc_html__('Your Surname', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
    ),
    array(
        'id'                => 'wc_el_inv-general_store_company_name',
        'type'              => 'text',
        'title'             => esc_html__('(*) Company Name:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'              => esc_html__('Please enter your company name', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder'       => esc_html__('Your Company Name', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'           => '',
        'custom_attributes' => array(
            'required' => 'required',
        ),
    ),
    array(
        'id'                => 'wc_el_inv-general_store_vat_number',
        'type'              => 'text',
        'title'             => esc_html__('(*) VAT number:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'              => esc_html__('Please enter your vat code (numbers only)', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder'       => esc_html__('Your vat number', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'           => '',
        'custom_attributes' => array(
            'required' => 'required',
        ),
    ),
    array(
        'id'                => 'wc_el_inv-general_store_tax_regime',
        'type'              => 'select',
        'title'             => esc_html__('(*) Tax Regine:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'              => esc_html__('Please select your tax regime', WC_EL_INV_FREE_TEXTDOMAIN),
        'options'           => ! empty($taxRegime['IT']) ? $taxRegime['IT'] : array(),
        'class'             => 'wc-enhanced-select',
        'default'           => '',
        'custom_attributes' => array(
            'required' => 'required',
        ),
    ),
    array(
        'id'                => 'wc_el_inv-province_business_register_office',
        'type'              => 'text',
        'title'             => esc_html__('Province business register office:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'              => esc_html__('Abbreviation of the province of the Register of Companies with which the company is registered eg: [MI], [RM], ...',
            WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder'       => esc_html__('Enter Province business register office', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'           => '',
        'custom_attributes' => array(
            'maxlength' => 2,
        ),
    ),
    array(
        'id'                => 'wc_el_inv-rea_registration_number',
        'type'              => 'text',
        'title'             => esc_html__('REA number:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'              => esc_html__('Company registration number', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder'       => esc_html__('Enter Company registration number', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'           => '',
        'custom_attributes' => array(
            'maxlength' => 20,
        ),
    ),
    array(
        'id'                => 'wc_el_inv-liquidation_status',
        'type'              => 'text',
        'title'             => esc_html__('Liquidation status:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'              => esc_html__('Indicates whether the Company is in liquidation or not [LS] = in liquidation, [LN] = not in liquidation',
            WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder'       => esc_html__('Enter liquidation status', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'           => '',
        'custom_attributes' => array(
            'maxlength' => 2,
        ),
    ),
    array(
        'id'          => 'wc_el_inv-general_store_phone',
        'type'        => 'text',
        'title'       => esc_html__('Phone number:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => '',
        'placeholder' => esc_html__('Your phone number', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
    ),
    array(
        'id'          => 'wc_el_inv-general_store_email',
        'type'        => 'text',
        'title'       => esc_html__('Email address:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => '',
        'placeholder' => esc_html__('Your email', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
    ),
    array(
        'type' => 'sectionend',
        'id'   => 'store_invoice',
    ),
    array(
        'title' => esc_html__('Electronic Invoice - Transmitter', WC_EL_INV_FREE_TEXTDOMAIN),
        'type'  => 'title',
        'desc'  => esc_html__(
                       'Enter the sender\'s data, usually provided by the brokerage service provider. (not required)',
                       WC_EL_INV_FREE_TEXTDOMAIN
                   ) .
                   sprintf(
                       "<p><strong>%s</strong></p>",
                       esc_html__('(*) If the fields are left blank, the sender\'s data will be populated with those present in the general invoice options', WC_EL_INV_FREE_TEXTDOMAIN)
                   ),
        'id'    => 'store_invoice_transmitter',
    ),
    array(
        'id'          => 'wc_el_inv-general_store_vat_number_transmitter',
        'type'        => 'text',
        'title'       => esc_html__('VAT number:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => esc_html__('Please enter vat code (numbers only)', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Transmitter vat number', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
    ),
    array(
        'id'          => 'wc_el_inv-general_store_phone_transmitter',
        'type'        => 'text',
        'title'       => esc_html__('Phone number:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => '',
        'placeholder' => esc_html__('Transmitter phone number', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
    ),
    array(
        'id'          => 'wc_el_inv-general_store_email_transmitter',
        'type'        => 'text',
        'title'       => esc_html__('Email address:', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => '',
        'placeholder' => esc_html__('Transmitter email', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
    ),
    array(
        'type' => 'sectionend',
        'id'   => 'store_invoice_transmitter',
    ),
));