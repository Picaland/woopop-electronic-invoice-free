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

use Mpdf\Mpdf;
use WcElectronInvoiceFree\Admin\Settings\OptionPage;
use WcElectronInvoiceFree\Plugin;
use WcElectronInvoiceFree\Utils\TimeZone;
use WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields;
use function WcElectronInvoiceFree\Functions\wcOrderClassName;

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
    private $pdf;

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
     * @param Mpdf $pdf
     *
     * @since  1.0.0
     *
     */
    public function __construct(\Mpdf\Mpdf $pdf)
    {
        $this->pdf = $pdf;
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
        $wcOrderClass       = wcOrderClassName('\WC_Order');
        $wcOrderRefundClass = wcOrderClassName('\WC_Order_Refund');
        $order              = wc_get_order($order->id);
        if (! $order instanceof $wcOrderClass && ! $order instanceof $wcOrderRefundClass) {
            return '';
        }

        $options = OptionPage::init();
        $number  = $order->get_meta('order_number_invoice');

        if ($order instanceof \WC_Order_Refund) {
            $number = $order->get_meta("refund_number_invoice-{$order->get_id()}");
        }

        // Number of digits
        $digits = $options->getOptions('number_digits_in_invoice');
        $digits = isset($digits) && '' !== $digits ? $digits : 2;
        // Prefix
        $prefix = $options->getOptions('prefix_invoice_number');
        $prefix = isset($prefix) && '' !== $prefix ? $prefix : 'inv';
        // Suffix
        $suffix = $options->getOptions('suffix_invoice_number');
        $suffix = isset($suffix) && '' !== $suffix ? $suffix : '';
        // Invoice number
        $invNumber = str_pad($number, $digits, '0', STR_PAD_LEFT);

        return isset($number) && 0 !== $number && '' !== $number ? "{$prefix}-{$invNumber}{$suffix}" : $order->get_id();
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
        if ('private' === $order->invoice_type || 'receipt' === $order->choice_type) {
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
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function dateCompleted($order, $format = 'Y-m-d')
    {
        if (isset($order->date_completed)) {
            $dateOrder = $order->date_completed;
        } elseif ($order->date_modified) {
            $dateOrder = $order->date_modified;
        } else {
            $dateOrder = $order->date_created;
        }

        // Get parent order if current data is refund
        if ('shop_order_refund' === $order->order_type) {
            $parentOrder = wc_get_order($order->parent_id);

            if ($parentOrder->get_date_completed()) {
                $dateOrder = $parentOrder->get_date_completed();
            } elseif ($parentOrder->get_date_modified()) {
                $dateOrder = $parentOrder->get_date_modified();
            } else {
                $dateOrder = $parentOrder->get_date_created();
            }

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
            isset($order->refunded['refunded_payment_method']) &&
            '' !== $order->refunded['refunded_payment_method']
        ) {
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
                    return '';
                    break;
            }
        }

        false !== strpos($order->payment_method, 'stripe') ? $order->payment_method = 'stripe' : '';
        false !== strpos($order->payment_method, 'paypal') ? $order->payment_method = 'paypal' : '';

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
                    return esc_html('MP08 - ' . $methodTitle);
                case 'ppec_paypal':
                    return esc_html('MP08 - ' . $methodTitle);
                case 'stripe':
                    return esc_html('MP08 - ' . $methodTitle);
                case 'stripe_sepa':
                    return esc_html('MP19 - ' . $methodTitle);
                    break;
                default:
                    return 'MP01';
                    break;
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

        $description = $post->post_title . ' - ' . $post->post_excerpt;

        if (! $post->post_excerpt) {
            $description = $post->post_title . ' - ' . $post->post_content;

            if (! $post->post_content) {
                $description = $post->post_title;
            }
        }

        if ('refund' === $type) {
            $description = '';
            $description = sprintf('%s %s', esc_html__('Refunded', WC_EL_INV_FREE_TEXTDOMAIN), "{$description}");
        }

        $description = mb_strimwidth($description, 0, 500, '...');

        return \WcElectronInvoiceFree\Functions\stripTags($description);
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
        if (! isset($item['product_id'])) {
            return '';
        }

        $product  = wc_get_product(intval($item['product_id']));
        $taxRates = \WC_Tax::get_rates($product->get_tax_class());
        if (empty($taxRates)) {
            $taxRates = \WC_Tax::get_base_tax_rates();
        }

        $taxRate = reset($taxRates);

        switch ($get) {
            case 'rate':
                return $taxRate[$get];
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
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerVatNumber($ordersData)
    {
        if (! property_exists($ordersData, 'vat_number')) {
            return '';
        }

        $vatNumber = '';

        $country = $this->customerCountry($ordersData);

        if (isset($ordersData->vat_number) && preg_match($this->regexVAT, $country . $ordersData->vat_number)) {
            $vatNumber = $country . $ordersData->vat_number;
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

        $order = wc_get_order($ordersData->id);

        $number = $order->get_meta('order_number_invoice');
        if ($order instanceof \WC_Order_Refund) {
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
     * @param $item
     *
     * @return float|int
     * @since 1.2
     */
    public function calcUnitPrice($item)
    {
        $total    = isset($item['total']) ? $item['total'] : 0;
        $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;
        $quantity = isset($item['quantity']) ? $item['quantity'] : 0;

        $unitTaxedPrice = (($total + $totalTax) / $quantity);
        // Vat
        $vat = $this->numberFormat($this->taxRate($item));

        return $unitTaxedPrice / (($vat / 100) + 1); // es: $unitTaxedPrice / 1,22 or 1.04
    }

    /**
     * Number Format
     *
     * @param int $number
     * @param int $decimal
     * @param bool $abs
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function numberFormat($number = 0, $decimal = 2, $abs = true)
    {
        if ($abs) {
            $number = abs($number);
        }

        return number_format($number, $decimal, '.', '');
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
        $active = OptionPage::init()->getOptions('invoice_via_email');
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

        if (! $order instanceof \WC_Order) {
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

        if ('customer_completed_order' === $emailID) {
            try {
                $nonce       = wp_create_nonce('wc_el_inv_invoice_pdf');
                $pdfArgs     = "?format=pdf&nonce={$nonce}";
                $url         = site_url() . '/' . \WcElectronInvoiceFree\EndPoint\Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/' . $orderID . $pdfArgs;
                $attachments = wp_remote_fopen(esc_url_raw($url));
                $data        = new \stdClass();
                $data->id    = $orderID;

                // File name
                $fileName = GeneralFields::getGeneralInvoiceOptionCountryState() .
                            GeneralFields::getGeneralInvoiceOptionVatNumber() . '_' .
                            $this->progressiveFileNumber($data) . '.pdf';

                $attachments = $this->buildAttachment($order, $fileName, $attachments);

                return $attachments;
            } catch (\Exception $e) {
                print_r(
                    esc_html__(
                        'Error in completed order attachments - message:',
                        WC_EL_INV_FREE_TEXTDOMAIN) . $e->getMessage()
                );
                die();
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
        if (! $order instanceof \WC_Order) {
            return $attachments;
        }

        // Get pdf data & store in temp file
        $pdfData = $attachments;
        $pdfPath = Plugin::getPluginDirPath('/tempPdf/invoice.pdf');
        file_put_contents($pdfPath, $pdfData);

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
        $getFormat = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'format', FILTER_SANITIZE_STRING);
        $getNonce  = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'nonce', FILTER_SANITIZE_STRING);
        $data      = ! empty($xmlData) && ! empty($xmlData[0]) ? (object)$xmlData[0] : null;

        // Override choice_type from $_GET['choice_type'] value
        $choiceType = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'choice_type', FILTER_SANITIZE_STRING);
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
     * @throws \Mpdf\MpdfException
     * @since  1.0.0
     *
     */
    public static function create($xmlData)
    {
        $instance = new self(new Mpdf());

        try {
            return $instance->buildPdf($xmlData);
        } catch (\Exception $e) {
            echo 'Create Pdf Exception: ', $e->getMessage(), "\n";
        }
    }
}