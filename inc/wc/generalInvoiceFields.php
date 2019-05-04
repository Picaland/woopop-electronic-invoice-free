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

// @codingStandardsIgnoreLine
$taxRegime = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/wc/taxRegime.php');

return apply_filters('wc_el_inv-general_shop_fields', array(
    array(
        'title' => esc_html__('General Options Invoice', WC_EL_INV_FREE_TEXTDOMAIN),
        'type'  => 'title',
        'desc'  => esc_html__(
            'This is where your business is located. Tax rates and shipping rates will use this address.',
            WC_EL_INV_FREE_TEXTDOMAIN
        ),
        'id'    => 'store_invoice',
    ),
    array(
        'id'          => 'wc_el_inv-general_store_your_name',
        'type'        => 'text',
        'title'       => esc_html__('(*) Name', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => esc_html__('Please enter your name', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Your Name', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
        'desc_tip'    => true,
    ),
    array(
        'id'          => 'wc_el_inv-general_store_your_surname',
        'type'        => 'text',
        'title'       => esc_html__('(*) Surname', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => esc_html__('Please enter your surname', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Your Surname', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
        'desc_tip'    => true,
    ),
    array(
        'id'          => 'wc_el_inv-general_store_company_name',
        'type'        => 'text',
        'title'       => esc_html__('(*) Company Name', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => esc_html__('Please enter your company name', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Your Company Name', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
        'desc_tip'    => true,
    ),
    array(
        'id'          => 'wc_el_inv-general_store_vat_number',
        'type'        => 'text',
        'title'       => esc_html__('(*) VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => esc_html__('Please enter your vat code', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Your vat number', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
        'desc_tip'    => true,
    ),
    array(
        'id'       => 'wc_el_inv-general_store_tax_regime',
        'type'     => 'select',
        'title'    => esc_html__('(*) Tax Regine', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'     => esc_html__('Please select your tax regime', WC_EL_INV_FREE_TEXTDOMAIN),
        'options'  => ! empty($taxRegime['IT']) ? $taxRegime['IT'] : array(),
        'class'    => 'wc-enhanced-select',
        'default'  => '',
        'desc_tip' => true,
    ),
    array(
        'id'          => 'wc_el_inv-general_store_phone',
        'type'        => 'text',
        'title'       => esc_html__('Phone number', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => esc_html__('Please enter your phone number', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Your phone number', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
        'desc_tip'    => true,
    ),
    array(
        'id'          => 'wc_el_inv-general_store_email',
        'type'        => 'text',
        'title'       => esc_html__('Email address', WC_EL_INV_FREE_TEXTDOMAIN),
        'desc'        => esc_html__('Please enter your email', WC_EL_INV_FREE_TEXTDOMAIN),
        'placeholder' => esc_html__('Your email', WC_EL_INV_FREE_TEXTDOMAIN),
        'default'     => '',
        'desc_tip'    => true,
    ),
    array(
        'type' => 'sectionend',
        'id'   => 'store_invoice',
    ),
));