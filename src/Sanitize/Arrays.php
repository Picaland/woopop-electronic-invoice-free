<?php
/**
 * Arrays
 *
 * @since      1.0.0
 * @author     alfiopiccione <alfio.piccione@gmail.com>
 * @copyright  Copyright (c) 2017, alfiopiccione
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2
 *
 * Copyright (C) 2017 alfiopiccione <alfio.piccione@gmail.com>
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

namespace WcElectronInvoiceFree\Sanitize;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Arrays
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class Arrays implements Sanitize
{
    /**
     * Input
     *
     * @since 1.0.0
     *
     * @var array The input tu sanitized
     */
    private $input = array();

    /**
     * Sanitize constructor.
     *
     * @since 1.0.0
     *
     * @param $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Sanitize
     *
     * @param $input
     *
     * @return array The array sanitized
     * @throws \Exception
     */
    public static function sanitize($input)
    {
        if (! is_array($input)) {
            throw new \Exception('Input is not an array');
        }

        try {
            if(! empty($input)) {
                foreach ($input as $key => $item) {
                    if(is_string($key)) {
                        // To be sure that it only works with the plugin data.
                        if (false !== strpos('wc_el_inv-', $key)) {
                            $input[$key] = esc_attr(strip_tags($item));
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            echo 'Input is not an array: ', $e->getMessage(), "\n";
        };

        return $input;
    }
}
