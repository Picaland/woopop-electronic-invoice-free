<?php
/**
 * pdfTemplate.php
 *
 * @since      1.0.0
 * @author     alfiopiccione <alfio.piccione@gmail.com>
 * @copyright  Copyright (c) 2019, alfiopiccione
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2
 *
 * Copyright (C) 2019 alfiopiccione <alfio.piccione@gmail.com>
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

ob_start();

if (! $data) {
    return false;
}

$options = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();

// Invoice type
$type = isset($data->invoice_type) ? $data->invoice_type : null;

// File name
$fileName = \WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields::getGeneralInvoiceOptionCountryState() .
            \WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields::getGeneralInvoiceOptionVatNumber() . '_' .
            $this->progressiveFileNumber($data) . '.pdf';

?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?php echo esc_attr($fileName); ?></title>
    </head>
    <body>
    <div class="doc-wrapper">
        <?php
        /**
         * PDF template before content
         */
        do_action('wc_el_inv-pdf_template_before_content') ?>
        <div id="pdf-content">
            <!-- head -->
            <?php $this->pdfHead($data); ?>

            <?php echo sprintf('<h1 style="font-size:18px;">%s</h1>', esc_html__('INVOICE', WC_EL_INV_FREE_TEXTDOMAIN)); ?>

            <?php do_action('wc_el_inv-after_document_label_pdf', $this->pdf, $data); ?>

            <!-- order addresses -->
            <?php $this->pdfAddresses($data); ?>

            <?php do_action('wc_el_inv-before_order_details_pdf', $this->pdf, $data); ?>

            <?php
            // Initialized
            $orderTotals    = 0;
            $orderTaxTotals = 0;
            ?>
            <!-- order details -->
            <?php $this->pdfDetails($data, $orderTotals, $orderTaxTotals); ?>

            <!-- order details totals -->
            <?php $this->pdfOrderTotals($data); ?>

            <!-- order summary -->
            <?php $this->pdfSummary($data); ?>

            <?php do_action('wc_el_inv-after_order_details_pdf', $this->pdf, $data); ?>

            <?php if (! empty($data->customer_note)) : ?>
                <!-- customer notes -->
                <table class="customer-notes" width="100%" style="margin-top:2em;">
                    <tr>
                        <td style="border-bottom:1px solid #ddd;font-size:12px;">
                            <div class="customer-notes">
                                <h3><?php esc_html_e('Customer Notes', WC_EL_INV_FREE_TEXTDOMAIN); ?></h3>
                                <p><?php echo \WcElectronInvoiceFree\Functions\stripTags($data->customer_note); ?></p>
                            </div>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php do_action('wc_el_inv-after_order_customer_note_pdf', $this->pdf, $data); ?>

            <!-- footer -->
            <?php $this->pdfFooter($data); ?>
        </div>
        <?php
        /**
         * PDF template after content
         */
        do_action('wc_el_inv-pdf_template_after_content') ?>
    </div>
    </body>
    </html>
<?php
// Invoice HTML view.
if ('on' === \WcElectronInvoiceFree\Admin\Settings\OptionPage::init()->getOptions('invoice_html')) {
    die();
}

$html = ob_get_contents();
ob_end_clean();

// PDF output.
$this->pdf->WriteHTML(trim($html));
// Send PDF in the browser
$this->pdf->Output($fileName, 'I');
die();
