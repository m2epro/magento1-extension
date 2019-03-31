<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Gallery
    extends Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract
{
    private $galleryId;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('rendererDescriptionGallery');
        // ---------------------------------------

        $this->setTemplate('M2ePro/renderer/description/gallery.phtml');
    }

    //########################################

    public function getGalleryId()
    {
        if (is_null($this->galleryId)) {
            $this->galleryId = substr(sha1(
                'gallery-'
                . $this->getData('index_number')
                . Mage::helper('M2ePro')->jsonEncode($this->getGalleryImages())
            ), 20);
        }
        return $this->galleryId;
    }

    //########################################

    public function isModeDefault()
    {
        return $this->getData('linked_mode') == Ess_M2ePro_Helper_Module_Renderer_Description::IMAGES_MODE_DEFAULT;
    }

    public function isModeGallery()
    {
        return $this->getData('linked_mode') == Ess_M2ePro_Helper_Module_Renderer_Description::IMAGES_MODE_GALLERY;
    }

    public function isLayoutColumnMode()
    {
        return $this->getData('layout') == Ess_M2ePro_Helper_Module_Renderer_Description::LAYOUT_MODE_COLUMN;
    }

    //########################################

    public function getGalleryImages()
    {
        return $this->getData('images') ? $this->getData('images') : array();
    }

    //########################################
}