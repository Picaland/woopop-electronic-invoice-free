<?php
/**
 * addresses.php
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

$invoiceTypeLabel         = esc_html__('Type:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceTypeID            = esc_html__('Refund ID:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceNumberLabel       = esc_html__('Number:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceDateLabel         = esc_html__('Date:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceOrderNumberLabel  = esc_html__('Order:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceOrderDateLabel    = esc_html__('Order date:', WC_EL_INV_FREE_TEXTDOMAIN);
$invoiceOrderPaymentLabel = esc_html__('Payment method:', WC_EL_INV_FREE_TEXTDOMAIN);

// Translate billing and shipping label.
esc_html__('First name', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Last name', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Address 1', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('City', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('State', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Postcode', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Postcode', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Country', WC_EL_INV_FREE_TEXTDOMAIN);
esc_html__('Phone', WC_EL_INV_FREE_TEXTDOMAIN);
?>
<table class="order-data-addresses" width="100%">
    <tr>
        <?php if (! empty($data->billing) && 'receipt' !== $data->choice_type) : ?>
            <td width="33%" class="address billing-address" valign="top" style="padding:0 10px 0 0">
                <div style="vert-align:top;font-size:12px;">
                    <?php foreach ($data->billing as $key => $billing) {
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
            <td width="33%" class="address shipping-address" valign="top" style="padding:0 10px 0 0">
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
        <td width="33%" class="invoice invoice-data" valign="top" style="padding:0 10px 0 0">
            <div style="vert-align:top;font-size:12px;">
                <?php echo $invoiceTypeLabel . ' ' . $this->docType($data); ?><br>
                <?php if ('shop_order_refund' === $data->order_type): ?>
                    <?php echo $invoiceTypeID . ' ' . $data->id; ?><br>
                <?php endif; ?>
                <?php echo $invoiceNumberLabel . ' ' . $this->invoiceNumber($data); ?><br>
                <?php echo $invoiceDateLabel . ' ' . $this->dateCompleted($data, 'd-m-Y'); ?><br>
                <?php echo $invoiceOrderNumberLabel . ' ' . $this->docID($data); ?><br>
                <?php echo $invoiceOrderDateLabel . ' ' . $this->dateOrder($data, 'd-m-Y'); ?><br>
                <?php echo $invoiceOrderPaymentLabel . ' ' . $this->paymentMethod($data); ?><br>
            </div>
        </td>
    </tr>
</table>
