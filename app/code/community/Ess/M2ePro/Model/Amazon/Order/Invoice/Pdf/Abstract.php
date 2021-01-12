<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Order_Invoice_Pdf_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract
{
    protected $bottomMinY = 25;

    /** @var Ess_M2ePro_Model_Order */
    protected $order;

    /** @var Ess_M2ePro_Model_Amazon_Order_Invoice */
    protected $invoice;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Order_Invoice $invoice
     */
    public function setInvocie($invoice)
    {
        $this->invoice = $invoice;
    }

    //########################################

    public function getDocumentTotal()
    {
        $documentData = $this->invoice->getSettings('document_data');

        return array_sum(array(
            $this->sumByField($documentData['items'], 'item-vat-incl-amount'),
            $this->sumByField($documentData['items'], 'item-promo-vat-incl-amount'),
            $this->sumByField($documentData['items'], 'shipping-vat-incl-amount'),
            $this->sumByField($documentData['items'], 'shipping-promo-vat-incl-amount'),
            $this->sumByField($documentData['items'], 'gift-wrap-vat-incl-amount'),
            $this->sumByField($documentData['items'], 'gift-promo-vat-incl-amount')
        ));
    }

    public function getDocumentExclVatTotal()
    {
        $documentData = $this->invoice->getSettings('document_data');

        return array_sum(array(
            $this->sumByField($documentData['items'], 'item-vat-excl-amount'),
            $this->sumByField($documentData['items'], 'item-promo-vat-excl-amount'),
            $this->sumByField($documentData['items'], 'shipping-vat-excl-amount'),
            $this->sumByField($documentData['items'], 'shipping-promo-vat-excl-amount'),
            $this->sumByField($documentData['items'], 'gift-wrap-vat-excl-amount'),
            $this->sumByField($documentData['items'], 'gift-promo-vat-excl-amount')
        ));
    }

    public function getDocumentVatTotal()
    {
        $documentData = $this->invoice->getSettings('document_data');

        return array_sum(array(
            $this->sumByField($documentData['items'], 'item-vat-amount'),
            $this->sumByField($documentData['items'], 'item-promo-vat-amount'),
            $this->sumByField($documentData['items'], 'shipping-vat-amount'),
            $this->sumByField($documentData['items'], 'shipping-promo-vat-amount'),
            $this->sumByField($documentData['items'], 'gift-wrap-vat-amount'),
            $this->sumByField($documentData['items'], 'gift-promo-vat-amount'),
        ));
    }

    //########################################

    protected function getFormatedAddress($data, $delimiter = ', ')
    {
        return implode($delimiter, array_filter($data));
    }

    protected function getFormatedPrice($value)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $this->invoice->getSetting('document_data', 'currency'),
            $value
        );
    }

    protected function getFormatedVAT($value)
    {
        return sprintf('%s%%', $value * 100);
    }

    protected function sumByField($data, $field)
    {
        $value = 0;
        foreach ($data as $item) {
            $value += $item[$field];
        }

        return $value;
    }

    //########################################

    protected function prepareColumnData(array $data, $strMaxLength)
    {
        $prepared = array();
        foreach ($data as $value) {
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, $strMaxLength, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $prepared[] = strip_tags(ltrim($part));
                }
            }
        }

        return $prepared;
    }

    protected function drawLabelValueData($page, array $data)
    {
        $labelText = $data['label'] . ': ';
        $valueText = $data['value'];

        $x = $data['x'];
        $y = empty($data['y']) ? $this->y : $data['y'];

        $textAlign = empty($data['align']) ? 'left' : $data['align'];
        switch ($textAlign) {
            case 'right':
                $this->_setFontBold($page, $data['font_size']);
                $x -= $this->widthForStringUsingFontSize($labelText, $page->getFont(), $page->getFontSize());

                $this->_setFontRegular($page, $data['font_size']);
                $x -= $this->widthForStringUsingFontSize($valueText, $page->getFont(), $page->getFontSize());
                break;
        }

        $this->_setFontBold($page, $data['font_size']);
        $page->drawText($labelText, $x, $y, 'UTF-8');

        $strWidth = $this->widthForStringUsingFontSize($labelText, $page->getFont(), $page->getFontSize());

        $this->_setFontRegular($page, $data['font_size']);
        $page->drawText($data['value'], $x + $strWidth, $y, 'UTF-8');

        if (empty($data['y'])) {
            $this->y -= $data['line_height'];
        }
    }

    //########################################

    protected function drawTitle($page, $title)
    {
        $this->drawLineBlocks($page, array(
            array(
                'lines'  => array(
                    array(
                        array(
                            'text'      => $title,
                            'feed'      => 575,
                            'font'      => 'bold',
                            'font_size' => 10,
                            'align'     => 'right'
                        )
                    ),
                ),
                'height' => 10
            )
        ));
    }

    protected function drawInfoBlock(Zend_Pdf_Page $page, $lablesTitle)
    {
        $x = 360;

        $color = new Zend_Pdf_Color_Html('#F5F6FA');
        $page->setFillColor($color);
        $page->setLineColor($color);
        $page->drawRectangle($x, $this->y, $x + 215, $this->y - 58);

        $page->setLineColor(new Zend_Pdf_Color_Html('#0B151E'));
        $page->setLineWidth(0.7);
        $page->drawRectangle($x + 5, $this->y - 5, $x + 210, $this->y - 53);

        $page->setFillColor(new Zend_Pdf_Color_Html('#0B151E'));

        $this->drawLabelValueData($page, array(
            'label' => Mage::helper('M2ePro')->__($lablesTitle . ' date'),
            'value' => Mage::helper('core')->formatDate($this->invoice->getData('create_date'), 'medium', false),
            'x' => $x + 20,
            'y' => $this->y - 25,
            'font_size' => 9,
        ));

        $this->drawLabelValueData($page, array(
            'label' => Mage::helper('M2ePro')->__($lablesTitle . ' #'),
            'value' => $this->invoice->getDocumentNumber(),
            'x' => $x + 20,
            'y' => $this->y - 38,
            'font_size' => 9,
        ));

        $this->y -= 90;
    }

    protected function drawAdresses($page)
    {
        $storeData = Mage::getStoreConfig('general/store_information', $this->order->getStoreId());
        $documentData = $this->invoice->getSettings('document_data');

        $page->setFillColor(new Zend_Pdf_Color_Html('#0E0621'));
        $this->drawLineBlocks($page, array(
            array(
                'lines'  => array(
                    array(
                        array(
                            'text'      => Mage::helper('M2ePro')->__('Billing address'),
                            'feed'      => 25,
                            'font'      => 'bold',
                            'font_size' => 12
                        ),
                        array(
                            'text'      => Mage::helper('M2ePro')->__('Delivery address'),
                            'feed'      => 205,
                            'font'      => 'bold',
                            'font_size' => 12
                        ),
                        array(
                            'text'      => Mage::helper('M2ePro')->__('Sold by'),
                            'feed'      => 395,
                            'font'      => 'bold',
                            'font_size' => 12
                        )
                    )
                ),
                'height' => 16
            )
        ));

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, array(
            array(
                'lines'  => array(
                    array(
                        array(
                            'text'      => $this->prepareColumnData(array(
                                $documentData['billing-address']['billing-name'],
                                $documentData['buyer-company-name'],
                                $this->getFormatedAddress(array(
                                    $documentData['billing-address']['bill-address-1'],
                                    $documentData['billing-address']['bill-address-2'],
                                    $documentData['billing-address']['bill-address-3'],
                                    $documentData['billing-address']['bill-city']
                                )),
                                $this->getFormatedAddress(array(
                                    $documentData['billing-address']['bill-state'],
                                    $documentData['billing-address']['bill-postal-code']
                                )),
                                $documentData['billing-address']['bill-country'],
                                $documentData['billing-address']['billing-phone-number'],
                                $documentData['buyer-vat-number'],
                            ), 35),
                            'feed'      => 25,
                            'font_size' => 8
                        ),
                        array(
                            'text'      => $this->prepareColumnData(array(
                                $documentData['shipping-address']['recipient-name'],
                                $this->getFormatedAddress(array(
                                    $documentData['shipping-address']['ship-address-1'],
                                    $documentData['shipping-address']['ship-address-2'],
                                    $documentData['shipping-address']['ship-address-3'],
                                    $documentData['shipping-address']['ship-city']
                                )),
                                $this->getFormatedAddress(array(
                                    $documentData['shipping-address']['ship-state'],
                                    $documentData['shipping-address']['ship-postal-code']
                                )),
                                $documentData['shipping-address']['ship-country'],
                                $documentData['shipping-address']['ship-phone-number'],
                            ), 35),
                            'feed'      => 205,
                            'font_size' => 8
                        ),
                        array(
                            'text'      => $this->prepareColumnData(array(
                                $storeData['name'],
                                $storeData['address'],
                                Mage::helper('M2ePro')->__('VAT Number') . ': ' . $documentData['seller-vat-number']
                            ), 35),
                            'feed'      => 395,
                            'font_size' => 8
                        )
                    )
                ),
                'height' => 12
            )
        ));
        $this->y -= 20;
    }

    protected function drawOrderInfo($page)
    {
        $documentData = $this->invoice->getSettings('document_data');

        $page->setFillColor(new Zend_Pdf_Color_Html('#0E0621'));
        $this->drawLineBlocks($page, array(
            array(
                'lines'  => array(
                    array(
                        array(
                            'text'      => Mage::helper('M2ePro')->__('Order Information'),
                            'feed'      => 25,
                            'font'      => 'bold',
                            'font_size' => 12
                        )
                    )
                ),
                'height' => 16
            )
        ));

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, array(
            array(
                'lines'  => array(
                    array(
                        array(
                            'text'      => $this->prepareColumnData(array(
                                Mage::helper('M2ePro')->__('Order date') . ': ' .
                                Mage::helper('core')->formatDate($documentData['order-date'], 'medium', false),
                                Mage::helper('M2ePro')->__('Order #') . ': ' . $documentData['order-id']
                            ), 35),
                            'feed'      => 25,
                            'font_size' => 8
                        )
                    )
                ),
                'height' => 12
            )
        ));
        $this->y -= 20;
    }

    //########################################

    protected function drawItemsHeader(Zend_Pdf_Page $page)
    {
        $color = new Zend_Pdf_Color_Html('#F5F6FA');
        $page->setFillColor($color);
        $page->setLineColor($color);
        $page->drawRectangle(25, $this->y, 575, 25);

        $color = new Zend_Pdf_Color_Html('#0B151E');
        $page->setFillColor($color);
        $page->setLineColor($color);
        $page->drawRectangle(25, $this->y, 575, $this->y - 30);
        $this->y -= 12;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('M2ePro')->__('Description'),
            'feed' => 30,
            'font' => 'bold',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('Qty'),
            'feed'  => 240,
            'align' => 'right',
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('Unit price'),
            'feed'  => 320,
            'align' => 'right',
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('VAT rate'),
            'feed'  => 400,
            'align' => 'right',
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('Unit price'),
            'feed'  => 480,
            'align' => 'right',
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('Item subtotal'),
            'feed'  => 570,
            'align' => 'right',
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[1][] = array(
            'text'  => Mage::helper('M2ePro')->__('(excl. VAT)'),
            'feed'  => 320,
            'align' => 'right',
            'font_size' => 8
        );

        $lines[1][] = array(
            'text'  => Mage::helper('M2ePro')->__('(incl. VAT)'),
            'feed'  => 480,
            'align' => 'right',
            'font_size' => 8
        );

        $lines[1][] = array(
            'text'  => Mage::helper('M2ePro')->__('(incl. VAT)'),
            'feed'  => 570,
            'align' => 'right',
            'font_size' => 8
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $this->drawLineBlocks($page, array($lineBlock));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 8;

        return $page;
    }

    protected function drawItem($item, $page)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 8;

        $lines[0] = array(
            array(
                'text' => array_merge(
                    Mage::helper('core/string')->str_split($item['product-name'], 45, true, true),
                    array(
                        $item['asin']
                    )
                ),
                'feed' => 30,
                'font_size' => 8
            )
        );

        $lines[0][] = array(
            'text'  => $item['quantity-purchased'],
            'feed'  => 240,
            'align' => 'right',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => $this->getFormatedPrice($item['item-vat-excl-amount'] / $item['quantity-purchased']),
            'feed'  => 320,
            'align' => 'right',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => $this->getFormatedVAT($item['item-vat-rate']),
            'feed'  => 400,
            'align' => 'right',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => $this->getFormatedPrice($item['item-vat-incl-amount'] / $item['quantity-purchased']),
            'feed'  => 480,
            'align' => 'right',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => $this->getFormatedPrice($item['item-vat-incl-amount']),
            'feed'  => 570,
            'align' => 'right',
            'font_size' => 8
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $this->drawLineBlocks($page, array($lineBlock), array('table_header_method' => 'drawItemsHeader'));
        $this->y -= 4;

        return $page;
    }

    protected function drawItemsFooter(Zend_Pdf_Page $page)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $this->y, 575, 25);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
    }


    protected function drawAdditionalInfo($page)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $documentData = $this->invoice->getSettings('document_data');

        $this->drawLabelValueData($page, array(
            'label' => Mage::helper('M2ePro')->__('Shipping Charge'),
            'value' => $this->getFormatedPrice($this->sumByField($documentData['items'], 'shipping-vat-incl-amount')),
            'x' => 570,
            'align' => 'right',
            'font_size' => 8,
            'line_height' => 12,
        ));

        $this->drawLabelValueData($page, array(
            'label' => Mage::helper('M2ePro')->__('Gift Wrap'),
            'value' => $this->getFormatedPrice($this->sumByField($documentData['items'], 'gift-wrap-vat-incl-amount')),
            'x' => 570,
            'align' => 'right',
            'font_size' => 8,
            'line_height' => 12,
        ));

        $this->drawLabelValueData($page, array(
            'label' => Mage::helper('M2ePro')->__('Promotions'),
            'value' => $this->getFormatedPrice(array_sum(array(
                $this->sumByField($documentData['items'], 'item-promo-vat-incl-amount'),
                $this->sumByField($documentData['items'], 'gift-promo-vat-incl-amount'),
                $this->sumByField($documentData['items'], 'shipping-promo-vat-incl-amount')
            ))),
            'x' => 570,
            'align' => 'right',
            'font_size' => 8,
            'line_height' => 12,
        ));

        $this->y -= 8;

        return $page;
    }

    protected function drawSubtotalHeader(Zend_Pdf_Page $page)
    {
        $color = new Zend_Pdf_Color_Html('#F5F6FA');
        $page->setFillColor($color);
        $page->setLineColor($color);
        $page->drawRectangle(290, $this->y, 575, 25);

        $color = new Zend_Pdf_Color_Html('#0B151E');
        $page->setFillColor($color);
        $page->setLineColor($color);
        $page->drawRectangle(290, $this->y, 575, $this->y - 30);
        $this->y -= 12;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('Vat rate'),
            'feed'  => 295,
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('Items subtotal'),
            'feed'  => 470,
            'align' => 'right',
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => Mage::helper('M2ePro')->__('VAT subtotal'),
            'feed'  => 570,
            'align' => 'right',
            'font'  => 'bold',
            'font_size' => 8
        );

        $lines[1][] = array(
            'text'  => Mage::helper('M2ePro')->__('(excl. VAT)'),
            'feed'  => 470,
            'align' => 'right',
            'font_size' => 8
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $this->drawLineBlocks($page, array($lineBlock));
        $this->y -= 8;

        return $page;
    }

    protected function drawSubtotalItem(Zend_Pdf_Page $page, $item)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 8;

        $lines[0][] = array(
            'text'  => $this->getFormatedVAT($item['item-vat-rate']),
            'feed'  => 295,
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => $this->getFormatedPrice(array_sum(array(
                $item['item-vat-excl-amount'],
                $item['item-promo-vat-excl-amount'],
                $item['shipping-vat-excl-amount'],
                $item['shipping-promo-vat-excl-amount'],
                $item['gift-wrap-vat-excl-amount'],
                $item['gift-promo-vat-excl-amount']
            ))),
            'feed'  => 470,
            'align' => 'right',
            'font_size' => 8
        );

        $lines[0][] = array(
            'text'  => $this->getFormatedPrice(array_sum(array(
                $item['item-vat-amount'],
                $item['item-promo-vat-amount'],
                $item['shipping-vat-amount'],
                $item['shipping-promo-vat-amount'],
                $item['gift-wrap-vat-amount'],
                $item['gift-promo-vat-amount']
            ))),
            'feed'  => 570,
            'align' => 'right',
            'font_size' => 8
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $this->drawLineBlocks($page, array($lineBlock), array('table_header_method' => 'drawSubtotalHeader'));
        $this->y -= 4;

        return $page;
    }

    protected function drawSubtotalFooter(Zend_Pdf_Page $page)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(290, $this->y, 575, 25);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
    }

    //########################################

    protected function drawLayout($page)
    {
        $height = 842;
        $with = 600;

        $color = new Zend_Pdf_Color_Html('#0C151E');
        $page->setFillColor($color);
        $page->setLineColor($color);
        $page->drawRectangle(0, $height, $with, $height - 7);
        $page->drawRectangle(0, 0, $with, 7);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        $this->drawCitation($page);
    }

    protected function drawCitation($page)
    {
        $documentData = $this->invoice->getSettings('document_data');
        $marketplaceCode = strtolower($this->order->getMarketplace()->getCode());

        if (empty($documentData['citation-' . $marketplaceCode])) {
            $marketplaceCode = strtolower($documentData['marketplace-id']);
        }

        if (!empty($documentData['citation-' . $marketplaceCode])) {
            $this->_setFontRegular($page, 8);

            $valueSplit = Mage::helper('core/string')->str_split(
                $documentData['citation-' . $marketplaceCode], 90, true, true
            );

            $y = 10 + count($valueSplit) * 8;
            $this->bottomMinY = ($y > $this->bottomMinY) ? $y : $this->bottomMinY;
            foreach ($valueSplit as $part) {
                $textWidth = $this->widthForStringUsingFontSize($part, $page->getFont(), $page->getFontSize());
                $page->drawText($part, 300 - $textWidth/2, $y, 'UTF-8');
                $y -= 8;
            }
        }

        return $page;
    }

    //########################################

    public function newPage(array $settings = array())
    {
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;

        $this->drawLayout($page);
        $this->y = 815;

        if (!empty($settings['table_header_method'])) {
            $method = $settings['table_header_method'];
            $this->$method($page);
        }

        return $page;
    }

    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $top = $this->y; //top border of the page
                $widthLimit = 220; //half of the page width
                $heightLimit = 220; //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 25;
            }
        }
    }

    protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(
            Mage::getDesign()->getSkinBaseDir(
                array(
                    '_area' => 'adminhtml',
                    '_package' => 'default',
                    '_theme' => 'default'
                )
            ) . '/M2ePro/fonts/WorkSans/WorkSans-Regular.ttf'
        );
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(
            Mage::getDesign()->getSkinBaseDir(
                array(
                    '_area' => 'adminhtml',
                    '_package' => 'default',
                    '_theme' => 'default'
                )
            ) . '/M2ePro/fonts/WorkSans/WorkSans-Bold.ttf'
        );
        $object->setFont($font, $size);
        return $font;
    }

    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines  = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < $this->bottomMinY) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacing < $this->bottomMinY) {
                            $page = $this->newPage($pageSettings);
                        }

                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                }
                                else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y-$top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }

    //########################################
}
