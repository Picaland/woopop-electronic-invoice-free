<?php
/**
 * CreateXml.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree\Xml
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

namespace WcElectronInvoiceFree\Xml;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class CreateXml
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class CreateXml
{
    /**
     * Filter Order data
     *
     * @param $ordersData
     *
     * @return mixed
     */
    public static function filterData($ordersData)
    {
        $orderID       = $ordersData['id'];
        $orderType     = $ordersData['order_type'];
        $orderRefunded = $ordersData['refunded'];
        $orderTotal    = $ordersData['total'];

        $sentInvoiceCheck = get_post_meta($orderID, '_invoice_sent', true);

        if ('shop_order' === $orderType && 'no_sent' === $sentInvoiceCheck && ! empty($orderRefunded)) {
            $ordersData['total'] = $orderTotal - $orderRefunded['total_refunded'];
        }

        return $ordersData;
    }

    /* ... PREMIUM VERSION ... */
}
