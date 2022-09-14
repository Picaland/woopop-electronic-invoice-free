<?php
/**
 * Text
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
 * Class Text
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class Text implements Sanitize
{
    /**
     * Input
     *
     * @since 1.0.0
     *
     * @var mixed The input tu sanitized
     */
    private $input;

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
     * @since 1.0.0
     *
     * @return string The string sanitized
     * @throws \Exception
     */
    public static function sanitize($input)
    {
        if (! is_string($input)) {
            throw new \Exception('Input is not a string');
        }

        try {
            $input = sanitize_text_field($input);
        } catch (\Exception $e) {
            echo 'Input is not a string: ', $e->getMessage(), "\n";
        };

        return $input;
    }
}
