<?php
/**
 * details.php
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

// Initialized
global $orderTotals, $orderTaxTotals, $summaryRate, $freeRefund;
$orderTotals = $orderTaxTotals = 0;
$summaryRate = array();
$currency    = get_woocommerce_currency_symbol($data->currency);
$freeRefund  = false;
?>
<table class="order-details" width="100%" style="margin-top:2em;">
    <!-- table header -->
    <?php if (! empty($data->items) && 'shop_order' === $data->order_type ||
              'shop_order_refund' === $data->order_type ||
              floatval(0) !== (floatval(abs($data->total)) - floatval(abs($data->refunded['total_refunded'])))
    ) : ?>
        <thead>
        <tr style="background:#ddd;">
            <th width="30%" class="product" align="left" style="font-size:12px;padding:5px;">
                <?php esc_html_e('Description', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="quantity" align="left" style="font-size:12px;padding:5px;">
                <?php esc_html_e('Quantity', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="vat" align="left" style="font-size:12px;padding:5px;">
                <?php esc_html_e('Tax rate', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="price-unit" align="left" style="font-size:12px;padding:5px;">
                <?php esc_html_e('Price unit', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="discount" align="left" style="font-size:12px;padding:5px;">
                <?php esc_html_e('Discount', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
            <th class="price" align="left" style="font-size:12px;padding:5px;">
                <?php esc_html_e('Price', WC_EL_INV_FREE_TEXTDOMAIN); ?>
            </th>
        </tr>
        </thead>
    <?php endif; ?>
    <!-- table body -->
    <tbody>
    <?php
    /**
     * Invoice
     * Shop Items
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
        // Check if shipping is refunded for set total and total tax
        foreach ($orderForShipping->get_items('shipping') as $itemID => $item) {
            $refunded = $orderForShipping->get_total_refunded_for_item($itemID, 'shipping');
            if (0 !== $refunded && floatval($refunded) === floatval(abs($data->shipping_total))) {
                $shippingTotalRefund = true;
                $shippingTotal       = $orderForShipping->get_shipping_total();
                $shippingTotalTax    = $orderForShipping->get_shipping_tax();
                $orderTotals         += floatval($shippingTotal) + floatval($shippingTotalTax);
                $orderTaxTotals      += floatval($shippingTotalTax);
            }
        }

        /**
         * Items line
         */
        foreach ($data->items as $item) :
            $id = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
            // Total item refund
            if (0 === intval($item['quantity'])) {
                continue;
            }
            ?>
            <tr>
                <td width="25%" style="border-bottom:1px solid #ddd;padding:5px 0;" class="product"
                    valign="top">
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
                    <?php esc_html_e($this->numberFormat($this->taxRate($item))); ?>%
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="price-unit">
                    <?php
                    // Set discount unit and total
                    $discountUnit  = $this->numberFormat((($item['subtotal'] - $item['total']) / abs($item['quantity'])), 4);
                    $discountTotal = $this->numberFormat((($item['subtotal'] - $item['total'])));
                    // Set Unit Price if have discount or not
                    if ($this->numberFormat($item['subtotal']) > $this->numberFormat($item['total'])) {
                        $unitPrice = $this->numberFormat($this->calcUnitPrice($item) + abs($discountUnit), 4);
                    } else {
                        $unitPrice = $this->numberFormat($this->calcUnitPrice($item), 4);
                    }
                    esc_html_e($currency . $unitPrice); ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="discount">
                    <?php if ('0.00' !== $discountTotal) : ?>
                        <?php esc_html_e($currency . $discountTotal); ?>
                    <?php else: ?> *** <?php endif; ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;" class="price">
                    <?php esc_html_e($currency . $this->numberFormat($item['total'])); ?><br>
                    <?php esc_html_e('tax:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                    <?php esc_html_e($currency . $this->numberFormat($item['total_tax'])); ?>
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
            // If shipping is refunded remove price from totals
            if ($shippingTotalRefund) {
                $orderTotals    = floatval($orderTotals) - (floatval($orderForShipping->get_shipping_total()) + floatval($orderForShipping->get_shipping_tax()));
                $orderTaxTotals = floatval($orderTaxTotals) - floatval($orderForShipping->get_shipping_tax());
            }

            /**
             * Invoice
             *
             * Set Total tax rates
             * Use in Summary pdf template
             */
            $summaryRate[$this->taxRate($item)][] = array(
                'total'     => $item['total'],
                'total_tax' => $item['total_tax'],
            );

        endforeach;

        /**
         * Shipping line
         */
        if (floatval(0) < floatval($data->shipping_total) && ! $shippingTotalRefund) : ?>
            <tr>
                <td width="30%" style="border-bottom:1px solid #ddd;" class="product" valign="top">
                    <div style="font-size:12px;padding:0 10px 0 0;">
                        <strong class="item-name">
                            <?php esc_html_e('Shipping', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                        </strong><br>
                    </div>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="quantity">
                    <?php echo '1'; ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="vat">
                    <?php echo $this->numberFormat(esc_html($this->shippingRate())); ?>%
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price-unit">
                    <?php esc_html_e($currency . $this->numberFormat(floatval($orderForShipping->get_shipping_total()))); ?>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="discount"> ***</td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price">
                    <?php esc_html_e($currency . $this->numberFormat(floatval($orderForShipping->get_shipping_total()))); ?>
                    <br>
                    <?php esc_html_e('tax:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                    <?php esc_html_e($currency . $this->numberFormat(floatval($orderForShipping->get_shipping_tax()))); ?>
                </td>
            </tr>

            <?php
            /**
             * Invoice
             *
             * Add shipping total and tax shipping cost
             * Use in Summary pdf template
             */
            $orderTotals    += floatval($orderForShipping->get_shipping_total()) + floatval($orderForShipping->get_shipping_tax());
            $orderTaxTotals += floatval($orderForShipping->get_shipping_tax());

            /**
             * Invoice
             *
             * Set Total tax rates
             * Use in Summary pdf template
             */
            $summaryRate[$this->shippingRate()][] = array(
                'total'     => $orderForShipping->get_shipping_total(),
                'total_tax' => $orderForShipping->get_shipping_tax(),
            );
        endif;
    /**
     * Items line refunded
     */
    elseif ('shop_order_refund' === $data->order_type) :
        if (! empty($data->current_refund_items)) :
            foreach ($data->current_refund_items as $item) :
                $id = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
                $product = wc_get_product($id);
                ?>
                <tr>
                    <td width="30%" style="border-bottom:1px solid #ddd;" class="product" valign="top">
                        <div style="font-size:12px;padding:0 10px 0 0;">
                            <?php if (isset($item['method_id'])) : ?>
                                <span class="item-description">
                            <?php echo esc_html__('Refunded', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                                </span><br>
                                <strong class="item-name">
                                    <?php esc_html_e('Shipping', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                                    <?php esc_html_e($item['name']); ?>
                                </strong><br>
                            <?php else : ?>
                                <span class="item-description"><?php echo $this->productDescription($item,
                                        'refund'); ?></span><br>
                                <strong class="item-name"><?php esc_html_e($item['name']); ?></strong><br>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="quantity">
                        <?php echo $this->numberFormat(esc_html(abs($item['quantity']))); ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="vat">
                        <?php echo $this->numberFormat(esc_html($this->taxRate($item))); ?>%
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price-unit">
                        <?php if (isset($item['method_id'])) : ?>
                            <?php esc_html_e($currency . $this->numberFormat(abs($item['total']))); ?>
                        <?php else : ?>
                            <?php esc_html_e($currency . $this->numberFormat(abs($item['subtotal']) / abs($item['quantity']))); ?>
                        <?php endif; ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="discount"> ***</td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;" class="price">
                        <?php esc_html_e($currency . $this->numberFormat(abs($item['total']))); ?>
                        <br>
                        <?php esc_html_e('tax:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                        <?php esc_html_e($currency . $this->numberFormat(abs($item['total_tax']))); ?>
                    </td>

                    <?php
                    /**
                     * Refund
                     *
                     * Set Totals with item total and total tax
                     * Use in Order Totals pdf template
                     */
                    $orderTotals    += abs($item['total']) + abs($item['total_tax']);
                    $orderTaxTotals += abs($item['total_tax']);

                    /**
                     * Refund
                     *
                     * Set Total tax rates
                     * Use in Summary pdf template
                     */
                    if (isset($item['product_id'])) {
                        $summaryRate[$this->taxRate($item)][] = array(
                            'total'     => abs($item['total']),
                            'total_tax' => abs($item['total_tax']),
                        );
                    }
                    if (isset($item['method_id'])) {
                        $summaryRate[$this->shippingRate()][] = array(
                            'total'     => abs($item['total']),
                            'total_tax' => abs($item['total_tax']),
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
                <td width="30%" style="border-bottom:1px solid #ddd;" class="product" valign="top">
                    <div style="font-size:12px;padding:0 10px 0 0;">
                        <strong><?php echo esc_html__('Description:', WC_EL_INV_FREE_TEXTDOMAIN); ?></strong><br>
                        <p class="item-name">
                            <?php echo $itemRefunded; ?>
                        </p><br>
                    </div>
                </td>
                <td style="border-bottom:1px solid #ddd;font-size:12px;" class="quantity">
                    <?php echo '1'; ?>
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
