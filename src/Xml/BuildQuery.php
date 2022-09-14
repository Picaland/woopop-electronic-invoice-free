<?php
/**
 * BuildQuery.php
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

use WcElectronInvoiceFree\EndPoint\Endpoints;
use WcElectronInvoiceFree\Functions as F;
use WcElectronInvoiceFree\Utils\Helpers;

/**
 * Class BuildQuery
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class BuildQuery extends Endpoints
{
    /**
     * Xml Query
     *
     * @return bool|false|\WC_Order|\WC_Order_Query|\WC_Order_Refund|\WC_Product|\WC_Product_Query|null
     * @since  1.0.0
     */
    public function xmlQuery()
    {
        $wpQuery = F\getWpQuery();
        $query   = null;

        // @codingStandardsIgnoreLine
        $getFormat = F\filterInput($_GET, 'format', FILTER_UNSAFE_RAW);
        $format    = get_query_var('format');
        // Date ID
        $dateIN = get_query_var('date_in');
        // Date ID
        $dateOUT = get_query_var('date_out');

        if ('json' === $format) {
            return null;
        }

        // Setup data for query
        $data = Helpers::setQueryData(array(
            'getFormat' => $getFormat,
            'format'    => $format,
        ), $wpQuery, $this);

        if (is_object($data)) {
            // Set arguments for query
            switch ($data->postType) {
                case 'shop_order':
                    $args = array(
                        'status'  => array('processing', 'completed', 'refunded'),
                        'limit'   => 1,
                        'orderby' => 'date',
                        'order'   => 'ASC',
                    );

                    // Filter Order by date completed
                    if (isset($dateIN) && '' !== $dateIN && '' === $dateOUT) {
                        $args['date_modified'] = ">{$dateIN}";
                    } elseif ('' === $dateIN && isset($dateOUT) && '' !== $dateOUT) {
                        $args['date_modified'] = "<{$dateOUT}";
                    } elseif (isset($dateIN) && '' !== $dateIN && isset($dateOUT) && '' !== $dateOUT) {
                        $args['date_modified'] = "{$dateIN}...{$dateOUT}";
                    }

                    // Equal date
                    if (isset($dateIN) &&
                        isset($dateOUT) &&
                        '' !== $dateIN &&
                        '' !== $dateOUT &&
                        $dateIN === $dateOUT
                    ) {
                        $date                  = date('Y-m-d', intval($dateIN));
                        $args['date_modified'] = "{$date}";
                    }

                    if (isset($billingEmail) && '' !== $billingEmail) {
                        $args['billing_email'] = $billingEmail;
                    }

                    if (isset($data->idTag) && '' !== $data->idTag) {
                        $args['order_id'] = intval($data->idTag);
                    }
                    break;
                default:
                    $args = array();
                    break;
            }

            // Set query
            $query = Helpers::setQuery($data->postType, $args);
        }

        return $query;
    }
}
