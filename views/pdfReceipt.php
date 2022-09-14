<?php
/**
 * pdfReceipt.php
 *
 * @since      1.0.0
 * @package    ${NAMESPACE}
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

// Dompdf
$pdfObj = \WcElectronInvoiceFree\Pdf\CreatePdf::$pdf;
// Options init
$options              = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$invoiceHtml          = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init()->getOptions('invoice_html');
$invoiceHtmlFromQuery = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'html_to_pdf', FILTER_UNSAFE_RAW);
// Invoice type
$type = isset($data->invoice_type) ? $data->invoice_type : null;
// File name
$fileName = \WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields::getGeneralInvoiceOptionCountryState() .
            \WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields::getGeneralInvoiceOptionVatNumber() . '_' .
            $this->progressiveFileNumber($data) . '.pdf'; ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?php echo esc_attr($fileName); ?></title>
        <style>
            #watermark {
                position:         fixed;
                top:              45%;
                width:            100%;
                text-align:       center;
                opacity:          .25;
                font-size:        100px;
                line-height:      .6rem;
                transform:        rotate(-45deg);
                transform-origin: 50% 50%;
                z-index:          -1000;
                color:            lightgrey;
            }
        </style>
    </head>
    <body>
    <?php
    // Watermark for only IT
    if (! empty($data->billing) &&
        'IT' === $data->billing['country'] &&
        'no' === apply_filters('wc_el_inv-disable_watermark', 'no')
    ) {
        $watermarkText = apply_filters('wc_el_inv-receipt_watermark', esc_html__('Receipt', WC_EL_INV_FREE_TEXTDOMAIN)); ?>
        <div id="watermark">
            <?php echo $watermarkText; ?>
        </div>
    <?php }
    ?>
    <div class="doc-wrapper">
        <?php
        /**
         * PDF template before content
         */
        do_action('wc_el_inv-pdf_template_before_content') ?>
        <div id="pdf-content" style="max-width:1024px;margin:0 auto;">
            <!-- head -->
            <?php $this->pdfHead($data); ?>

            <!-- doc type -->
            <?php echo sprintf('<h1 style="font-size:18px;">%s</h1>',
                esc_html__($this->docType($data), WC_EL_INV_FREE_TEXTDOMAIN)
            ); ?>

            <?php do_action('wc_el_inv-after_document_label_pdf', $pdfObj, $data); ?>

            <!-- order addresses -->
            <?php $this->pdfAddresses($data); ?>

            <?php do_action('wc_el_inv-before_order_details_pdf', $pdfObj, $data); ?>

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

            <?php do_action('wc_el_inv-after_order_details_pdf', $pdfObj, $data); ?>

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
if ('on' === $invoiceHtml || 'true' === $invoiceHtmlFromQuery) {
    die();
}

$html = ob_get_contents();
ob_end_clean();

// Create PDF and browser preview
$pdfObj->loadHtml($html);
$pdfObj->setPaper('A4', 'portrait');
$pdfObj->render();
// 1 = download
// 0 = preview
$pdfObj->stream($fileName, array('Accept-Ranges' => 1, 'Attachment' => 0));
die();
