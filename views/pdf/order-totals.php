<?php
/**
 * order-totals.php
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

global $orderTotals, $orderTaxTotals, $summaryRate, $freeRefund;
$currency = apply_filters('wc_el_inv-pdf_currency_symbol', get_woocommerce_currency_symbol($data->currency));
?>
<?php
// Only for Invoice, Refund whit items or Shipping refund
if (! $freeRefund) : ?>
    <table style="width:100%;" class="order-details">
        <tr style="border-bottom:1px solid #ddd;">
            <td style="width:72%;"></td>
            <td style="background-color:#dddddd;border-bottom:1px solid #ddd;font-size:12px;padding:5px;">
                <strong><?php esc_html_e('Totals:', WC_EL_INV_FREE_TEXTDOMAIN); ?></strong>
            </td>
        </tr>
        <tr style="border-bottom:1px solid #ddd;">
            <td style="width:72%;"></td>
            <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;">
                <?php esc_html_e('Total Tax:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                <strong><?php echo esc_html($currency . $this->numberFormat($orderTaxTotals)) ?></strong>
            </td>
        </tr>
        <tr style="border-bottom:1px solid #ddd;">
            <td style="width:72%;"></td>
            <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;">
                <?php esc_html_e('Total:', WC_EL_INV_FREE_TEXTDOMAIN); ?>
                <strong><?php echo esc_html($currency . $this->numberFormat(abs($orderTotals))) ?></strong>
            </td>
        </tr>
    </table>
<?php endif; ?>