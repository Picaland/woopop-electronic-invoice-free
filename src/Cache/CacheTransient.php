<?php
/**
 * CacheTransient.php
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

namespace WcElectronInvoiceFree\Cache;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class CacheTransient
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class CacheTransient implements CacheInterface
{
    /**
     * The cache key
     *
     * @since  2.0.0
     *
     * @var string The key for the caching data
     */
    private static $name = 'wc_inv_data_caching';

    /**
     * Expiration data timing
     *
     * @since  2.0.0
     *
     * @var int The time when the transient will expire
     */
    private static $expiration = 0;

    /**
     * Get Transient
     *
     * @inheritdoc
     */
    public function get($name)
    {
        return get_transient(self::$name . "_{$name}");
    }

    /**
     * Set the cache data
     *
     * @inheritdoc
     */
    public function set($data, $name, $expiration = 0)
    {
        if (! is_string($data)) {
            throw new \InvalidArgumentException('Invalid data value for ' . __METHOD__);
        }

        if ($data) {
            set_transient(self::$name . "_{$name}", $data, $expiration);
        }
    }

    /**
     * Delete the cache data
     *
     * @param $name
     */
    public function delete($name)
    {
        delete_transient(self::$name . "_{$name}");
    }
}
