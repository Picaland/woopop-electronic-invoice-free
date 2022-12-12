<?php
/**
 * CreatePdf.php
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

namespace WcElectronInvoiceFree\Pdf;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Dompdf\Dompdf;
use Dompdf\Options;
use WcElectronInvoiceFree\Admin\Settings\OptionPage;
use WcElectronInvoiceFree\Plugin;
use WcElectronInvoiceFree\Utils\TimeZone;
use WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields;
use WcElectronInvoiceFree\WooCommerce\Fields\InvoiceFields;

/**
 * Class CreatePdf
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class CreatePdf
{
    /**
     * List type
     *
     * @since 1.0.0
     */
    const LIST_TYPE = 'shop_order';

    /**
     * Pdf
     *
     * @since  1.0.0
     *
     * @var object \mPDF The mPDF object
     */
    public static $pdf;

    /**
     * Extra Italian SDI code
     *
     * @since 1.0.0
     */
    const NO_IT_SDI_CODE = 'XXXXXXX';

    /**
     * Regex Tax Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexCF = "/^([A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-9LMNPQRSTUV]{2}[A-Za-z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1})$/i";

    /**
     * Regex Web Service Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexWEBSERV = "/^[a-zA-Z0-9]{7}$/i";

    /**
     * Regex PEC
     *
     * @since 1.0.0
     *
     * @var string
     */
    private $regexPEC = "/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:\w*.?pec(?:.?\w+)*)$/i";

    /**
     * Regex Legal Mail
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexLEGALMAIL = "/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:\w*.?legalmail(?:.?\w+)*)$/i";

    /**
     * Regex VAT Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexVAT = "/^(ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{13})|HU[0-9]{8}|IE[0-9][A-Z0-9][0-9]{5}[A-Z]{1,2}|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$/i";

    /**
     * CreatePdf constructor.
     *
     * @param Dompdf $pdf
     *
     * @since  1.0.0
     */
    public function __construct(Dompdf $pdf)
    {
        self::$pdf = $pdf;

        // Set Dompdf Options
        $options = new Options();
        $options->set('defaultFont', 'helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        self::$pdf->setOptions($options);
    }

    /**
     * Invoice Number
     *
     * @param $order
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function invoiceNumber($order)
    {
        $order              = wc_get_order($order->id);
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order_Refund');

        if (! $order instanceof $wcOrderClass && ! $order instanceof $wcOrderRefundClass) {
            return '';
        }

        $options = OptionPage::init();
        $number  = $order->get_meta('order_number_invoice');

        if ($order instanceof $wcOrderRefundClass) {
            $number = $order->get_meta("refund_number_invoice-{$order->get_id()}");
        }

        // Number of digits
        $digits = $options->getOptions('number_digits_in_invoice');
        $digits = isset($digits) && '' !== $digits ? $digits : 2;
        // Prefix
        $prefix = $options->getOptions('prefix_invoice_number');
        $prefix = isset($prefix) && '' !== $prefix ? "{$prefix}-" : 'inv-';

        /**
         * Invoice prefix filter
         */
        $prefix = apply_filters('wc_el_inv-prefix_invoice', $prefix, $order);

        // Suffix
        $suffix     = $options->getOptions('suffix_invoice_number');
        $suffixYear = $options->getOptions('suffix_year_invoice_number');
        if ('on' === $suffixYear) {
            $created = $order->get_date_created();
            $suffix  = "/" . $created->format('Y');
        } else {
            $suffix = isset($suffix) && '' !== $suffix ? $suffix : '';
        }

        /**
         * Invoice suffix filter
         */
        $suffix = apply_filters('wc_el_inv-suffix_invoice', $suffix, $order);

        // Invoice number
        $invNumber = str_pad($number, $digits, '0', STR_PAD_LEFT);

        return isset($number) && 0 !== $number && '' !== $number ? "{$prefix}{$invNumber}{$suffix}" : $order->get_id();
    }

    /**
     * Doc ID
     *
     * @param $order
     *
     * @return int The doc ID
     * @since 1.0.0
     *
     */
    private function docID($order)
    {
        $id = $order->id;

        // If Refund get id of the parent.
        if ('shop_order_refund' === $order->order_type) {
            $id = $order->parent_id;
        }

        return intval($id);
    }

    /**
     * Doc Type
     *
     * @param $order
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function docType($order)
    {
        if ('receipt' === $order->choice_type) {
            return esc_html__('Receipt', WC_EL_INV_FREE_TEXTDOMAIN);
        }

        switch ($order->order_type) {
            case 'shop_order':
                return esc_html__('Invoice', WC_EL_INV_FREE_TEXTDOMAIN);
                break;
            case 'shop_order_refund':
                return esc_html__('Credit note', WC_EL_INV_FREE_TEXTDOMAIN);
                break;
            default:
                break;
        }
    }

    /**
     * Date completed
     *
     * @param        $order
     * @param string $format
     * @param bool   $parent
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function dateCompleted($order, $format = 'Y-m-d', $parent = false)
    {
        $dateOrder = $order->date_created;

        // Get parent order if current data is refund
        if ($parent && 'shop_order_refund' === $order->order_type) {
            $parentOrder = wc_get_order($order->parent_id);

            // Parent order created
            $dateOrder = $parentOrder->get_date_created();
            $dateOrder = $dateOrder->format('c');
        }

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime($dateOrder);
            $date->setTimezone($timeZone);

            return $date->format($format);
        } catch (\Exception $e) {
            echo esc_html__('Error DateTime in dateCompleted: ') . $e->getMessage();
        }
    }

    /**
     * Date completed
     *
     * @param        $order
     * @param string $format
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function dateOrder($order, $format = 'Y-m-d')
    {
        $dateOrder = $order->date_created;

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime($dateOrder);
            $date->setTimezone($timeZone);

            return $date->format($format);
        } catch (\Exception $e) {
            echo esc_html__('Error DateTime in dateCompleted: ') . $e->getMessage();
        }
    }

    /**
     * Date invoice
     *
     * @param        $order
     * @param string $format
     *
     * @return string
     */
    private function dateInvoice($order, $format = 'Y-m-d')
    {
        $orderObj    = wc_get_order($order->id);
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName($orderObj, '\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($orderObj, '\WC_Order_Refund');
        $dateInvoice = null;

        if ($orderObj instanceof $wcOrderRefundClass) {
            $dateInvoice = $orderObj->get_date_created();
        } elseif ($orderObj instanceof $wcOrderClass) {
            $dateInvoice = $orderObj->get_date_completed();
        }

        if ($dateInvoice) {
            return $dateInvoice->date_i18n($format);
        }

        return $dateInvoice;
    }

    /**
     * Payment Method
     *
     * @param $order
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function paymentMethod($order)
    {
        if (! property_exists($order, 'payment_method') &&
            ! property_exists($order, 'refunded') &&
            ! isset($order->refunded['refunded_payment_method'])
        ) {
            return '';
        }

        /**
         * Payment method for refunded:
         */
        if ('shop_order_refund' === $order->order_type &&
            isset($order->refunded['refunded_payment_method'])
        ) {
            if ('' === $order->refunded['refunded_payment_method']) {
                return sprintf('MP01 - %s', esc_html__('Cash money', WC_EL_INV_FREE_TEXTDOMAIN));
            }
            switch ($order->refunded['refunded_payment_method']) {
                case 'MP01':
                    return sprintf('MP01 - %s', esc_html__('Cash money', WC_EL_INV_FREE_TEXTDOMAIN));
                case 'MP02':
                    return sprintf('MP02 - %s', esc_html__('Bank check', WC_EL_INV_FREE_TEXTDOMAIN));
                case 'MP03':
                    return sprintf('MP03 - %s', esc_html__('Bank check', WC_EL_INV_FREE_TEXTDOMAIN));
                case 'MP05':
                    return sprintf('MP05 - %s', esc_html__('Bank transfer', WC_EL_INV_FREE_TEXTDOMAIN));
                case 'MP08':
                    return sprintf('MP08 - %s', esc_html__('Credit Card', WC_EL_INV_FREE_TEXTDOMAIN));
                default:
                    return sprintf('MP01 - %s', esc_html__('Cash money', WC_EL_INV_FREE_TEXTDOMAIN));
                    break;
            }
        }

        if (property_exists($order, 'payment_method')) {
            false !== strpos($order->payment_method, 'stripe') ? $order->payment_method = 'stripe' : '';
            false !== strpos($order->payment_method, 'paypal') ? $order->payment_method = 'paypal' : '';
        }

        /**
         * Payment method for order:
         *
         * - Bacs
         * - Cheque
         * - PayPal Express Checkout
         * - Stripe
         * - Stripe SEPA
         */
        if (property_exists($order, 'payment_method')) {
            $methodTitle = $order->payment_method_title;
            switch ($order->payment_method) {
                case 'bacs':
                    return esc_html('MP05 - ' . $methodTitle);
                case 'cheque':
                    return esc_html('MP02 - ' . $methodTitle);
                case 'paypal':
                case 'ppec_paypal':
                case 'ppcp-gateway':
                case 'stripe':
                case 'xpay':
                case 'soisy':
                case 'igfs':
                    return esc_html('MP08 - ' . $methodTitle);
                case 'stripe_sepa':
                    return esc_html('MP19 - ' . $methodTitle);
                default:
                    return apply_filters('wc_el_inv-default_payment_method_pdf_invoice',
                        sprintf('MP01 - %s', esc_html__('Cash money', WC_EL_INV_FREE_TEXTDOMAIN)),
                        $order->payment_method
                    );
            }
        }
    }

    /**
     * Product Description
     *
     * @param      $item
     * @param null $type
     *
     * @return string|string[]|null
     * @since 1.0.0
     *
     */
    private function productDescription($item, $type = null)
    {
        if (! isset($item['product_id'])) {
            return '';
        }

        $post = get_post(intval($item['product_id']));

        /**
         * Filter - description excerpt + title or only title
         */
        if (apply_filters('wc_el_inv-product_excerpt_description_pdf_invoice', true) && $post->post_excerpt) {
            $description = $post->post_excerpt;
        } else {
            $description = $post->post_title;
        }

        /**
         * Filter - force description only post title
         */
        if (true === apply_filters('wc_el_inv-product_title_description_pdf_invoice', false)) {
            $description = $post->post_title;
        }

        /**
         * Filter - Product Meta data
         */
        if (true === apply_filters('wc_el_inv-product_meta_description_pdf_invoice', false)) {
            $metaString = '';
            if (! empty($item['meta_data'])) {
                foreach ($item['meta_data'] as $index => $meta) {
                    $sep        = $index === count($item['meta_data']) - 1 ? '' : ', ';
                    $metaString = $metaString . "{$meta['key']}: {$meta['value']}{$sep}";
                }

                $description = "{$description} {$metaString}";
            }
        }

        /**
         * Filter - description for item
         */
        $description = apply_filters('wc_el_inv-product_description_pdf_invoice', $description, $item);

        if ('refund' === $type) {
            // Reset description content, view only title
            // * the meta are managed in the pdf template details.
            $description = $post->post_title;
        }

        $description = mb_strimwidth($description, 0, 500, '...');

        return \WcElectronInvoiceFree\Functions\stripTags($description, true);
    }

    /**
     * Tax Rate
     *
     * @param        $item
     * @param string $get
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function taxRate($item, $get = 'rate')
    {
        if (! isset($item['product_id']) && ! isset($item['variation_id'])) {
            return '';
        }

        $taxEnabled = get_option('woocommerce_calc_taxes');
        if ('yes' !== $taxEnabled) {
            return floatval(0);
        }

        $taxStatus = '';
        $taxRates  = array();

        $id      = isset($item['variation_id']) && 0 !== intval($item['variation_id']) ? intval($item['variation_id']) : intval($item['product_id']);
        $product = wc_get_product($id);
        if ($product instanceof \WC_Product) {
            $taxRates  = \WC_Tax::get_rates($product->get_tax_class());
            $taxStatus = $product->get_tax_status();

            // Double check get rates by billing country
            $order = wc_get_order($item['order_id']);
            $taxes = \WC_Tax::get_rates_for_tax_class($product->get_tax_class());
            foreach ($taxes as $tax) {
                if ($tax->tax_rate_country === $order->get_billing_country()) {
                    return $this->numberFormat($tax->tax_rate, 0);
                    break;
                }
            }
        }

        if (empty($taxRates)) {
            $taxRates = \WC_Tax::get_base_tax_rates();
        }

        $taxRate = reset($taxRates);

        switch ($get) {
            case 'rate':
                return $taxStatus === 'taxable' && floatval(0) !== floatval($item['total_tax']) ? $taxRate[$get] : floatval(0);
            default:
                return '';
                break;
        }
    }

    /**
     * Shipping Rate
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function shippingRate()
    {
        $taxRates = \WC_Tax::get_shipping_tax_rates();
        if (empty($taxRates)) {
            $taxRates = \WC_Tax::get_base_tax_rates();
        }

        $taxRate = reset($taxRates);
        if ('no' === $taxRate['shipping']) {
            return '';
        }

        return $taxRate['rate'];
    }

    /**
     * Code or Pec
     *
     * @param $ordersData
     * @param $type
     *
     * @return null|string
     * @since 1.0.0
     *
     */
    private function codeOrPec($ordersData, $type)
    {
        if (! property_exists($ordersData, 'sdi_type')) {
            return '';
        }

        $country = $this->customerCountry($ordersData);

        // Get pec
        $pec         = preg_match($this->regexPEC, $ordersData->sdi_type) ? $ordersData->sdi_type : null;
        $legalMail   = preg_match($this->regexLEGALMAIL, $ordersData->sdi_type) ? $ordersData->sdi_type : null;
        $generalMail = false !== filter_var($ordersData->sdi_type,
            FILTER_VALIDATE_EMAIL) ? $ordersData->sdi_type : null;

        if (null === $pec && null === $legalMail && $generalMail) {
            $pec = $generalMail;
        }

        $pec = $pec ?: $legalMail;

        $invoiceType = $ordersData->invoice_type;

        $code     = '';
        $emailPec = '';

        switch ($invoiceType) {
            case 'private':
                $code     = ! preg_match($this->regexWEBSERV, $ordersData->sdi_type) ?
                    '0000000' : $ordersData->sdi_type;
                $emailPec = '0000000' !== $code ? $pec : null;
                break;
            case 'freelance':
            case 'company':
                $code     = ! preg_match($this->regexWEBSERV,
                    $ordersData->sdi_type) && $pec || '' === $ordersData->sdi_type ?
                    '0000000' : $ordersData->sdi_type;
                $emailPec = '0000000' === $code ? $pec : null;
                break;
            default:
                break;
        }

        switch ($type) {
            case 'pec':
                return $emailPec;
                break;
            case 'code':
                return 'IT' === $country ? $code : self::NO_IT_SDI_CODE;
                break;
            default:
                return '';
                break;
        }
    }

    /**
     * Customer country
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerCountry($ordersData)
    {
        if (! property_exists($ordersData, 'billing')) {
            return '';
        }

        return isset($ordersData->billing['country']) ? $ordersData->billing['country'] : '';
    }

    /**
     * Customer Tax Code
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerTaxCodeNumber($ordersData)
    {
        if (! property_exists($ordersData, 'tax_code')) {
            return '';
        }

        $country = $this->customerCountry($ordersData);

        if ('private' !== $ordersData->invoice_type) {
            // If VAT format
            if (preg_match($this->regexVAT, $country . $ordersData->tax_code)) {
                $taxCode = $country . $ordersData->tax_code;
                // Else TAX code
            } else {
                $taxCode = $ordersData->tax_code;
            }

            return isset($ordersData->tax_code) ? strtoupper($taxCode) : '';
        }

        return isset($ordersData->tax_code) && preg_match($this->regexCF, $ordersData->tax_code) ?
            strtoupper($ordersData->tax_code) : '';
    }

    /**
     * Customer vat
     *
     * @param      $ordersData
     * @param bool $onlyNumber
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerVatNumber($ordersData, $onlyNumber = false)
    {
        if (! property_exists($ordersData, 'vat_number')) {
            return '';
        }

        $vatNumber = '';

        $country = $this->customerCountry($ordersData);

        if (isset($ordersData->vat_number) && preg_match($this->regexVAT, $country . $ordersData->vat_number)) {
            $vatNumber = $country . $ordersData->vat_number;
        }

        if ($onlyNumber) {
            $vatNumber = $ordersData->vat_number;
        }

        return $vatNumber;
    }

    /**
     * Progressive file number
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function progressiveFileNumber($ordersData)
    {
        if (! property_exists($ordersData, 'id')) {
            return '';
        }

        $order              = wc_get_order($ordersData->id);
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order_Refund');

        $number = $order->get_meta('order_number_invoice');
        if ($order instanceof $wcOrderRefundClass) {
            $number = $order->get_meta("refund_number_invoice-{$order->get_id()}");
        }

        if ($number >= 1) {
            $number = base_convert($number, 10, 36);
            $number = str_pad($number, 5, '0', STR_PAD_LEFT);

            return strtoupper($number);
        }
    }

    /**
     * Calc Unit price from total and total tax
     *
     * @param      $item
     * @param null $ordersData
     *
     * @return float|int
     * @since 1.2
     */
    public function calcUnitPrice($item, $ordersData = null, $format = true)
    {
        $total    = isset($item['total']) ? $item['total'] : 0;
        $subtotal = isset($item['subtotal']) ? $item['subtotal'] : 0;
        $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;
        $quantity = isset($item['quantity']) ? $item['quantity'] : 1;

        if ($subtotal > $total) {
            $unitTaxedPrice = (($subtotal) / $quantity);
        } else {
            $unitTaxedPrice = (($total + $totalTax) / $quantity);
        }
        //$unitTaxedPrice = $this->numberFormat($unitTaxedPrice, 6);
        // Vat
        $taxEnabled = get_option('woocommerce_calc_taxes');
        $vat        = 0;
        if ('yes' === $taxEnabled) {
            $vat = $this->numberFormat($this->taxRate($item));
        }

        if ($subtotal > $total) {
            $finalPrice = floatval($unitTaxedPrice);
        } else {
            $finalPrice = floatval($unitTaxedPrice) / (1 + (floatval($vat) / 100));
        }

        if (! $format) {
            return $finalPrice;
        }

        return $this->numberFormat($finalPrice, 6); // es: $unitTaxedPrice / 1,22 or 1.04
    }

    /**
     * Number Format
     *
     * @param int    $number
     * @param int    $decimal
     * @param bool   $abs
     * @param string $decSep
     * @param string $thSep
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function numberFormat($number = 0, $decimal = 2, $abs = true, $decSep = '.', $thSep = '')
    {
        if ($abs) {
            $number = abs($number);
        }

        $wctThousandSep = '' === $decSep ? get_option('woocommerce_price_thousand_sep') : $decSep;
        $wcDecimalSep   = '' === $thSep ? get_option('woocommerce_price_decimal_sep') : $thSep;

        return number_format($number, $decimal, $wcDecimalSep, $wctThousandSep);
    }

    /**
     * Remove Sent Invoice attachment file
     *
     * @return bool
     * @since 1.0.0
     *
     */
    public function removeSentInvoice()
    {
        // Get xml file
        $tempPDF = glob(
            Plugin::getPluginDirPath('/') . '/tempPdf/*'
        );

        if (! empty($tempPDF) && count($tempPDF) > 1) {
            foreach ($tempPDF as $file) {
                if (file_exists($file)) {
                    $info = pathinfo($file);
                    if ('invoice' !== $info['filename']) {
                        unlink($file);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Attachments Pdf To Email
     *
     * @param $attachments
     * @param $emailID
     * @param $order
     *
     * @return array|false|string
     * @since 1.0.0
     *
     */
    public function attachmentsPdfToEmail($attachments, $emailID, $order)
    {
        // Send attachments via email ?
        $active       = OptionPage::init()->getOptions('invoice_via_email');
        $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');

        // Not send/create attachments for the bulk action
        $actionBulkCheck = \WcElectronInvoiceFree\Functions\filterInput($_REQUEST, 'action', FILTER_UNSAFE_RAW);
        if ('mark_completed' === $actionBulkCheck) {
            return $attachments;
        }

        if ('on' !== $active) {
            return $attachments;
        }

        // check if all variables properly set
        if (! is_object($order) || ! isset($emailID)) {
            return $attachments;
        }

        // Skip User emails
        if ($order instanceof \WP_User) {
            return $attachments;
        }

        if (! $order instanceof $wcOrderClass) {
            return $attachments;
        }

        $orderID = $order->get_id();

        if ($orderID == false) {
            return $attachments;
        }

        // do not process low stock notifications, user emails etc!
        if (in_array($emailID, array(
                'no_stock',
                'low_stock',
                'backorder',
                'customer_new_account',
                'customer_reset_password',
            )) || get_post_type($orderID) !== self::LIST_TYPE) {
            return $attachments;
        }

        if ('customer_completed_order' === $emailID ||
            'custom_email_completed_order_pdf_invoice' === $emailID ||
            'custom_email_completed_order_pdf_receipt' === $emailID
        ) {
            try {

                // Pdf type conditions
                switch ($emailID) {
                    default :
                    case 'custom_email_completed_order_pdf_invoice':
                        $type = 'invoice';
                        break;
                    case 'custom_email_completed_order_pdf_receipt':
                        $type = 'receipt';
                        break;
                }

                // Set invoice number
                $options           = OptionPage::init();
                $nextInvoiceNumber = $options->getOptions('number_next_invoice');
                $invoiceNumber     = $order->get_meta('order_number_invoice');
                if ('' === $invoiceNumber) {
                    update_post_meta($orderID, 'order_number_invoice', intval($nextInvoiceNumber));
                }

                $nonce     = wp_create_nonce('wc_el_inv_invoice_pdf');
                $pdfArgs   = "?format=pdf&nonce={$nonce}&choice_type={$type}&html_to_pdf=true&v=" . time();
                $url       = home_url() . '/' . \WcElectronInvoiceFree\EndPoint\Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/' . $orderID . $pdfArgs;
                $pdfOutput = wp_remote_fopen(esc_url_raw($url));
                $data      = new \stdClass();
                $data->id  = $orderID;

                // File name
                $fileName = GeneralFields::getGeneralInvoiceOptionCountryState() .
                            GeneralFields::getGeneralInvoiceOptionVatNumber() . '_' .
                            $this->progressiveFileNumber($data) . '.pdf';

                if (! $pdfOutput) {
                    $message   = __('Error in generating the PDF, download it from your reserved area, or ask the seller for it',
                        WC_EL_INV_FREE_TEXTDOMAIN);
                    $pdfOutput = "<!DOCTYPE html><html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/><title>{$fileName}</title></head><body><h1>{$message}</h1></body></html>";
                }

                return $this->buildAttachment($order, $fileName, $pdfOutput);
            } catch (\Exception $e) {
                return '';
            }
        }

        return $attachments;
    }

    /**
     * Build Attachment
     *
     * @param $order
     * @param $fileName
     * @param $attachments
     *
     * @return array
     * @since  1.0.0
     *
     */
    public function buildAttachment($order, $fileName, $attachments)
    {
        $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');

        if (! $order instanceof $wcOrderClass) {
            return $attachments;
        }

        // Get pdf data & store in temp file
        $pdfData = $attachments;

        // Create PDF
        self::$pdf->loadHtml($pdfData);
        self::$pdf->setPaper('A4', 'portrait');
        self::$pdf->render();
        $pdf = self::$pdf->output();

        $pdfPath = Plugin::getPluginDirPath('/tempPdf/invoice.pdf');
        file_put_contents($pdfPath, $pdf);

        // Initialize new attachments
        $attachments = array();

        // Copy temp file
        $tempFile = copy(
            $pdfPath,
            Plugin::getPluginDirPath('/tempPdf') . '/' . $fileName
        );

        if ($tempFile) {
            $attachments[] = Plugin::getPluginDirPath('/tempPdf') . '/' . $fileName;
        }

        return $attachments;
    }

    /**
     * pdf Header
     *
     * @param $data
     */
    public function pdfHead($data)
    {
        // @codingStandardsIgnoreLine
        include_once Plugin::getPluginDirPath('/views/pdf/head.php');
    }

    /**
     * pdf Addresses
     *
     * @param $data
     */
    public function pdfAddresses($data)
    {
        // @codingStandardsIgnoreLine
        include_once Plugin::getPluginDirPath('/views/pdf/addresses.php');
    }

    /**
     * pdf Details
     *
     * @param $data
     */
    public function pdfDetails($data)
    {
        // @codingStandardsIgnoreLine
        include_once Plugin::getPluginDirPath('/views/pdf/details.php');
    }

    /**
     * pdf Order Totals
     *
     * @param $data
     */
    public function pdfOrderTotals($data)
    {
        // @codingStandardsIgnoreLine
        include_once Plugin::getPluginDirPath('/views/pdf/order-totals.php');
    }

    /**
     * pdf Summary
     *
     * @param $data
     */
    public function pdfSummary($data)
    {
        // @codingStandardsIgnoreLine
        include_once Plugin::getPluginDirPath('/views/pdf/summary.php');
    }

    /**
     * pdf Footer
     *
     * @param $data
     */
    public function pdfFooter($data)
    {
        // @codingStandardsIgnoreLine
        include_once Plugin::getPluginDirPath('/views/pdf/footer.php');
    }

    /**
     * Create PDF
     *
     * @param $xmlData
     *
     * @return mixed
     * @since  1.0.0
     *
     */
    public function buildPdf($xmlData)
    {
        $getFormat = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'format', FILTER_UNSAFE_RAW);
        $getNonce  = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'nonce', FILTER_UNSAFE_RAW);
        $data      = ! empty($xmlData) && ! empty($xmlData[0]) ? (object)$xmlData[0] : null;

        // Override choice_type from $_GET['choice_type'] value
        $choiceType = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'choice_type', FILTER_UNSAFE_RAW);

        if (! $data) {
            return $xmlData;
        }

        // Default type choice.
        $data->choice_type = 'invoice';
        if ($choiceType) {
            $data->choice_type = $choiceType;
        }

        $retrieveFromRemote = isset($_SERVER['HTTP_REFERER']) ?
            strpos($_SERVER['HTTP_REFERER'], 'format=pdf&nonce=') : false;

        if ('pdf' === $getFormat &&
            false === wp_verify_nonce($getNonce, 'wc_el_inv_invoice_pdf') &&
            false === $retrieveFromRemote
        ) {
            wp_send_json(esc_html__('ERROR: you can not view the PDF for security and privacy issues. to view it you must be logged in',
                WC_EL_INV_FREE_TEXTDOMAIN), 400);
            die();
        }

        if (! $data || 'pdf' !== $getFormat) {
            return $xmlData;
        }

        // @codingStandardsIgnoreLine
        $fileName = "pdf" . ucfirst(esc_attr($data->choice_type));
        if (file_exists(get_theme_file_path("/woocommerce/pdf/{$fileName}.php"))) {
            include_once get_theme_file_path("/woocommerce/pdf/{$fileName}.php");
        } else {
            include_once Plugin::getPluginDirPath("/views/{$fileName}.php");
        }
    }

    /**
     * Create PDF
     *
     * @param array $xmlData The args for create Pdf
     *
     * @return mixed
     */
    public static function create($xmlData)
    {
        if (class_exists('\Dompdf\Dompdf')) {
            $instance = new self(self::$pdf);

            try {
                return $instance->buildPdf($xmlData);
            } catch (\Exception $e) {
                echo 'Create Pdf Exception: ', $e->getMessage(), "\n";
            }
        }
    }
}