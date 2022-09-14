<?php
/**
 * header.php
 *
 * @since      2.0.0
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

$options              = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$invoiceHtml          = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init()->getOptions('invoice_html');
$invoiceHtmlFromQuery = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'html_to_pdf', FILTER_UNSAFE_RAW);

// Invoice Logo
$logoUrl = $options->getOptions('invoice_pdf_logo_url');
$logo    = ((! $invoiceHtml || 'off' === $invoiceHtml) || 'true' === $invoiceHtmlFromQuery) &&
           (0 === (int)ini_get('allow_url_fopen')) ?
    esc_url($_SERVER['DOCUMENT_ROOT'] . parse_url($logoUrl, PHP_URL_PATH)) :
    esc_url($logoUrl);

/**
 * Filter logo URL/DIR
 */
$logo = apply_filters('wc_el_inv-invoice_pdf_logo_url', $logo);

$labelName                   = esc_html__('Name:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelSurName                = esc_html__('Surname:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelCompany                = esc_html__('Company:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelVat                    = esc_html__('VAT:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelTaxRegime              = esc_html__('Cod Regime:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelProvinceOfficeRegister = esc_html__('Province business register office:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelReaNumber              = esc_html__('REA number:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelLiquidationStatus      = esc_html__('Liquidation status:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelStoreAddress           = esc_html__('Address:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelStorePhone             = esc_html__('Phone:', WC_EL_INV_FREE_TEXTDOMAIN);
$labelStoreEmail             = esc_html__('Email:', WC_EL_INV_FREE_TEXTDOMAIN);

$name      = G::getGeneralInvoiceOptionName();
$surname   = G::getGeneralInvoiceOptionSurname();
$company   = G::getGeneralInvoiceOptionCompanyName();
$vat       = G::getGeneralInvoiceOptionCountryState() . G::getGeneralInvoiceOptionVatNumber();
$taxRegime = G::getGeneralInvoiceOptionTaxRegime();
$phone     = G::getGeneralInvoiceOptionPhoneNumber();
$email     = G::getGeneralInvoiceOptionEmailAddress();
$reaOffice = G::getGeneralInvoiceOptionProvinceOfficeBusinessRegister();
$reaNumber = G::getGeneralInvoiceOptionReaNumber();
$reaStatus = G::getGeneralInvoiceOptionLiquidationStatus();
if ('LS' === $reaStatus) {
    $reaStatus = $reaStatus . ' - ' . esc_html__('in liquidation', WC_EL_INV_FREE_TEXTDOMAIN);
} elseif ('LN' === $reaStatus) {
    $reaStatus = $reaStatus . ' - ' . esc_html__('not in liquidation', WC_EL_INV_FREE_TEXTDOMAIN);
}
?>
<table class="head container">
    <tr>
        <td class="header" style="width:50%;min-width:300px">
            <?php if ($logo): ?>
                <img alt="<?php echo $company; ?>" style="width:100%;min-width:220px;max-width:220px;"
                     src="<?php echo $logo; ?>">
            <?php else: ?>
                <?php echo apply_filters('wc_el_inv-invoice_pdf_logo_text', $company); ?>
            <?php endif; ?>
        </td>
        <td class="shop-info" style="width:50%;min-width:500px;margin-left:100px;">
            <div style="font-size:12px;">
                <?php if ('' === $company) : ?>
                    <?php echo '<strong>' . $labelName . '</strong>' . ' ' . esc_html__($name); ?><br>
                    <?php echo '<strong>' . $labelSurName . '</strong>' . ' ' . esc_html__($surname); ?><br>
                <?php else : ?>
                    <?php echo '<strong>' . $labelCompany . '</strong>' . ' ' . esc_html__($company); ?><br>
                <?php endif; ?>
                <?php echo '<strong>' . $labelVat . '</strong>' . ' ' . esc_html__($vat); ?><br>
                <?php echo '<strong>' . $labelTaxRegime . '</strong>' . ' ' . esc_html__($taxRegime); ?><br>
                <?php if ($reaOffice && $reaNumber && $reaStatus) : ?>
                    <?php echo '<strong>' . $labelProvinceOfficeRegister . '</strong>' . ' ' . esc_html__($reaOffice); ?>
                    <br>
                    <?php echo '<strong>' . $labelReaNumber . '</strong>' . ' ' . esc_html__($reaNumber); ?><br>
                    <?php echo '<strong>' . $labelLiquidationStatus . '</strong>' . ' ' . esc_html__($reaStatus); ?><br>
                <?php endif; ?>
                <?php echo '<strong>' . $labelStoreAddress . '</strong>' . ' '; ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionAddress()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionCity()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionPostCode()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionCountryState()); ?>
                <?php esc_html_e(G::getGeneralInvoiceOptionCountryProvince()); ?><br>
                <?php echo '<strong>' . $labelStorePhone . '</strong>' . ' ' . esc_html__($phone); ?><br>
                <?php echo '<strong>' . $labelStoreEmail . '</strong>' . ' ' . esc_html__($email); ?><br>
                <br>
            </div>
        </td>
    </tr>
</table>
