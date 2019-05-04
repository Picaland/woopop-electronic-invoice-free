<?php
/**
 * TimeZone.php
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

namespace WcElectronInvoiceFree\Utils;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class TimeZone
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class TimeZone
{
    /**
     * Time Zone
     *
     * @since  1.0.0
     *
     * @var \DateTimeZone The date time zone based on option
     */
    private $timeZone;

    /**
     * Get the TimeZone
     *
     * Retrieve the timezone based on WordPress option.
     *
     * @since  1.0.0
     *
     * @return string The timezone option value
     */
    private function getTimeZoneOption()
    {
        // Timezone_string is empty when the option is set to Manual Offset. So we use gmt_offset.
        $option = get_option('timezone_string') ? get_option('timezone_string') : get_option('gmt_offset');
        // Set to UTC in order to prevent issue if used with DateTimeZone constructor.
        $option = (in_array($option, array('', '0'), true) ? 'UTC' : $option);
        // And remember to add the symbol.
        if (is_numeric($option) && 0 < $option) {
            $option = '+' . $option;
        }

        return $option;
    }

    /**
     * Create the time zone instance
     *
     * @since  1.0.0
     *
     * @return \DateTimeZone
     */
    private function createTimeZone()
    {
        // Get the option.
        $option = $this->getTimeZoneOption();

        // Return the new instance.
        return new \DateTimeZone($option);
    }

    /**
     * TimeZoneOption constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // Set the time zone.
        $this->timeZone = $this->createTimeZone();
    }

    /**
     * Get the Time Zone
     *
     * @since  1.0.0
     *
     * @return \DateTimeZone The \DateTimeZone instance
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }
}
