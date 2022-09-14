/**
 * datePicker.js
 *
 * @since      ${SINCE}
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

;(
    function (_, $, wc_el_inv_invoice) {

        /**
         * Date picker init
         *
         * @since 1.0.0
         *
         * @returns {boolean}
         */
        function dateInit()
        {
            var dateInputs = document.querySelectorAll('.wc_el_inv-datepicker');
            if (!dateInputs) {
                return false;
            }

            _.forEach(dateInputs, function (item) {
                $(item).datepicker({
                    showWeek: true,
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd',
                    firstDay: 1
                });
            })
        }

        window.addEventListener('load', function (ev) {
            dateInit();
        })

    }(window._, window.jQuery, window.wc_el_inv_invoice)
);
