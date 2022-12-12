<?php
/**
 * addresses.php
 *
 * @since      2.0.0
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

$invoiceTypeID                  = esc_html__('Refund ID:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceNumberLabel             = esc_html__('Number:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceDateLabel               = esc_html__('Date:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceOrderNumberLabel        = esc_html__('Order:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceOrderNumberRelatedLabel = esc_html__('Order related:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceOrderDateLabel          = esc_html__('Order date:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceOrderPaymentLabel       = esc_html__('Payment method:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceReverseCharge           = esc_html__('Nature operations:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceReverseChargeRefNorm    = esc_html__('Ref. Normative:', WC_EL_INV_FREE_TEXTDOMAIN);

// Translate billing and shipping label.
esc_html__('First name', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Last name', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Address 1', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Address 2', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('City', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('State', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Postcode', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Postcode', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Country', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Phone', WC_EL_INV_FREE_TEXTDOMAIN);

// Reverse charge data
$order = isset($data->parent_id) && 0 !== $data->parent_id ?
    // shop refund order
    wc_get_order($data->parent_id) :
    // shop order
    wc_get_order($data->id);

$nature  = $order->get_meta('nature_rc');
$refNorm = $order->get_meta('ref_norm_rc');
?>
<table class="order-data-addresses" style="width:100%">
    <tr>
        <?php if (! empty($data->billing) && 'receipt' !== $data->choice_type) : ?>
            <td class="address billing-address"
                style="vertical-align:top;padding:0 10px 0 0;width:33%;vertical-align:top;">
                <h5 style="margin-bottom:10px"><?php esc_html_e('Billing address', WC_EL_INV_FREE_TEXTDOMAIN); ?></h5>
                <div style="vert-align:top;font-size:12px;">
                    <?php
                    foreach ($data->billing as $key => $billing) {
                        if (isset($data->billing[$key]) && '' !== $data->billing[$key]) {
                            echo sprintf('%s: %s<br>',
                                esc_html__(ucfirst(str_replace('_', ' ', $key)), WC_EL_INV_FREE_TEXTDOMAIN),
                                $data->billing[$key]);
                        }
                    }

                    if ($this->customerVatNumber($data)) {
                        echo sprintf('%s: %s<br>', esc_html__('VAT', WC_EL_INV_FREE_TEXTDOMAIN),
                            $this->customerVatNumber($data));
                    }

                    if ($this->customerTaxCodeNumber($data)) {
                        echo sprintf('%s: %s<br>', esc_html__('Tax code', WC_EL_INV_FREE_TEXTDOMAIN),
                            $this->customerTaxCodeNumber($data));
                    }

                    if ($this->codeOrPec($data, 'pec')) {
                        echo sprintf('%s: %s<br>', esc_html__('Email PEC', WC_EL_INV_FREE_TEXTDOMAIN),
                            $this->codeOrPec($data, 'pec'));
                    }
                    if ($this->codeOrPec($data, 'code')) {
                        echo sprintf('%s: %s<br>', esc_html__('Web-service code', WC_EL_INV_FREE_TEXTDOMAIN),
                            $this->codeOrPec($data, 'code'));
                    } ?>
                </div>
            </td>
        <?php endif; ?>
        <?php
        $shipping = isset($data->shipping) ? array_filter($data->shipping) : array();
        if (! empty($shipping)) : ?>
            <td class="address shipping-address"
                style="vertical-align:top;padding:0 10px 0 0;width:33%;vertical-align:top;">
                <h5 style="margin-bottom:10px"><?php esc_html_e('Shipping address', WC_EL_INV_FREE_TEXTDOMAIN); ?></h5>
                <div style="vert-align:top;font-size:12px;">
                    <?php
                    if (! empty($data->shipping)) {
                        foreach ($data->shipping as $key => $shipping) {
                            if (isset($data->shipping[$key]) && '' !== $data->shipping[$key]) {
                                echo sprintf('%s: %s<br>',
                                    esc_html__(ucfirst(str_replace('_', ' ', $key)), WC_EL_INV_FREE_TEXTDOMAIN),
                                    $data->shipping[$key]);
                            }
                        }
                    } else {
                        echo sprintf('%s<br>', 'N/A');
                    } ?>
                </div>
            </td>
        <?php endif; ?>
        <td class="invoice invoice-data" style="©;padding:0 10px 0 0;width:33%;vertical-align:top;">
            <h5 style="margin-bottom:10px"><?php esc_html_e('Payment', WC_EL_INV_FREE_TEXTDOMAIN); ?></h5>
            <div style="vertical-align:top;font-size:12px;">
                <?php if ('shop_order_refund' === $data->order_type): ?>
                    <?php echo $invoiceTypeID . ' ' . $data->id; ?><br>
                <?php endif; ?>
                <?php echo $invoiceNumberLabel . ' ' . $this->invoiceNumber($data); ?><br>
                <?php if ('shop_order_refund' === $data->order_type): ?>
                    <?php echo $invoiceDateLabel . ' ' . $this->dateOrder($data, 'd-m-Y'); ?><br>
                <?php else: ?>
                    <?php echo $invoiceDateLabel . ' ' . $this->dateInvoice($data, 'd-m-Y'); ?><br>
                <?php endif; ?>
                <?php if ('shop_order_refund' === $data->order_type): ?>
                    <p style="border-bottom:1px solid #ddd"></p>
                    <?php echo $invoiceOrderNumberRelatedLabel . ' ' . $this->docID($data); ?><br>
                <?php else: ?>
                    <?php echo $invoiceOrderNumberLabel . ' ' . $this->docID($data); ?><br>
                <?php endif; ?>
                <?php if ('shop_order_refund' === $data->order_type): ?>
                    <?php echo $invoiceOrderDateLabel . ' ' . $this->dateCompleted($data, 'd-m-Y', true); ?><br>
                <?php endif; ?>

                <?php if ('RF19' === WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields::getGeneralInvoiceOptionTaxRegime()) : ?>
                    <?php echo $invoiceReverseCharge . ' ' . 'N2.2 (non soggette)'; ?><br>
                <?php else : ?>
                    <?php if (($nature && $refNorm) || (floatval(0) === floatval($data->total_tax) && $nature && $refNorm)) : ?>
                        <?php echo $invoiceReverseCharge . ' ' . $nature; ?><br>
                        <?php echo $invoiceReverseChargeRefNorm . ' ' . $refNorm; ?><br>
                    <?php endif; ?>
                <?php endif; ?>
                <p style="border-bottom:1px solid #ddd"></p>
                <?php echo $invoiceOrderPaymentLabel . ' ' . $this->paymentMethod($data); ?><br>
            </div>
        </td>
    </tr>
</table>
