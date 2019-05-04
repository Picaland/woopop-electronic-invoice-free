/**
 * admin.js
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
    function (_, $, wc_el_inv_admin) {

        /**
         * Edit Link
         *
         * @since 1.0.0
         *
         * @param id
         * @param appendTo
         * @param position
         */
        function createEditLink(id, appendTo, position)
        {
            var edit = document.createElement('a');
            edit.setAttribute('id', id);
            edit.setAttribute('href', 'javascript:;');
            edit.innerHTML = '<i class="dashicons dashicons-edit"></i>';
            appendTo.insertAdjacentElement(position, edit);
        }

        /**
         * Button Save
         *
         * @since 1.0.0
         *
         * @param id
         * @param appendTo
         * @param position
         */
        function createButtonSave(id, appendTo, position)
        {
            var button = document.createElement('button');
            button.setAttribute('id', id);
            button.setAttribute('name', id);
            button.setAttribute('class', 'button');
            button.innerText = wc_el_inv_admin.text_save;
            appendTo.insertAdjacentElement(position, button);
        }

        /**
         * Close Link
         *
         * @since 1.0.0
         *
         * @param id
         * @param appendTo
         * @param position
         */
        function createCloseLink(id, appendTo, position)
        {
            var close = document.createElement('a');
            close.setAttribute('id', id);
            close.setAttribute('href', 'javascript:;');
            close.innerHTML = '<i class="dashicons dashicons-no"></i>';
            appendTo.insertAdjacentElement('beforeend', close);
        }

        /**
         * Edit and Save invoice next number
         *
         * @since 1.0.0
         */
        function editInvoiceNumber()
        {
            var input = document.getElementById('wc_el_inv-settings-number_next_invoice');
            if (!input) {
                return;
            }

            if ('' !== input.value) {
                input.setAttribute('disabled', 'disabled');
            }

            createEditLink('edit_invoice_next_number', input, 'afterend');
            var edit = document.getElementById('edit_invoice_next_number');

            // Edit.
            edit.addEventListener('click', function () {
                // Hide edit
                edit.style.display = 'none';
                input.removeAttribute('disabled');
                createButtonSave('save_invoice_next_number', input, 'afterend');
            });
        }

        /**
         * Edit order invoice number
         *
         * @since 1.0.0
         */
        function editOrderInvoiceNumber()
        {
            var inputs = document.querySelectorAll('.wc_el_inv-order_fields');
            if (0 === inputs.length) {
                return;
            }

            _.forEach(inputs, function (input) {
                if ('' !== input.value) {
                    input.setAttribute('disabled', 'disabled');
                }
            });

            var wrapTitle = document.querySelector('.wc_el_inv__general-order h3');
            var fields = document.querySelector('.wc_el_inv__general-order--hidden-fields');
            var textData = document.querySelector('.wc_el_inv__general-order--text-data');

            if (wrapTitle && fields && textData) {
                // Create edit action
                createEditLink('edit_invoice_next_number', wrapTitle, 'beforeend');
                var edit = document.getElementById('edit_invoice_next_number');
                // Create close action
                createCloseLink('close_invoice_next_number', wrapTitle, 'beforeend');
                var close = document.getElementById('close_invoice_next_number');

                // Close default hidden
                close.style.display = 'none';

                // Edit click event.
                edit.addEventListener('click', function () {
                    // Show fields and hide text data
                    fields.style.display = 'block';
                    textData.style.display = 'none';
                    // Hide edit
                    this.style.display = 'none';
                    // Show close
                    close.style.display = 'block';

                    _.forEach(inputs, function (input) {
                        input.removeAttribute('disabled');
                    });
                });

                // Close click event.
                close.addEventListener('click', function () {
                    // Show text data and hide fields
                    fields.style.display = 'none';
                    textData.style.display = 'block';

                    // Hide close
                    close.style.display = 'none';
                    edit.style.display = '';

                    _.forEach(inputs, function (input) {
                        input.setAttribute('disabled', 'disabled');
                    });

                });
            }
        }

        /**
         * Edit refund invoice number
         *
         * @since 1.0.0
         */
        function editRefundInvoiceNumber()
        {
            var refundLine = document.querySelectorAll('.wc_el_inv__refund-invoice[data-order_refund_id]');
            var inputs = document.querySelectorAll('.wc_el_inv-order_fields');
            if (0 === inputs.length) {
                return;
            }

            // Set disabled attr
            _.forEach(inputs, function (input) {
                if ('' !== input.value) {
                    input.setAttribute('disabled', 'disabled');
                }
            });

            // Refund line
            _.forEach(refundLine, function (item, index) {
                var wrapTitle = item.querySelector('.wc_el_inv__refund-invoice td h3');
                var fields = item.querySelector('.wc_el_inv__refund-invoice--hidden-fields');
                var textData = item.querySelector('.wc_el_inv__refund-invoice--text-data');

                if (wrapTitle && fields && textData) {
                    // Create edit action
                    createEditLink('edit_refund_invoice_next_number-' + index, wrapTitle, 'beforeend');
                    var edit = document.getElementById('edit_refund_invoice_next_number-' + index);
                    // Create close action
                    createCloseLink('close_refund_invoice_next_number-' + index, wrapTitle, 'beforeend');
                    var close = document.getElementById('close_refund_invoice_next_number-' + index);

                    // Close default hidden
                    close.style.display = 'none';

                    // Close click event.
                    close.addEventListener('click', function () {
                        // Show text data and hide fields
                        fields.style.display = 'none';
                        textData.style.display = 'block';

                        // Hide close
                        close.style.display = 'none';
                        edit.style.display = '';
                        _.forEach(inputs, function (input) {
                            input.setAttribute('disabled', 'disabled');
                        });

                    });

                    // Edit click event.
                    edit.addEventListener('click', function () {
                        // Show fields and hide text data
                        fields.style.display = 'block';
                        textData.style.display = 'none';
                        // Hide edit
                        this.style.display = 'none';
                        // Show close
                        close.style.display = 'block';

                        _.forEach(inputs, function (input) {
                            input.removeAttribute('disabled');
                        });
                    });
                }
            });

        }

        /**
         * Filter by date
         *
         * @since ${SINCE}
         */
        function filterByDate()
        {
            var actions = [
                document.querySelector('.save-all'),
                document.querySelector('.view-all'),
                document.querySelector('.get-all')
            ];

            if ([] === actions) {
                return;
            }

            // Filter in admin table
            var filter = document.querySelector('.filter');
            if (filter) {
                filter.addEventListener('click', function (evt) {
                    evt.preventDefault();
                    evt.stopImmediatePropagation();

                    var baseHref = evt.target.href;

                    var customerID = '';
                    var customer = document.getElementById('filter_customer_id');
                    if (customer) {
                        customerID = customer.options[customer.selectedIndex].value;
                        if (!(customerID === '')) {
                            baseHref = evt.target.href + '&customer_id=' + customerID;
                        }
                    }

                    window.location = setUrlForFilter(baseHref);
                })
            }

            // Endpoint filer
            if (actions) {
                _.forEach(actions, function (item) {

                    if (!item) {
                        return;
                    }

                    item.addEventListener('click', function (evt) {
                        evt.preventDefault();
                        evt.stopImmediatePropagation();
                        /* ... PREMIM VERION ... */
                    })
                });
            }
        }

        /**
         * Set url for filter
         *
         * @since ${SINCE}
         *
         * @param baseHref
         * @returns {string}
         */
        function setUrlForFilter(baseHref)
        {
            var href;

            var dateIN = document.getElementById('date_in');
            var dateOUT = document.getElementById('date_out');

            if (dateIN.value || dateOUT.value) {
                var IN = dateIN.value + ' 00:00';
                var OUT = dateOUT.value + ' 00:00';

                IN = IN.split(" - ").map(function (date) {
                    return Date.parse(date + "-0500") / 1000;
                }).join(" - ");

                OUT = OUT.split(" - ").map(function (date) {
                    return Date.parse(date + "-0500") / 1000;
                }).join(" - ");

                if (dateIN.value && '' === dateOUT.value) {
                    href = baseHref + '&date_in=' + IN;
                }

                if ('' === dateIN.value && dateOUT.value) {
                    href = baseHref + '&date_out=' + OUT
                }

                if (dateIN.value && dateOUT.value) {
                    href = baseHref + '&date_in=' + IN + '&date_out=' + OUT;
                }

            } else {
                href = baseHref;
            }

            return href;
        }

        /**
         * Filter customer
         *
         * @since 1.0.0
         */
        function filterCustomer()
        {
            var select = document.getElementById('filter_customer_id');
            if (!select) {
                return;
            }

            select.addEventListener('change', function () {
                window.location = window.location.href + '&customer_id=' + this.value;
            });
        }

        /**
         * Filter customer
         *
         * @since 1.0.0
         */
        function filterType()
        {
            var select = document.getElementById('filter_type');
            if (!select) {
                return;
            }

            select.addEventListener('change', function () {
                window.location = window.location.href + '&type=' + this.value;
            });
        }

        /**
         * Bulk Mark
         */
        function bulkMarkActions()
        {
            var triggers = document.querySelectorAll('.mark_bulk_trigger');
            if (!triggers) {
                return;
            }

            _.forEach(triggers, function (trigger) {
                trigger.addEventListener('click', function (evt) {
                    evt.preventDefault();
                    evt.stopImmediatePropagation();
                })
            });
        }

        /**
         * Ajax mark invoice action
         */
        function markInvoice()
        {
            var triggers = document.querySelectorAll('.mark_trigger');
            if (!triggers) {
                return;
            }
            _.forEach(triggers, function (trigger) {

                trigger.addEventListener('click', function (evt) {
                    evt.preventDefault();
                    evt.stopImmediatePropagation();
                })
            });
        }

        window.addEventListener('load', function () {
            editInvoiceNumber();
            editOrderInvoiceNumber();
            editRefundInvoiceNumber();
            filterCustomer();
            filterType();
            filterByDate();
            markInvoice();
            bulkMarkActions();
        });

    }(window._, window.jQuery, window.wc_el_inv_admin)
);
