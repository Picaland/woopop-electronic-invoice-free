<?php
/**
 * details.php
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

// Initialized
global $orderTotals, $orderTaxTotals, $summaryRate, $freeRefund;
$orderTotals = $orderTaxTotals = 0;
$summaryRate = array();
$currency    = apply_filters('wc_el_inv-pdf_currency_symbol', get_woocommerce_currency_symbol($data->currency));
$freeRefund  = false;
$country     = $data->billing['country'];
?>
<table class="order-details" width="100%" style="margin-top:2em;">
    <!-- table header -->
    <?php if (! empty($data->items) && 'shop_order' === $data->order_type ||
              'shop_order_refund' === $data->order_type ||
              floatval(0) !== (floatval(abs($data->total)) - floatval(abs($data->refunded['total_refunded'])))
    ) : ?>
        <thead>
        <tr style="background:#ddd;">
            <th class="product"
                style="text-align:left;font-size:12px;padding:5px;width:30%;"><?php esc_html_e('Description',
                    WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="quantity" style="text-align:left;font-size:12px;padding:5px;">
                <?php esc_html_e('Quantity', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="vat" style="text-align:left;font-size:12px;padding:5px;">
                <?php esc_html_e('Tax rate', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="price-unit" style="text-align:left;font-size:12px;padding:5px;">
                <?php esc_html_e('Price unit', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="discount" style="text-align:left;font-size:12px;padding:5px;">
                <?php esc_html_e('Discount', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="price" style="text-align:left;font-size:12px;padding:5px;">
                <?php esc_html_e('Price', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
        </tr>
        </thead>
    <?php endif; ?>
    <!-- table body -->
    <tbody>
    <?php
    /**
     * Shop Items +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
     */
    if (! empty($data->items) && 'shop_order' === $data->order_type) :

        $checkSentOrder = get_post_meta($data->id, '_invoice_sent', true);
        if ('sent' !== $checkSentOrder) {
            // Recalculate if refund items in shop order (invoice)
            if (! empty($data->items_refunded)) {
                foreach ($data->items_refunded as $index => $itemRefund) {
                    foreach ($data->items as $key => $lineItem) {
                        if ($lineItem['product_id'] === $itemRefund['product_id']) {
                            $newQty         = abs($lineItem['quantity']) - abs($itemRefund['qty_refunded_for_item']);
                            $newSubTotal    = ($lineItem['subtotal'] / $lineItem['quantity']);
                            $newSubTotal    = ($newSubTotal * $newQty);
                            $newTotal       = $newSubTotal;
                            $newSubTotalTax = ($lineItem['subtotal_tax'] / $lineItem['quantity']);
                            $newSubTotalTax = ($newSubTotalTax * $newQty);
                            $newTotalTax    = $newSubTotalTax;

                            $data->items[$key]['quantity']          = "{$newQty}";
                            $data->items[$key]['subtotal']          = "{$newSubTotal}";
                            $data->items[$key]['total']             = "{$newTotal}";
                            $data->items[$key]['subtotal_tax']      = "{$newSubTotalTax}";
                            $data->items[$key]['total_tax']         = "{$newTotalTax}";
                            $data->items[$key]['taxes']['total']    = "{$newTotalTax}";
                            $data->items[$key]['taxes']['subtotal'] = "{$newSubTotalTax}";
                        }
                    }
                }
            }
        }

        $orderForShipping    = wc_get_order($data->id);
        $shippingTotalRefund = false;
        $totalShipping       = floatval($orderForShipping->get_shipping_total());
        $totalShippingTax    = floatval($orderForShipping->get_shipping_tax());

        // Check if shipping is refunded for set total and total tax
        $refunded         = $refundedTax = 0;
        foreach ($orderForShipping->get_items('shipping') as $itemID => $item) {
            $taxRates = \WC_Tax::get_rates($item->get_tax_class());
            if (empty($taxRates)) {
                $taxRates = \WC_Tax::get_base_tax_rates();
            }
            // I take any refunds
            if (! empty($taxRates)) {
                $id          = array_keys($taxRates);
                $refunded    = floatval($orderForShipping->get_total_refunded_for_item($itemID, 'shipping'));
                $refundedTax = floatval($orderForShipping->get_tax_refunded_for_item($itemID, $id[0], 'shipping'));
            }

            // Shipping total refunded
            if ($refunded === $totalShipping && $refundedTax === $totalShippingTax) {
                $shippingTotalRefund = true;
            } else {

                if (isset($data->items_shipping[1]) && is_array($data->items_shipping[1])) {
                    $dataShippingPartialRefund = $data->items_shipping[1]['refund_shipping'];
                    $totalShipping             = $orderForShipping->get_shipping_total() - $dataShippingPartialRefund['total'];
                    $totalShippingTax          = $orderForShipping->get_shipping_tax() - $dataShippingPartialRefund['total_tax'];
                }

                $orderTotals    += floatval($totalShipping) + floatval($totalShippingTax);
                $orderTaxTotals += floatval($totalShippingTax);
            }
        }

        /**
         * Product Items line +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
         */
        foreach ($data->items as $item) :
            $id = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
            // Total item refund
            if (0 === intval($item['quantity'])) {
                continue;
            }
            ?>
            <tr>
                <td style="vertical-align:top;width:25%;border-bottom:1px solid #ddd;padding:5px 0;" class="product">
                    <div style="font-size:12px;padding:0 10px 0 0;">
                        <strong class="item-name"><?php echo esc_html($item['name']); ?></strong><br>
                        <strong><?php echo esc_html__('Description:', WC_EL_INV_FREE_TEXTDOMAIN); ?></strong><br>
                        <span class="item-description"><?php echo $this->productDescription($item); ?></span><br>
                        <?php if ($item['sku']) : ?>
                            <strong><?php echo esc_html__('Sku:', WC_EL_INV_FREE_TEXTDOMAIN); ?></strong><br>
                            <span class="item-sku"><?php echo esc_html($item['sku']); ?></span><br>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="quantity">
                    <?php esc_html_e($this->numberFormat($item['quantity'])); ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="vat">
                    <?php
                    // Zero rate if total tax is zero
                    if ((floatval(0) === floatval($item['total_tax']))) {
                        $rate = 0;
                    } else {
                        $rate = $this->taxRate($item);
                    }
                    esc_html_e($this->numberFormat($rate)); ?>%
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="price-unit">
                    <?php
                    // Set discount unit and total
                    $discountUnit  = ((floatval($item['subtotal']) - floatval($item['total'])) / abs($item['quantity']));
                    $discountTotal = (floatval($item['subtotal']) - floatval($item['total']));
                    // Set Unit Price if have discount or not
                    if ($this->numberFormat($item['subtotal']) > $this->numberFormat($item['total'])) {
                        $unitPrice = $this->calcUnitPrice($item, $data, false);
                    } else {
                        $unitPrice = $this->calcUnitPrice($item, $data, false);
                    }
                    esc_html_e($currency . $this->numberFormat(floatval($unitPrice), 3)); ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="discount">
                    <?php if (floatval('0.00') !== $discountTotal) : ?>
                        <?php esc_html_e($currency . $this->numberFormat($discountTotal, 3)); ?>
                    <?php else: ?> *** <?php endif; ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="price">
                    <?php
                    $totalPrice    = floatval($unitPrice) * abs($item['quantity']);
                    $totalPrice    = $totalPrice - $discountTotal;
                    $totalPrice    = $this->numberFormat(floatval($totalPrice), 3, true);
                    ?>
                    <?php esc_html_e($currency . $totalPrice); ?><br>
                    <?php esc_html_e('tax:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                    <?php esc_html_e($currency . $this->numberFormat($item['total_tax'], 3)); ?>
                </td>
            </tr>

            <?php
            /**
             * Invoice
             *
             * Set Totals with item total and total tax
             * Use in Order Totals pdf template
             */
            $orderTotals    += ($item['total'] + $item['total_tax']);
            $orderTaxTotals += $item['total_tax'];

            /**
             * Invoice
             *
             * Set Total tax rates
             * Use in Summary pdf template
             */
            // Zero rate if total tax is zero
            if ((floatval(0) === floatval($item['total_tax']))) {
                $rate = 0;
            } else {
                $rate = $this->taxRate($item);
            }
            $summaryRate[$rate][] = array(
                'total'     => $item['total'],
                'total_tax' => $item['total_tax'],
            );

        endforeach;

        /**
         * Shipping line ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
         */
        if ('disabled' !== \WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields::getGeneralShippingLocation()) :
            if (floatval(0) < floatval($data->shipping_total) && ! $shippingTotalRefund) :
                if (isset($data->items_shipping[1]) && is_array($data->items_shipping[1])) {
                    $dataShippingPartialRefund = $data->items_shipping[1]['refund_shipping'];
                    $totalShipping             = $orderForShipping->get_shipping_total() - $dataShippingPartialRefund['total'];
                    $totalShippingTax          = $orderForShipping->get_shipping_tax() - $dataShippingPartialRefund['total_tax'];
                }
                ?>
                <tr>
                    <td style="width:30%;vertical-align:top:border-bottom:1px solid #ddd;" class="product">
                        <div style="font-size:12px;padding:0 10px 0 0;">
                            <strong class="item-name">
                                <?php esc_html_e('Shipping', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                            </strong><br>
                        </div>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="quantity">
                        <?php echo '1,00'; ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="vat">
                        <?php
                        if (floatval(0) !== floatval($totalShippingTax)) {
                            echo $this->numberFormat(esc_html($this->shippingRate())) . '%';
                        } else {
                            echo '0,00%';
                        }
                        ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price-unit">
                        <?php esc_html_e($currency . $this->numberFormat(floatval($totalShipping), 3)); ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="discount"> ***</td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price">
                        <?php esc_html_e($currency . $this->numberFormat(floatval($totalShipping), 3)); ?>
                        <br>
                        <?php esc_html_e('tax:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                        <?php esc_html_e($currency . $this->numberFormat(floatval($totalShippingTax))); ?>
                    </td>
                </tr>

                <?php
                /**
                 * Invoice
                 *
                 * Set Total tax rates
                 * Use in Summary pdf template
                 */
                if (floatval(0) !== floatval($totalShippingTax)) {
                    $shippingRate = $this->shippingRate();
                } else {
                    $shippingRate = 0;
                }
                $summaryRate[$shippingRate][] = array(
                    'total'     => $totalShipping,
                    'total_tax' => $totalShippingTax,
                );
            endif;
        endif;

        /**
         * Add items fee ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
         */
        if (! empty($data->items_fee)) :
            $itemsFees = $data->items_fee;
            $itemFeeTotal = $itemFeeTotalTax = $refundFee = $feeRate = null;

            foreach ($itemsFees as $key => $itemFees) {
                if (! isset($itemFees['refund_fee'])) {
                    $taxRates = \WC_Tax::get_rates($itemFees['tax_class']);
                    if (empty($taxRates)) {
                        $taxRates = \WC_Tax::get_base_tax_rates();
                    }
                    if (! empty($taxRates)) {
                        $taxRate         = reset($taxRates);
                        $feeRate         = $taxRate['rate'];
                        $itemFeeTotal    = isset($itemsFees[$key]['total']) ? $itemsFees[$key]['total'] : null;
                        $itemFeeTotalTax = isset($itemsFees[$key]['total_tax']) ? $itemsFees[$key]['total_tax'] : null;
                    }
                }
                $refundFee = isset($itemFees['refund_fee']) ? $itemFees['refund_fee'] : null;
            }

            // Refund fee - recalculate fee total and total tax
            if (! empty($refundFee)) {
                $itemFeeTotal    = floatval($itemFeeTotal) - floatval($refundFee['total']);
                $itemFeeTotalTax = floatval($itemFeeTotalTax) - floatval($refundFee['total_tax']);
            }

            if (floatval(0) === floatval($itemFeeTotalTax)) {
                $feeRate = 0;
            }

            if (floatval(0) !== $itemFeeTotal || floatval(0) !== $itemFeeTotal) :
                $summaryRate[$feeRate][] = array(
                    'total'     => abs($itemFeeTotal),
                    'total_tax' => abs($itemFeeTotalTax),
                );
                foreach ($itemsFees as $index => $itemFee) :
                    if (isset($itemsFees[$index]['id'])):
                        ?>
                        <tr>
                            <td style="width:30%;vertical-align:top;border-bottom:1px solid #ddd;" class="product">
                                <div style="font-size:12px;padding:0 10px 0 0;">
                                    <strong class="item-name">
                                        <?php esc_html_e($itemFee['name']);
                                        ?>
                                    </strong><br>
                                </div>
                            </td>
                            <td style="border-bottom:1px solid #ddd;font-size:12px;" class="quantity">
                                <?php echo '1,00';
                                ?>
                            </td>
                            <td style="border-bottom:1px solid #ddd;font-size:12px;" class="vat">
                                <?php
                                // Zero rate if total tax is zero
                                if ((floatval(0) === floatval($itemFeeTotalTax))) {
                                    $rate = 0;
                                } else {
                                    $rate = $taxRate['rate'];
                                }
                                echo $this->numberFormat(esc_html($rate)) . '%';
                                ?>
                            </td>
                            <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price-unit">
                                <?php esc_html_e($currency . $this->numberFormat(floatval($itemFeeTotal), 3)); ?>
                            </td>
                            <td style="border-bottom:1px solid #ddd;font-size:12px;" class="discount"> ***</td>
                            <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price">
                                <?php esc_html_e($currency . $this->numberFormat(floatval($itemFeeTotal), 3));
                                ?>
                                <br>
                                <?php esc_html_e('tax:', WC_EL_INV_FREE_TEXTDOMAIN);
                                ?>
                                <?php esc_html_e($currency . $this->numberFormat(floatval($itemFeeTotalTax)));
                                ?>
                            </td>
                        </tr>

                        <?php
                        /**
                         * Invoice
                         *
                         * Add fee total and tax fee cost
                         * Use in Summary pdf template
                         */
                        $orderTotals    += floatval($itemFeeTotal) + floatval($itemFeeTotalTax);
                        $orderTaxTotals += floatval($itemFeeTotalTax);
                    endif;
                endforeach;
            endif;
        endif;

    /**
     * Items line refunded ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
     */
    elseif ('shop_order_refund' === $data->order_type) :

        // Set refund item meta data for product
        $refundItemMeta = array();
        if (! empty($data->items)) {
            foreach ($data->items as $item) {
                $id                  = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
                $refundItemMeta[$id] = $item['meta_data'];
            }
        }

        if (! empty($data->current_refund_items)) :
            foreach ($data->current_refund_items as $item) :
                $total = isset($item['total']) ? $item['total'] : 0;
                $subtotal = isset($item['subtotal']) ? $item['subtotal'] : 0;
                $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;
                $isShipping = isset($item['method_id']) ? true : false;

                if ($isShipping) {
                    if (isset($data->items_shipping[1]) && is_array($data->items_shipping[1])) {
                        $refundShipping = $data->items_shipping[1];
                        $total          = $refundShipping['refund_shipping']['total'];
                        $totalTax       = $refundShipping['refund_shipping']['total_tax'];
                        if (0 === abs($totalTax)) {
                            $totalTax = $item['total_tax'] - $refundShipping['refund_shipping']['total_tax'];
                        }
                    }
                }

                $productID = isset($item['product_id']) ? $item['product_id'] : null;
                $id        = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $productID;

                // Set item meta data
                $item['meta_data'] = $refundItemMeta[$id];

                $product = wc_get_product($id);
                $qty     = isset($item['quantity']) ? $item['quantity'] : 1;

                $rate     = $this->taxRate($item);
                $taxRates = array();

                // Fee rates
                if (isset($item['refund_type']) && 'fee' === $item['refund_type']) {
                    // Fee rate
                    $total    = isset($item['total']) ? $item['total'] : 0;
                    $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                    $orderForFee = wc_get_order($item['order_id']);
                    foreach ($orderForFee->get_items('fee') as $itemID => $feeItem) {
                        // Get tax by country
                        $country = $orderForFee->get_billing_country();
                        $taxes   = \WC_Tax::get_rates_for_tax_class($feeItem->get_tax_class());
                        if (! empty($taxes)) {
                            foreach ($taxes as $tax) {
                                if ($tax->tax_rate_country === $country) {
                                    $taxRates = $tax;
                                    break;
                                }
                            }
                        }
                    }

                    $itemsFees = $data->items_fee;
                    foreach ($itemsFees as $key => $itemFees) {
                        // Get fee data
                        if (! isset($itemFees['refund_fee'])) {
                            $total    = isset($itemsFees[$key]['total']) ? $itemsFees[$key]['total'] : null;
                            $totalTax = isset($itemsFees[$key]['total_tax']) ? $itemsFees[$key]['total_tax'] : null;
                        }
                    }

                    // Rate based local tax
                    if (! empty($taxRates) && is_object($taxRates) &&
                        (floatval(0) !== floatval($totalTax) || floatval(0) !== abs($totalTax))
                    ) {
                        $rate = $this->numberFormat($taxRates->tax_rate, 0);
                    } else {
                        $rate = $item['tax_rate'];
                    }
                    if (floatval(0) === floatval($totalTax) || floatval(0) === abs($totalTax)) {
                        // Zero rate if total tax is zero
                        $rate = 0;
                    }
                    // Shipping rates
                } elseif (isset($item['refund_type']) && 'shipping' === $item['refund_type']) {
                    // Shipping rate
                    $total    = isset($item['total']) ? $item['total'] : 0;
                    $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                    $orderForShipping = wc_get_order($item['order_id']);
                    foreach ($orderForShipping->get_items('shipping') as $itemID => $shipItem) {
                        // Get tax by country
                        $country = $orderForShipping->get_billing_country();
                        $taxes   = \WC_Tax::get_rates_for_tax_class($shipItem->get_tax_class());
                        if (! empty($taxes)) {
                            foreach ($taxes as $tax) {
                                if ($tax->tax_rate_country === $country) {
                                    $taxRates = $tax;
                                    break;
                                }
                            }
                        }
                    }

                    // Rate based local tax
                    if (! empty($taxRates) && is_object($taxRates) && floatval(0) !== floatval($totalTax)) {
                        $rate = $this->numberFormat($taxRates->tax_rate, 0);
                    } elseif (floatval(0) === floatval($totalTax)) {
                        // Zero rate if total tax is zero
                        $rate = 0;
                    } else {
                        $rate = $this->shippingRate();
                    }
                }
                ?>
                <tr>
                    <td style="width:30%;vertical-align:top;border-bottom:1px solid #ddd;" class="product">
                        <div style="font-size:12px;padding:0 10px 0 0;">
                            <?php if (isset($item['method_id']) || isset($item['refund_type'])) : ?>
                                <span class="item-description">
                            <?php echo esc_html__('Refunded', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                                </span><br>
                                <strong class="item-name">
                                    <?php if ('shipping' === $item['refund_type']) : ?>
                                        <?php esc_html_e('Shipping', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                                    <?php endif; ?>
                                    <?php esc_html_e($item['name']); ?>
                                </strong><br>
                            <?php else : ?>
                                <strong class="item-name">
                                    <?php echo $this->productDescription($item, 'refund'); ?>
                                </strong><br>
                                <?php
                                /**
                                 * Meta data
                                 */
                                if (true === apply_filters('wc_el_inv-product_meta_description_pdf_invoice', false)) {
                                    $metaString = '';
                                    if (! empty($item['meta_data'])) {
                                        foreach ($item['meta_data'] as $index => $meta) {
                                            $sep        = $index === count($item['meta_data']) - 1 ? '' : '<br> ';
                                            $metaString = $metaString . "{$meta['key']}: {$meta['value']}{$sep}";
                                        }
                                    }
                                    echo "<p><small>{$metaString}</small></p>";
                                }
                                ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="quantity">
                        <?php echo $this->numberFormat(esc_html(abs($qty))); ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="vat">
                        <?php echo $this->numberFormat(esc_html($rate)); ?>%
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price-unit">
                        <?php if (isset($item['method_id']) || isset($item['refund_type'])) : ?>
                            <?php esc_html_e($currency . $this->numberFormat(abs($total), 3)); ?>
                        <?php else : ?>
                            <?php esc_html_e($currency . $this->numberFormat(abs($subtotal) / abs($item['quantity']),
                                    6)); ?>
                        <?php endif; ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="discount"> ***</td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price">
                        <?php esc_html_e($currency . $this->numberFormat(abs($total), 3)); ?>
                        <br>
                        <?php esc_html_e('tax:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                        <?php esc_html_e($currency . $this->numberFormat($totalTax, 3)); ?>
                    </td>

                    <?php
                    /**
                     * Refund
                     *
                     * Set Totals with item total and total tax
                     * Use in Order Totals pdf template
                     */
                    $orderTotals    += abs($total) + abs($totalTax);
                    $orderTaxTotals += abs($totalTax);

                    /**
                     * Refund
                     *
                     * Set Total tax rates
                     * Use in Summary pdf template
                     */
                    if (isset($item['product_id'])) {
                        $summaryRate[$this->taxRate($item)][] = array(
                            'total'     => abs($total),
                            'total_tax' => abs($totalTax),
                        );
                    }

                    if (isset($item['refund_type']) && 'fee' === $item['refund_type']) {

                        $total    = isset($item['total']) ? $item['total'] : 0;
                        $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                        // Fee rate
                        $orderForFee = wc_get_order($item['order_id']);
                        foreach ($orderForFee->get_items('fee') as $itemID => $feeItem) {
                            // Get tax by country
                            $country = $orderForFee->get_billing_country();
                            $taxes   = \WC_Tax::get_rates_for_tax_class($feeItem->get_tax_class());
                            if (! empty($taxes)) {
                                foreach ($taxes as $tax) {
                                    if ($tax->tax_rate_country === $country) {
                                        $taxRates = $tax;
                                        break;
                                    }
                                }
                            }
                        }

                        $itemsFees = $data->items_fee;
                        foreach ($itemsFees as $key => $itemFees) {
                            // Get fee data
                            if (! isset($itemFees['refund_fee'])) {
                                $total    = isset($itemsFees[$key]['total']) ? $itemsFees[$key]['total'] : null;
                                $totalTax = isset($itemsFees[$key]['total_tax']) ? $itemsFees[$key]['total_tax'] : null;
                            }
                        }

                        // Rate based local tax
                        if (! empty($taxRates) && is_object($taxRates) &&
                            (floatval(0) !== floatval($totalTax) || floatval(0) !== abs($totalTax))
                        ) {
                            $rate = $this->numberFormat($taxRates->tax_rate, 0);
                        } else {
                            $rate = $item['tax_rate'];
                        }
                        if (floatval(0) === floatval($totalTax) || floatval(0) === abs($totalTax)) {
                            // Zero rate if total tax is zero
                            $rate = 0;
                        }

                        $summaryRate[$rate][] = array(
                            'total'     => abs($item['total']),
                            'total_tax' => abs($totalTax),
                        );
                    }

                    if (isset($item['method_id']) && isset($item['refund_type']) && 'shipping' === $item['refund_type']) {

                        // Shipping tax
                        $total    = isset($item['total']) ? $item['total'] : 0;
                        $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                        $orderForShipping = wc_get_order($data->id);
                        foreach ($orderForShipping->get_items('shipping') as $itemID => $shipItem) {
                            // Get tax by country
                            $taxes = \WC_Tax::get_rates_for_tax_class($shipItem->get_tax_class());
                            if (! empty($taxes)) {
                                foreach ($taxes as $tax) {
                                    if ($tax->tax_rate_country === $country) {
                                        $taxRates = $tax;
                                        break;
                                    }
                                }
                            }
                        }
                        // Rate based local tax
                        if (! empty($taxRates) && is_object($taxRates) && floatval(0) !== floatval($totalTax)) {
                            $shippingRate = $this->numberFormat($taxRates->tax_rate, 0);
                        } elseif (floatval(0) === floatval($totalTax)) {
                            // Zero rate if total tax is zero
                            $shippingRate = 0;
                        } else {
                            $shippingRate = $this->shippingRate();
                        }

                        $summaryRate[$shippingRate][] = array(
                            'total'     => abs($total),
                            'total_tax' => abs($totalTax),
                        );
                    }
                    ?>
                </tr>
            <?php endforeach;
        endif;
        /**
         * Order Total refund
         */
        if ('shop_order_refund' === $data->order_type &&
            empty($data->current_refund_items) &&
            ! empty($data->refunded) &&
            '0' !== $data->refunded['total_refunded'] &&
            ! isset($shippingRefundLine) &&
            floatval(0) === (floatval(abs($data->total)) - floatval(abs($data->refunded['total_refunded'])))
        ) : ?>
            <tr>
                <?php
                $freeRefund    = true;
                $refund        = $data->refunded['total_refunded'];
                $reason        = isset($data->reason) && '' !== $data->reason ?
                    $data->reason : esc_html__('Flat-rate refund', WC_EL_INV_FREE_TEXTDOMAIN);
                $itemRefunded  = sprintf('%s', $reason);
                $totalRefunded = "{$currency}{$refund}"
                ?>
                <td style="width:30%;vertical-align:top;border-bottom:1px solid #ddd;" class="product">
                    <div style="font-size:12px;padding:0 10px 0 0;">
                        <strong><?php echo esc_html__('Description:', WC_EL_INV_FREE_TEXTDOMAIN); ?></strong><br>
                        <p class="item-name">
                            <?php echo $itemRefunded; ?>
                        </p><br>
                    </div>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="quantity">
                    <?php echo '1,00'; ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="vat"> ***</td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price-unit"> ***</td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="discount"> ***</td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price">
                    <?php echo $totalRefunded; ?>
                </td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>
    </tbody>
    <tfoot></tfoot>
</table>
