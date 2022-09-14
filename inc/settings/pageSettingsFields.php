<?php
/**
 * pageSettingsFields.php
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

// No SoapClient message
$noSoapMessage = '';
if (! class_exists('SoapClient')) {
    $noSoapMessage = '****** ' .
                     __('the SoapClient service is not active on your server. The VIES control will always be true',
                         WC_EL_INV_FREE_TEXTDOMAIN) .
                     ' ******';
}

$page = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$tabs = $this->tabs;

$active  = $this->checkPageTab($tabs);
$section = isset($tabs[$active]['section_id']) ? $tabs[$active]['section_id'] : '';

$invoiceNumber         = $page->getOptions('number_next_invoice');
$invoiceNumberDisabled = isset($invoiceNumber) && '' !== $invoiceNumber ? 'disabled' : '';

switch ($section) {
    // General Tab
    case 'setting_section_general' :
        $this->sectionArgs['wc_el_inv_settings'] = array(
            'section_id'       => 'setting_section_general',
            'section_title'    => esc_html__('General options', WC_EL_INV_FREE_TEXTDOMAIN),
            'section_callback' => array($this, 'sectionSettingsGeneralDescription'),
            'section_page'     => 'wc_el_inv-options-page',
        );
        break;
    // Invoice
    case 'setting_section_invoice':
        $this->sectionArgs['wc_el_inv_settings'] = array(
            'section_id'       => 'setting_section_invoice',
            'section_title'    => esc_html__('Invoice settings', WC_EL_INV_FREE_TEXTDOMAIN),
            'section_callback' => '',
            'section_page'     => 'wc_el_inv-options-page',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'add_stamp_duty_options_fields',
            'field_title'    => esc_html__('Add virtual stamp duty on invoices', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                    'id'          => 'wc_el_inv-settings-add_stamp_duty',
                    'name'        => 'wc_el_inv-settings-add_stamp_duty',
                    'label'       => esc_html__('Add virtual stamp', WC_EL_INV_FREE_TEXTDOMAIN),
                    'description' => esc_html__('The duty stamp must be applied when electronic invoices (and pdfs) are issued without VAT and if the amount exceeds 77.47€.',
                        WC_EL_INV_FREE_TEXTDOMAIN),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'prefix_invoice_number_options_fields',
            'field_title'    => esc_html__('Prefix invoice number', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Text(array(
                    'id'   => 'wc_el_inv-settings-prefix_invoice_number',
                    'name' => 'wc_el_inv-settings-prefix_invoice_number',
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'number_digits_in_invoice_options_fields',
            'field_title'    => esc_html__('Number of digits (enter 4 to display 13 as 0013)',
                WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Text(array(
                    'type'        => 'number',
                    'id'          => 'wc_el_inv-settings-number_digits_in_invoice',
                    'name'        => 'wc_el_inv-settings-number_digits_in_invoice',
                    'description' => sprintf('%s',
                        esc_html__('Recommended 4 (you can leave it blank)', WC_EL_INV_FREE_TEXTDOMAIN)
                    ),
                    'attrs'       => array(
                        'min' => 1,
                        'max' => 6,
                    ),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'number_next_invoice_options_fields',
            'field_title'    => esc_html__('Number of the next invoice', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Text(array(
                    'type'        => 'number',
                    'id'          => 'wc_el_inv-settings-number_next_invoice',
                    'name'        => 'wc_el_inv-settings-number_next_invoice',
                    'description' => sprintf('%s',
                        esc_html__('Enter a number from which to start generating the invoice numbering',
                            WC_EL_INV_FREE_TEXTDOMAIN)
                    ),
                    'attrs'       => array(
                        'required' => 'required',
                        'min'      => 1,
                        'disabled' => $invoiceNumberDisabled,
                    ),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'disable_invoice_number_order_zero_options_fields',
            'field_title'    => esc_html__('Disable invoice if total zero', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                    'id'          => 'wc_el_inv-settings-disable_invoice_number_order_zero',
                    'name'        => 'wc_el_inv-settings-disable_invoice_number_order_zero',
                    'label'       => esc_html__('Disable increment', WC_EL_INV_FREE_TEXTDOMAIN),
                    'description' => esc_html__('Disable invoice number increment if order total is zero.',
                        WC_EL_INV_FREE_TEXTDOMAIN),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'suffix_invoice_number_options_fields',
            'field_title'    => esc_html__('Suffix invoice number', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Text(array(
                    'id'   => 'wc_el_inv-settings-suffix_invoice_number',
                    'name' => 'wc_el_inv-settings-suffix_invoice_number',
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'suffix_year_invoice_number_options_fields',
            'field_title'    => esc_html__('Suffix year', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                    'id'          => 'wc_el_inv-settings-suffix_year_invoice_number',
                    'name'        => 'wc_el_inv-settings-suffix_year_invoice_number',
                    'label'       => esc_html__('Set the year as a suffix', WC_EL_INV_FREE_TEXTDOMAIN),
                    'description' => esc_html__('Selecting the option the year of order creation will be set as a suffix.',
                        WC_EL_INV_FREE_TEXTDOMAIN),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'invoice_choice_type_options_fields',
            'field_title'    => esc_html__('Invoice or Receipt', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                    'id'          => 'wc_el_inv-settings-invoice_choice_type',
                    'name'        => 'wc_el_inv-settings-invoice_choice_type',
                    'label'       => esc_html__('Check to activate', WC_EL_INV_FREE_TEXTDOMAIN),
                    'description' => esc_html__('Activate the select for choosing the type of document (invoice or receipt)',
                        WC_EL_INV_FREE_TEXTDOMAIN),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'invoice_disable_pec_sdi_options_fields',
            'field_title'    => esc_html__('Disable PEC/SDI', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                    'id'          => 'wc_el_inv-settings-invoice_disable_pec_sdi',
                    'name'        => 'wc_el_inv-settings-invoice_disable_pec_sdi',
                    'label'       => esc_html__('Disable Pec / Unique Code field in (checkout / my-account)',
                        WC_EL_INV_FREE_TEXTDOMAIN),
                    'description' => sprintf('%s',
                        esc_html__('Disable the Pec / Unique Code field in the checkout (the recipient code will be set to "0000000")',
                            WC_EL_INV_FREE_TEXTDOMAIN)
                    ),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'invoice_disable_cf_options_fields',
            'field_title'    => esc_html__('Disable Fiscal Code', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                    'id'          => 'wc_el_inv-settings-invoice_disable_cf',
                    'name'        => 'wc_el_inv-settings-invoice_disable_cf',
                    'label'       => esc_html__('Disable the tax code field in (checkout / my-account)',
                        WC_EL_INV_FREE_TEXTDOMAIN),
                    'description' => sprintf('%s',
                        esc_html__('Disable the tax code from the checkout and from my-account (only for companies and freelancers)',
                            WC_EL_INV_FREE_TEXTDOMAIN)
                    ),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'invoice_vies_check_options_fields',
            'field_title'    => esc_html__('Vies Check', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => function () {
                echo '<strong style="color:red">' . WC_EL_INV_PREMIUM . '</strong>';
            },
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'invoice_active_js_cf_check_options_fields',
            'field_title'    => esc_html__('Verify Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => function () {
                echo '<strong style="color:red">' . WC_EL_INV_PREMIUM . '</strong>';
            },
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_invoice',
        );

        // Only Dompdf exists
        if (class_exists('\Dompdf\Dompdf')) {
            $this->fieldsArgs[] = array(
                'field_id'       => 'invoice_in_my_orders_options_fields',
                'field_title'    => esc_html__('My Orders invoice', WC_EL_INV_FREE_TEXTDOMAIN),
                'field_callback' => array(
                    new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                        'id'          => 'wc_el_inv-settings-invoice_in_my_orders',
                        'name'        => 'wc_el_inv-settings-invoice_in_my_orders',
                        'label'       => esc_html__('Check to activate', WC_EL_INV_FREE_TEXTDOMAIN),
                        'description' => esc_html__('Activate to view the link to download the pdf in the list of orders in my account',
                            WC_EL_INV_FREE_TEXTDOMAIN),
                    ), $this, $page),
                    'field',
                ),
                'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
                'field_section'  => 'setting_section_invoice',
            );
            $this->fieldsArgs[] = array(
                'field_id'       => 'invoice_via_email_options_fields',
                'field_title'    => esc_html__('Email invoice', WC_EL_INV_FREE_TEXTDOMAIN),
                'field_callback' => array(
                    new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                        'id'          => 'wc_el_inv-settings-invoice_via_email',
                        'name'        => 'wc_el_inv-settings-invoice_via_email',
                        'label'       => esc_html__('Check to activate', WC_EL_INV_FREE_TEXTDOMAIN),
                        'description' => esc_html__('Active the sending of the invoice in PDF format via e-mail upon completion of the order',
                            WC_EL_INV_FREE_TEXTDOMAIN),
                    ), $this, $page),
                    'field',
                ),
                'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
                'field_section'  => 'setting_section_invoice',
            );
            $this->fieldsArgs[] = array(
                'field_id'       => 'invoice_html_options_fields',
                'field_title'    => esc_html__('Invoice HTML', WC_EL_INV_FREE_TEXTDOMAIN),
                'field_callback' => array(
                    new \WcElectronInvoiceFree\Admin\Settings\Fields\Checkbox(array(
                        'id'          => 'wc_el_inv-settings-invoice_html',
                        'name'        => 'wc_el_inv-settings-invoice_html',
                        'label'       => esc_html__('Check to activate', WC_EL_INV_FREE_TEXTDOMAIN),
                        'description' => esc_html__('View the invoice (pdf) in HTML - (activate only if the PDF generation presents problems)',
                            WC_EL_INV_FREE_TEXTDOMAIN),
                    ), $this, $page),
                    'field',
                ),
                'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
                'field_section'  => 'setting_section_invoice',
            );
            $this->fieldsArgs[] = array(
                'field_id'       => 'invoice_pdf_logo_url_options_fields',
                'field_title'    => esc_html__('Invoice PDF Logo', WC_EL_INV_FREE_TEXTDOMAIN),
                'field_callback' => array(
                    new \WcElectronInvoiceFree\Admin\Settings\Fields\Text(array(
                        'type'        => 'url',
                        'id'          => 'wc_el_inv-settings-invoice_pdf_logo_url',
                        'name'        => 'wc_el_inv-settings-invoice_pdf_logo_url',
                        'description' => esc_html__('Enter the url of the logo for the invoice (.jpg format)',
                            WC_EL_INV_FREE_TEXTDOMAIN),
                    ), $this, $page),
                    'field',
                ),
                'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
                'field_section'  => 'setting_section_invoice',
            );
            $this->fieldsArgs[] = array(
                'field_id'       => 'invoice_pdf_footer_options_fields',
                'field_title'    => esc_html__('Invoice pdf footer text', WC_EL_INV_FREE_TEXTDOMAIN),
                'field_callback' => array(
                    new \WcElectronInvoiceFree\Admin\Settings\Fields\TextArea(array(
                        'id'          => 'wc_el_inv-settings-invoice_pdf_footer',
                        'name'        => 'wc_el_inv-settings-invoice_pdf_footer',
                        'description' => esc_html__('Enter the text for the PDF invoice footer.',
                            WC_EL_INV_FREE_TEXTDOMAIN),
                    ), $this, $page),
                    'field',
                ),
                'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
                'field_section'  => 'setting_section_invoice',
            );
        }
        break;
    // WC Checkout
    case 'setting_section_wc-checkout':
        $this->sectionArgs['wc_el_inv_settings'] = array(
            'section_id'       => 'setting_section_wc-checkout',
            'section_title'    => esc_html__('General WooCommerce integration settings', WC_EL_INV_FREE_TEXTDOMAIN),
            'section_callback' => '',
            'section_page'     => 'wc_el_inv-options-page',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'invoice_required_options_fields',
            'field_title'    => esc_html__('VAT number and tax code, always required',
                WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Radio(array(
                    'id'          => 'wc_el_inv-settings-invoice_required',
                    'name'        => 'wc_el_inv-settings-invoice_required',
                    'description' => esc_html__('Choose if you want the VAT / Tax Code playing field is never optional. 
                    If it is NOT always required, follow these rules:', WC_EL_INV_FREE_TEXTDOMAIN),
                    'options'     => array(
                        'required'     => esc_html__('Required', WC_EL_INV_FREE_TEXTDOMAIN),
                        'not-required' => esc_html__('Not required (IT always required)', WC_EL_INV_FREE_TEXTDOMAIN),
                    ),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_wc-checkout',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => '',
            'field_title'    => '',
            'field_callback' => array($page, 'fieldsVatRules'),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_wc-checkout',
        );
        $this->fieldsArgs[]                      = array(
            'field_id'       => 'hide_outside_ue_options_fields',
            'field_title'    => esc_html__('(1) Hide the VAT number and tax code field if the billing address is not in the European Union?',
                WC_EL_INV_FREE_TEXTDOMAIN),
            'field_callback' => array(
                new \WcElectronInvoiceFree\Admin\Settings\Fields\Radio(array(
                    'id'          => 'wc_el_inv-settings-hide_outside_ue',
                    'name'        => 'wc_el_inv-settings-hide_outside_ue',
                    'description' => esc_html__('Choose if you want to hide the VAT number or tax code field when the billing address is not in the European Union',
                        WC_EL_INV_FREE_TEXTDOMAIN),
                    'options'     => array(
                        'show' => esc_html__('Always show the VAT number / tax code field', WC_EL_INV_FREE_TEXTDOMAIN),
                        'hide' => esc_html__('Hide the VAT number and tax code when the customer billing address is outside the European Union',
                            WC_EL_INV_FREE_TEXTDOMAIN),
                    ),
                ), $this, $page),
                'field',
            ),
            'field_page'     => $this->sectionArgs['wc_el_inv_settings']['section_page'],
            'field_section'  => 'setting_section_wc-checkout',
        );
        break;
    // Xml Invoice Tab
    case 'setting_section_xml' :
        $this->sectionArgs['wc_el_inv_settings'] = array(
            'section_id'       => 'setting_section_xml',
            'section_title'    => array(
                //$icon . '%1$s' . $mode,
                esc_html__('Invoice table', WC_EL_INV_FREE_TEXTDOMAIN),
            ),
            // After option form
            'section_callback' => function () {
                new \WcElectronInvoiceFree\Admin\Settings\Fields\XmlOrdersList();
            },
            'section_page'     => 'wc_el_inv-options-page',
        );
        break;
    default:
        $this->sectionArgs = array();
        break;
}
