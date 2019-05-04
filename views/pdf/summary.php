<?php
/**
 * summary.php
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

global $orderTotals, $orderTaxTotals, $summaryRate, $currency;

?>
<?php if (! empty($summaryRate)) : ?>
    <?php echo sprintf('<h2 style="font-size:16px;">%s</h2>',
        esc_html__('VAT SUMMARY', WC_EL_INV_FREE_TEXTDOMAIN)
    ); ?>
    <table class="summary" width="100%" style="margin-top:2em;">
        <thead>
        <tr style="background:#ddd;">
            <th align="left"><?php esc_html_e('Tax rate', WC_EL_INV_FREE_TEXTDOMAIN); ?></th>
            <th align="left"><?php esc_html_e('Total amount', WC_EL_INV_FREE_TEXTDOMAIN); ?></th>
            <th align="left"><?php esc_html_e('Total taxable', WC_EL_INV_FREE_TEXTDOMAIN); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $total    = 0;
        $totalTax = 0;
        foreach ($summaryRate as $rate => $value) : ?>
            <tr>
                <?php
                foreach ($value as $item) {
                    $total    += $item['total'];
                    $totalTax += $item['total_tax'];
                }
                if (0 !== $total && 0 !== $totalTax) : ?>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;">
                        <?php echo $this->numberFormat($rate); ?>%
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;">
                        <?php echo esc_html($currency . $this->numberFormat($total)); ?>
                    </td>
                    <td style="border-bottom:1px solid #ddd;font-size:12px;padding:5px 0;">
                        <?php echo esc_html($currency . $this->numberFormat($totalTax)); ?>
                    </td>
                    <?php
                    // Reset total
                    $total = $totalTax = 0;
                endif;
                ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>