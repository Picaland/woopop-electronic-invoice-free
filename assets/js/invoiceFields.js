/**
 * invoiceFields.js
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
    function ($, wc_el_inv_invoice) {

        /**
         * Init Select2
         *
         * @since 1.0.0
         *
         * @returns {boolean}
         */
        function initSelect2()
        {
            var invoiceType = document.getElementById('billing_invoice_type');
            if (!invoiceType) {
                return false;
            }

            if ($(invoiceType).data('select2')) {
                return false;
            }

            $(invoiceType).select2(
                {
                    minimumResultsForSearch: Infinity
                }
            );

            var choiceType = document.getElementById('billing_choice_type');
            if (!choiceType) {
                return false;
            }

            if ($(choiceType).data('select2')) {
                return false;
            }

            $(choiceType).select2(
                {
                    minimumResultsForSearch: Infinity
                }
            );
        }

        /**
         * Clone Vat in tax code for only company
         * @returns {boolean}
         */
        function cloneValueVat()
        {
            var invoiceType = document.getElementById('billing_invoice_type');
            var country = document.getElementById('billing_country');
            var invoiceVat = document.getElementById('billing_vat_number');
            var invoiceTax = document.getElementById('billing_tax_code');

            // Return if tax code is disabled
            if ('on' === wc_el_inv_invoice.disable_cf) {
                return false;
            }

            if (!invoiceVat && !invoiceTax) {
                return false;
            }

            invoiceVat.addEventListener('keyup', function (evt) {
                if ('IT' === country.value && 'company' !== invoiceType.value) {
                    return false;
                }
                invoiceTax.value = this.value;
            })

        }

        /**
         * Set Required
         *
         * @since 1.0.0
         *
         * @returns {boolean}
         */
        function setRequired()
        {
            var invoiceType = document.getElementById('billing_invoice_type');
            var country = document.getElementById('billing_country');

            if (!invoiceType || !country) {
                return false;
            }

            document.addEventListener('DOMContentLoaded', function () {
                invoiceType.onchange = changeInvoiceTypeEventHandler;
            }, false);

            document.addEventListener('DOMContentLoaded', function () {
                country.onchange = changeCountryEventHandler;
            }, false);
        }

        /**
         * Change Country Handler
         *
         * @since 1.0.0
         *
         * @param event
         */
        function changeCountryEventHandler(event)
        {
            var country = event.target.value;
            var invoiceType = document.getElementById('billing_invoice_type');

            if ('IT' !== country) {
                switchTypeNoIT(invoiceType.value, event);
            } else {
                switchType(invoiceType.value, event);
            }
        }

        /**
         * Change Invoice Type Handler
         *
         * @since 1.0.0
         *
         * @param event
         */
        function changeInvoiceTypeEventHandler(event)
        {
            var country = document.getElementById('billing_country');
            var type = event.target.value;

            // IT country and not required fields and country is in UE
            if ('IT' === country.value &&
                -1 !== wc_el_inv_invoice.eu_vat_country.indexOf(country.value)
            ) {
                switchType(type, event);
            } else {
                switchTypeNoIT(type, event)
            }
        }

        /**
         * Switch Country
         *
         * @param country
         * @param type
         * @param event
         */
        function switchCountry(country, type, event)
        {
            if ('IT' !== country) {
                switchTypeNoIT(type, event);
            } else {
                switchType(type, event);
            }
        }

        /**
         * Switch No IT type
         * @param type
         * @param ev
         */
        function switchTypeNoIT(type, ev)
        {
            var country = document.getElementById('billing_country');
            var choiceTypeField = document.getElementById('billing_choice_type_field');
            var invoiceTypeField = document.getElementById('billing_invoice_type_field');
            var sdi = document.getElementById('billing_sdi_type');
            var sdiField = document.getElementById('billing_sdi_type_field');
            var sdiLabel = document.querySelector('#billing_sdi_type_field > label');
            if (sdiLabel) {
                var sdiAbbr = sdiLabel.firstElementChild;
            }

            var vat = document.getElementById('billing_vat_number');
            var vatField = document.getElementById('billing_vat_number_field');
            var vatLabel = document.querySelector('#billing_vat_number_field > label');
            var vatAbbr = vatLabel.firstElementChild;

            var taxCode = document.getElementById('billing_tax_code');
            var taxCodeField = document.getElementById('billing_tax_code_field');
            var taxCodeLabel = document.querySelector('#billing_tax_code_field > label');
            if (taxCode) {
                var taxCodeAbbr = taxCodeLabel.firstElementChild;
            }

            // Sdi not required for no IT country
            if (sdiField) {
                // SDI
                sdiField.style.display = 'none';
                sdiAbbr.classList.remove('required');
                sdiAbbr.classList.add('optional');
                sdiField.classList.remove('validate-required');
                sdiAbbr.innerText = '(' + wc_el_inv_invoice.not_required_text + ')';
                sdiAbbr.outerHTML = sdiAbbr.outerHTML.replace(/abbr/g, "span");
                sdi.removeAttribute('required');
                sdi.value = '';
            }

            // Reset value on change
            if ('change' === ev.type) {
                if (sdi) {
                    sdi.value = '';
                }
                if (vat) {
                    vat.value = '';
                }
                if (taxCode) {
                    taxCode.value = '';
                }
            }

            // No UE not required VAT and TAX
            if ('show' !== wc_el_inv_invoice.hide_outside_ue &&
                -1 === wc_el_inv_invoice.eu_vat_country.indexOf(country.value)
            ) {
                if (choiceTypeField) {
                    choiceTypeField.style.display = 'none';
                }
                invoiceTypeField.style.display = 'none';
                // TAX
                taxCodeField.style.display = 'none';
                taxCode.value = ''; // reset value
                taxCodeAbbr.classList.remove('required');
                taxCodeField.classList.remove('validate-required');
                taxCode.removeAttribute('required');
                // VAT
                vatField.style.display = 'none';
                vat.value = ''; // reset value
                vatAbbr.classList.remove('required');
                vatField.classList.remove('validate-required');
                vat.removeAttribute('required');
                return;
            }

            if ('1' === wc_el_inv_invoice.invoice_required) {
                // TAX
                taxCodeField.style.display = 'block';
                taxCodeAbbr.classList.remove('optional');
                taxCodeAbbr.classList.add('required');
                taxCodeField.classList.add('validate-required');
                taxCodeAbbr.innerText = '*';
                taxCodeAbbr.outerHTML = taxCodeAbbr.outerHTML.replace(/span/g, "abbr");
                taxCode.setAttribute('required', 'required');
                // VAT
                vatField.style.display = 'block';
                vatAbbr.classList.remove('optional');
                vatAbbr.classList.add('required');
                vatField.classList.add('validate-required');
                vatAbbr.innerText = '*';
                vatAbbr.setAttribute('title', wc_el_inv_invoice.required_text);
                vatAbbr.outerHTML = vatAbbr.outerHTML.replace(/span/g, "abbr");
                vat.setAttribute('required', 'required');
            } else {
                // TAX
                taxCodeAbbr.classList.remove('required');
                taxCodeAbbr.classList.add('optional');
                taxCodeField.classList.remove('validate-required');
                taxCodeAbbr.innerText = '(' + wc_el_inv_invoice.not_required_text + ')';
                taxCodeAbbr.outerHTML = vatAbbr.outerHTML.replace(/abbr/g, "span");
                taxCode.removeAttribute('required');
                // VAT
                vatAbbr.classList.remove('required');
                vatAbbr.classList.add('optional');
                vatField.classList.remove('validate-required');
                vatAbbr.innerText = '(' + wc_el_inv_invoice.not_required_text + ')';
                vatAbbr.outerHTML = vatAbbr.outerHTML.replace(/abbr/g, "span");
                vat.removeAttribute('required');
            }

            invoiceTypeField.style.display = 'block';

            switch (type) {
                case 'private':
                    if (vat) {
                        // VAT
                        vatField.style.display = 'none';
                        vatAbbr.classList.remove('required');
                        vatAbbr.classList.add('optional');
                        vatField.classList.remove('validate-required');
                        vatAbbr.innerText = '(' + wc_el_inv_invoice.not_required_text + ')';
                        vatAbbr.outerHTML = vatAbbr.outerHTML.replace(/abbr/g, "span");
                        vat.removeAttribute('required');
                    }
                    if (taxCode) {
                        taxCodeField.style.display = 'block';
                    }
                    break;
                case 'company':
                case 'freelance':
                    if (vat) {
                        vatField.style.display = 'block';
                    }
                    if (taxCode && 'on' === wc_el_inv_invoice.disable_cf) {
                        // TAX
                        taxCodeField.style.display = 'none';
                        taxCodeAbbr.classList.remove('required');
                        taxCodeAbbr.classList.add('optional');
                        taxCodeField.classList.remove('validate-required');
                        taxCodeAbbr.innerText = '(' + wc_el_inv_invoice.not_required_text + ')';
                        taxCodeAbbr.outerHTML = vatAbbr.outerHTML.replace(/abbr/g, "span");
                        taxCode.removeAttribute('required');
                    }
                    break;
                case '':
                    taxCodeField.style.display = 'none';
                    if (sdiField) {
                        sdiField.style.display = 'none';
                    }
                    if (vatField) {
                        vatField.style.display = 'none';
                    }
                    break;
                default:
                    break;
            }
        }

        /**
         * Switch Type and set args in input
         *
         * @since 1.0.0
         *
         * @param type
         * @param ev
         */
        function switchType(type, ev)
        {
            var country = document.getElementById('billing_country');
            var choiceTypeField = document.getElementById('billing_choice_type_field');
            var invoiceTypeField = document.getElementById('billing_invoice_type_field');
            var sdi = document.getElementById('billing_sdi_type');
            var sdiField = document.getElementById('billing_sdi_type_field');
            var sdiInput = document.getElementById('billing_sdi_type');
            var sdiLabel = document.querySelector('#billing_sdi_type_field > label');
            var sdiDesc = document.querySelector('#billing_sdi_type_field #billing_sdi_type-description');

            if (sdi) {
                var sdiAbbr = sdiLabel.firstElementChild;
            }

            var vat = document.getElementById('billing_vat_number');
            var vatField = document.getElementById('billing_vat_number_field');
            var vatLabel = document.querySelector('#billing_vat_number_field > label');
            var vatAbbr = vatLabel.firstElementChild;

            var taxCode = document.getElementById('billing_tax_code');
            var taxCodeField = document.getElementById('billing_tax_code_field');
            var taxCodeLabel = document.querySelector('#billing_tax_code_field > label');

            if (taxCode) {
                var taxCodeAbbr = taxCodeLabel.firstElementChild;
            }

            // No UE check
            if (-1 === wc_el_inv_invoice.eu_vat_country.indexOf(country.value)) {
                return;
            }

            // No IT check
            if ('IT' !== country.value) {
                return;
            }

            // Reset value on change
            if ('change' === ev.type) {
                if (sdi) {
                    sdi.value = '';
                }
                if (vat) {
                    vat.value = '';
                }
                if (taxCode) {
                    taxCode.value = '';
                }
            }

            // Initialize display fields
            if (choiceTypeField) {
                choiceTypeField.style.display = 'block';
            }
            invoiceTypeField.style.display = 'block';
            taxCodeField.style.display = 'block';
            vatField.style.display = 'block';

            switch (type) {
                case 'private':
                    // TAX-CODE
                    if (taxCode) {
                        taxCodeField.style.display = 'block';
                        taxCodeAbbr.classList.remove('optional');
                        taxCodeAbbr.classList.add('required');
                        taxCodeField.classList.add('validate-required');
                        taxCodeAbbr.innerText = '*';
                        taxCodeAbbr.outerHTML = taxCodeAbbr.outerHTML.replace(/span/g, "abbr");
                        taxCode.setAttribute('required', 'required');
                    }
                    // SDI check
                    if (sdiField) {
                        // SDI
                        sdiField.style.display = 'none';
                        sdi.value = ''; // reset value
                        sdiAbbr.classList.remove('required');
                        sdiAbbr.classList.add('optional');
                        sdiField.classList.remove('validate-required');
                        sdiAbbr.innerText = '(' + wc_el_inv_invoice.not_required_text + ')';
                        sdiAbbr.outerHTML = sdiAbbr.outerHTML.replace(/abbr/g, "span");
                        sdi.removeAttribute('required');
                    }

                    // VAT
                    vatField.style.display = 'none';
                    vatAbbr.classList.remove('required');
                    vatAbbr.classList.add('optional');
                    vatField.classList.remove('validate-required');
                    vatAbbr.innerText = '(' + wc_el_inv_invoice.not_required_text + ')';
                    vatAbbr.outerHTML = vatAbbr.outerHTML.replace(/abbr/g, "span");
                    vat.removeAttribute('required');

                    break;
                case 'company':
                case 'freelance':
                    // SDI check
                    if (sdiField) {
                        if ('on' !== wc_el_inv_invoice.disable_pec_sdi) {
                            // SDI
                            sdiField.style.display = 'block';
                            sdiAbbr.classList.remove('optional');
                            sdiAbbr.classList.add('required');
                            sdiField.classList.add('validate-required');
                            sdiAbbr.innerText = '*';
                            sdiAbbr.setAttribute('title', wc_el_inv_invoice.required_text);
                            sdiAbbr.outerHTML = sdiAbbr.outerHTML.replace(/span/g, "abbr");
                            sdi.setAttribute('required', 'required');

                            sdiLabel.innerHTML = wc_el_inv_invoice.sdi_label + sdiAbbr.outerHTML;
                            sdiDesc.innerText = wc_el_inv_invoice.sdi_description;
                            sdiInput.placeholder = wc_el_inv_invoice.sdi_placeholder;
                        } else {
                            sdiField.style.display = 'none';
                            sdi.removeAttribute('required');
                        }
                    }

                    // VAT
                    vatField.style.display = 'block';
                    vatAbbr.classList.remove('optional');
                    vatAbbr.classList.add('required');
                    vatField.classList.add('validate-required');
                    vatAbbr.innerText = '*';
                    vatAbbr.setAttribute('title', wc_el_inv_invoice.required_text);
                    vatAbbr.outerHTML = vatAbbr.outerHTML.replace(/span/g, "abbr");
                    vat.setAttribute('required', 'required');

                    // TAX-CODE
                    if (taxCode && 'on' !== wc_el_inv_invoice.disable_cf) {
                        taxCodeField.style.display = 'block';
                        taxCodeAbbr.classList.remove('optional');
                        taxCodeAbbr.classList.add('required');
                        taxCodeField.classList.add('validate-required');
                        taxCodeAbbr.innerText = '*';
                        taxCodeAbbr.outerHTML = taxCodeAbbr.outerHTML.replace(/span/g, "abbr");
                        taxCode.setAttribute('required', 'required');
                    } else if (taxCode && 'on' === wc_el_inv_invoice.disable_cf) {
                        taxCodeField.style.display = 'none';
                        taxCode.removeAttribute('required');
                    }
                    break;
                case '':
                    taxCodeField.style.display = 'none';
                    if (sdiField) {
                        sdiField.style.display = 'none';
                    }
                    if (vatField) {
                        vatField.style.display = 'none';
                    }
                    break;
                default:
                    break;
            }
        }

        /**
         * Choice type
         * @param ev
         */
        function choiceType(ev)
        {
            var choiceType = document.getElementById('billing_choice_type');
            if (!choiceType) {
                toggleFieldsDisplay('reset', ev);
                return;
            }

            // Remove optional text
            var choiceTypeLabel = document.querySelector('#billing_choice_type_field > label span.optional');
            if (choiceTypeLabel) {
                choiceTypeLabel.remove();
            }

            if ('load' === ev.type) {
                toggleFieldsDisplay(choiceType.options[choiceType.selectedIndex].value, ev);
            }

            $(choiceType).on('change', function (evt) {
                toggleFieldsDisplay(this.value, evt);
            });

        }

        /**
         * Toggle Display fields
         *
         * @param type
         * @param ev
         */
        function toggleFieldsDisplay(type, ev)
        {
            var invoiceType = document.getElementById('billing_invoice_type');
            var invoiceTypeField = document.getElementById('billing_invoice_type_field');
            var sdiField = document.getElementById('billing_sdi_type_field');
            var vatField = document.getElementById('billing_vat_number_field');
            var taxCodeField = document.getElementById('billing_tax_code_field');
            var sdi = document.getElementById('billing_sdi_type');
            var vat = document.getElementById('billing_vat_number');
            var taxCode = document.getElementById('billing_tax_code');

            switch (type) {
                case'invoice':
                    var country = document.getElementById('billing_country');
                    if (invoiceType) {
                        // Check condition for display fields based on country and invoice type selected
                        if ('' === invoiceType.options[invoiceType.selectedIndex].value) {
                            invoiceTypeField.style.display = 'block';
                        } else {
                            invoiceTypeField.style.display = 'block';

                            if ('private' === invoiceType.options[invoiceType.selectedIndex].value) {
                                taxCodeField.style.display = 'block';
                            } else if ('company' === invoiceType.options[invoiceType.selectedIndex].value ||
                                       'freelance' === invoiceType.options[invoiceType.selectedIndex].value
                            ) {
                                // Display and reset value
                                if (vat) {
                                    vatField.style.display = 'block';
                                    vat.value = '';
                                }
                                // Display Only IT
                                if ('IT' === country.value) {
                                    if (sdi) {
                                        sdiField.style.display = 'block';
                                        sdi.value = '';
                                    }
                                }
                                if (taxCode) {
                                    taxCodeField.style.display = 'block';
                                    taxCode.value = '';
                                }
                            }

                            switchType(invoiceType.options[invoiceType.selectedIndex].value, ev);
                        }
                    }
                    break;
                case'receipt':
                    // It serves as a discriminant to generate the receipt
                    // fake data to validate the fields
                    if (vat) {
                        vat.value = '11111111111';
                        vatField.style.display = 'none';
                    }
                    if (sdi) {
                        sdi.value = '1111111';
                        sdiField.style.display = 'none';
                    }
                    if (taxCode) {
                        taxCode.value = 'XXXXXX00L00L000X';
                        taxCodeField.style.display = 'none';
                    }
                    invoiceTypeField.style.display = 'none';
                    break;
                // Reset fields
                case'reset':
                    if (vat) {
                        vat.value = '';
                    }
                    if (sdi) {
                        sdi.value = '';
                    }
                    if (taxCode) {
                        taxCode.value = '';
                    }
                    break;
                default:
                    break;
            }
        }

        // Set required fields
        setRequired();

        // Load listener
        window.addEventListener('load', function (ev) {
            // Default
            var invoiceType = document.getElementById('billing_invoice_type');
            var country = document.getElementById('billing_country');
            if (!invoiceType) {
                return false;
            }

            // Required and in not UE country
            if ('IT' === country.value &&
                -1 !== wc_el_inv_invoice.eu_vat_country.indexOf(wc_el_inv_invoice.country)
            ) {
                switchType(invoiceType.options[invoiceType.selectedIndex].value, ev);
            } else {
                switchTypeNoIT(invoiceType.options[invoiceType.selectedIndex].value, ev)
            }

            // Switch country
            switchCountry(country.value, invoiceType.value, ev);
            choiceType(ev);
            initSelect2();
            cloneValueVat();
        })

    }(jQuery, window.wc_el_inv_invoice)
);
