<?php
/**
 * header.php
 *
 * @since      1.0.0
 * @package    ${NAMESPACE}
 * @author     alfiopiccione <alfio.piccione@gmail.com>
 * @copyright  Copyright (c) 2019, alfiopiccione
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2
 *
 * Copyright (C) 2019 alfiopiccione <alfio.piccione@gmail.com>
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

use \WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields as G;

$options = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();

$labelName                = esc_html__('Name:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelSurName             = esc_html__('Surname:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelCompany             = esc_html__('Company:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelVat                 = esc_html__('VAT:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelTaxRegime           = esc_html__('Cod Regime:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelStoreAddress        = esc_html__('Address:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelStorePhone          = esc_html__('Phone:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelStoreEmail          = esc_html__('Email:', WC_EL_INV_FREE_TEXTDOMAIN);
?>
<table class="head container">
    <tr>
        <td class="header" width="50%">
            <?php if ($options->getOptions('invoice_pdf_logo_url')): ?>
                <img alt="invoice logo" width="120px"
                     src="<?php echo esc_url($options->getOptions('invoice_pdf_logo_url')); ?>">
            <?php else: ?>
                <?php echo get_bloginfo('Name'); ?>
            <?php endif; ?>
        </td>
        <td class="shop-info" width="50%">
            <div style="font-size:12px;">
                <?php echo '<strong>' . $labelName . '</strong>' . ' ' . esc_html__(G::getGeneralInvoiceOptionName()); ?>
                <br>
                <?php echo '<strong>' . $labelSurName . '</strong>' . ' ' . esc_html__(G::getGeneralInvoiceOptionSurname()); ?>
                <br>
                <?php echo '<strong>' . $labelCompany . '</strong>' . ' ' . esc_html__(G::getGeneralInvoiceOptionCompanyName()); ?>
                <br>
                <?php echo '<strong>' . $labelVat . '</strong>' . ' ' . esc_html__(G::getGeneralInvoiceOptionCountryState() . G::getGeneralInvoiceOptionVatNumber()); ?>
                <br>
                <?php echo '<strong>' . $labelTaxRegime . '</strong>' . ' ' . esc_html__(G::getGeneralInvoiceOptionTaxRegime()); ?>
                <br>
                <?php echo '<strong>' . $labelStoreAddress . '</strong>' . ' '; ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionAddress()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionCity()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionPostCode()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionCountryState()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionCountryProvince()); ?><br>
                <?php echo '<strong>' . $labelStorePhone . '</strong>' . ' ' . esc_html__(G::getGeneralInvoiceOptionPhoneNumber()); ?>
                <br>
                <?php echo '<strong>' . $labelStoreEmail . '</strong>' . ' ' . esc_html__(G::getGeneralInvoiceOptionEmailAddress()); ?>
                <br>
                <br>
            </div>
        </td>
    </tr>
</table>
