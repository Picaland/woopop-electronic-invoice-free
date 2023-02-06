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
                document.querySelector('.save-all-csv'),
                document.querySelector('.view-all'),
                document.querySelector('.get-all')
            ];

            if ([] === actions) {
                return;
            }

            // Filter in admin table
            var filter = document.querySelector('.filter');
            if (filter) {
                var filterHref = filter.getAttribute('href');
                filter.addEventListener('click', function (evt) {
                    evt.preventDefault();
                    evt.stopImmediatePropagation();

                    var baseHref = filterHref;

                    var customerID = '';
                    var customer = document.getElementById('filter_customer_id');
                    if (customer) {
                        customerID = customer.options[customer.selectedIndex].value;
                        if (!(customerID === '')) {
                            baseHref = evt.target.href + '&customer_id=' + customerID;
                        }
                    }

                    if ('undefined' !== baseHref) {
                        var targetHref = setUrlForFilter(baseHref);
                        if (targetHref !== filterHref && -1 !== targetHref.indexOf('date_in')) {
                            window.location = targetHref;
                        } else {
                            alert(wc_el_inv_admin.select_date_filter);
                        }
                    }

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

                        var href = setUrlForFilter(evt.target.parentElement.href);
                        window.open(href, '_blank');
                    })
                });
            }
        }

        /**
         * Parse Date
         * @param date
         * @returns {number}
         */
        function parseDate(date)
        {
            var dateSplit = date.split(/[^0-9]/);
            var DateString = dateSplit[0] + '-' +
                             dateSplit[1] + '-' +
                             dateSplit[2] + 'T' +
                             dateSplit[3] + ':' +
                             dateSplit[4] + ':00';

            var dateObj = new Date(DateString.toString());

            return dateObj.getTime() / 1000;
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

            // Add date params
            if (dateIN.value || dateOUT.value) {
                var IN = dateIN.value + ' 00:00';
                var OUT = dateOUT.value + ' 23:59';

                IN = IN.split(" - ").map(function (date) {
                    return parseDate(date);
                }).join(" - ");

                OUT = OUT.split(" - ").map(function (date) {
                    return parseDate(date);
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

                    var confirm = false;

                    if (this.classList.contains('mark_as_sent')) {
                        if (window.confirm(wc_el_inv_admin.invoice_sent_confirm)) {
                            confirm = true;
                        }
                    } else if (this.classList.contains('mark_undo')) {
                        if (window.confirm(wc_el_inv_admin.invoice_undo_confirm)) {
                            confirm = true;
                        }
                    }

                    if (!confirm) {
                        return;
                    }

                    $.ajax({
                        url: wc_el_inv_admin.ajax_url,
                        method: 'POST',
                        cache: false,
                        data: {
                            'action': 'markInvoice',
                            'action_url': this.href,
                            'nonce': wc_el_inv_admin.ajax_nonce
                        },
                        /**
                         * Before Send
                         */
                        beforeSend: function () {

                        }.bind(this),
                        /**
                         * Complete
                         */
                        complete: function (xhr, status) {
                            // Stop running.
                            console.log(xhr, status)
                        }.bind(this),
                        /**
                         * Error
                         */
                        error: function (xhr, status, error) {
                            console.warn('markInvoice ' + error, status);
                        }.bind(this),
                        /**
                         * Success
                         */
                        success: function (data, status, xhr) {
                            console.log(data);
                            window.location.reload();
                        }.bind(this)
                    });

                })
            });
        }

        /**
         * Refund item control
         */
        function refundItemControl()
        {
            var buttonRefund = document.querySelector('#woocommerce-order-items .inside .refund-items');
            var marks = document.querySelectorAll('#order_refunds .actions .mark_trigger');

            if (!marks) {
                return
            }
            _.forEach(marks, function (mark) {
                if (mark.classList.contains('mark_as_sent')) {
                    if (buttonRefund) {
                        buttonRefund.setAttribute('disabled', true);
                        buttonRefund.innerText = wc_el_inv_admin.refund_item_disabled_text;
                    }
                }
            });

        }

        /**
         * invoice item control refund amount
         */
        function invoiceItemControl()
        {
            var refundAmount = document.querySelector('#woocommerce-order-items .inside input#refund_amount');
            var mark = document.querySelector('.wc_el_inv__general-order .actions .mark_trigger');

            if (!refundAmount || !mark) {
                return;
            }

            if (mark.classList.contains('mark_as_sent')) {
                refundAmount.setAttribute('readonly', true);
                refundAmount.insertAdjacentHTML(
                    'afterend',
                    '<p id="readonly-info">' + wc_el_inv_admin.refund_amount_read_only_info_text + '</p>'
                )
            }
        }

        /**
         * Choice type
         */
        function choiceType()
        {
            var docTypeInputs = document.querySelectorAll('.doc-type-input');
            if (!docTypeInputs) {
                return;
            }

            _.forEach(docTypeInputs, function (input) {
                input.addEventListener('change', function (evt) {
                    this.parentElement
                        .parentElement
                        .parentElement
                        .querySelectorAll('.choice_type--current')[0]
                        .setAttribute('value', this.value);
                })
            });

        }

        /**
         * Set document type in query args
         */
        function endPointActions()
        {
            var actions = document.querySelectorAll('.action-endpoint');
            if (!actions) {
                return;
            }

            _.forEach(actions, function (action) {
                action.addEventListener('click', function (evt) {
                    evt.preventDefault();
                    evt.stopImmediatePropagation();

                    var type = this.parentElement.querySelector('.choice_type--current');
                    var href = this.href;
                    if (type) {
                        href = href + '&choice_type=' + type.value;
                    }
                    window.open(href);
                })
            });
        }

        /**
         * Get Url vars
         * @returns {{}}
         */
        function getUrlVars()
        {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
                vars[key] = value;
            });
            return vars;
        }

        /**
         * Get url param
         * @param parameter
         * @param defaultvalue
         * @returns {*}
         */
        function getUrlParam(parameter, defaultvalue)
        {
            var urlparameter = defaultvalue;
            if (window.location.href.indexOf(parameter) > -1) {
                urlparameter = getUrlVars()[parameter];
            }
            return urlparameter;
        }

        /**
         * Active sub menu item
         */
        function activeSubMenu()
        {
            var urlParam = getUrlParam('tab');

            var list = document.querySelectorAll('#toplevel_page_wc_el_inv-options-page ul.wp-submenu li a');
            if (list) {
                list.forEach(function (item) {
                    if (item.parentElement.classList.contains('wp-first-item')) {
                        item.parentElement.remove();
                    }
                    item.parentElement.classList.remove('current');
                    var href = item.getAttribute('href');
                    if (-1 !== href.indexOf(urlParam)) {
                        item.parentElement.classList.add('current');
                    }
                })
            }
        }

        function searchOrderByID()
        {
            var searchInput = document.getElementById('wc_el_inv_order_search');
            var searchTrigger = document.querySelector('.wc_el_inv_order_search_trigger');
            if (searchInput && searchTrigger) {
                searchTrigger.addEventListener('click', function (evt) {
                    evt.preventDefault();
                    evt.stopImmediatePropagation();
                    if ('' === searchInput.value) {
                        alert(wc_el_inv_admin.search_by_id);
                    } else {
                        window.location = searchTrigger.href + "&order_search=" + searchInput.value;
                    }
                    console.log(searchInput.value);
                })
            }
        }

        window.addEventListener('load', function () {

            // Invoice xml data
            var contentInv = $('#store_invoice-description');
            if (contentInv) {
                var titleInv = $(contentInv).prev();
                titleInv.addClass('store_invoice_title');
            }

            var contentInvT = $('#store_invoice_transmitter-description');
            if (contentInvT) {
                var titleInvT = $(contentInvT).prev();
                titleInvT.addClass('store_invoice_transmitter_title');
            }

            // Invoice info UE and extra UE options
            var contentOptions = $('#reverse_charge_invoice-description');
            if (contentOptions) {
                var title = $(contentOptions).prev();
                title.addClass('reverse_charge_invoice_title');
            }

            // Invoice info UE and extra UE
            var content = $('#reverse_charge_invoice_info-description');
            if (content) {
                var trigger = $(content).prev();
                trigger.addClass('reverse_charge_info_trigger');
                trigger.append('<span class="dashicons dashicons-arrow-right-alt2"></span>')
                $(trigger).on('click', function (evt) {
                    content.slideToggle('slow');
                    trigger.toggleClass('open');
                });
            }

            // Hide the info by clicking on the edit link
            $('a.edit_address').on('click', function (evt) {
                $('.order_data_column > p').addClass('hide');
            });

            activeSubMenu();
            editInvoiceNumber();
            editOrderInvoiceNumber();
            editRefundInvoiceNumber();
            filterCustomer();
            filterType();
            filterByDate();
            markInvoice();
            refundItemControl();
            invoiceItemControl();
            searchOrderByID();
            choiceType();
            endPointActions();

            // Remove refund ajax complete action
            $(document).ajaxComplete(function () {
                var refundAmount = document.querySelector('#woocommerce-order-items .inside input#refund_amount');
                var mark = document.querySelector('.wc_el_inv__general-order .actions .mark_trigger');

                if (!refundAmount || !mark) {
                    return;
                }

                if (!refundAmount.hasAttribute('readonly') && mark.classList.contains('mark_as_sent')) {
                    refundAmount.setAttribute('readonly', true);
                    refundAmount.insertAdjacentHTML(
                        'afterend',
                        '<p id="readonly-info">' + wc_el_inv_admin.refund_amount_read_only_info_text + '</p>'
                    )
                } else if (refundAmount.hasAttribute('readonly') && !mark.classList.contains('mark_as_sent')) {
                    refundAmount.removeAttribute('readonly');
                }
            });
        });

    }(window._, window.jQuery, window.wc_el_inv_admin)
);
