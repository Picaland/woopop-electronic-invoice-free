<?php
/**
 * ordersIdsJsonArgs.php
 *
 * @since      1.0.0
 * @package    ${NAMESPACE}
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

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (! isset($orderID) && empty($orderData)) {
    return (object)array();
}

// Initialized data array
$data = array(
    'id'            => intval($orderID),
    'date_modified' => $orderData['date_modified'],
);

// Sanitize
$args = array(
    'id'            => FILTER_VALIDATE_INT,
    'date_modified' => array(
        'data'          => FILTER_UNSAFE_RAW,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_UNSAFE_RAW,
    ),
);

/**
 * Filter data and filter var
 *
 * @since 1.0.0
 */
$data = apply_filters('wc_el_inv-orders_ids_json_data', $data);
$args = apply_filters('wc_el_inv-orders_ids_json_args_filter_var', $args);

$data = filter_var_array($data, $args);

return (object)$data;
