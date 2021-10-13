<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Description as Description;

class Ess_M2EPro_Model_Ebay_Template_Description_Builder
    extends Ess_M2ePro_Model_Ebay_Template_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $data = parent::prepareData();

        $defaultData = $this->getDefaultData();

        $data = Mage::helper('M2ePro')->arrayReplaceRecursive($defaultData, $data);

        if (isset($this->_rawData['title_mode'])) {
            $data['title_mode'] = (int)$this->_rawData['title_mode'];
        }

        if (isset($this->_rawData['title_template'])) {
            $data['title_template'] = $this->_rawData['title_template'];
        }

        if (isset($this->_rawData['subtitle_mode'])) {
            $data['subtitle_mode'] = (int)$this->_rawData['subtitle_mode'];
        }

        if (isset($this->_rawData['subtitle_template'])) {
            $data['subtitle_template'] = $this->_rawData['subtitle_template'];
        }

        if (isset($this->_rawData['description_mode'])) {
            $data['description_mode'] = (int)$this->_rawData['description_mode'];
        }

        if (isset($this->_rawData['description_template'])) {
            $data['description_template'] = $this->_rawData['description_template'];
        }

        if (isset($this->_rawData['condition_mode'])) {
            $data['condition_mode'] = (int)$this->_rawData['condition_mode'];
        }

        if (isset($this->_rawData['condition_value'])) {
            $data['condition_value'] = (int)$this->_rawData['condition_value'];
        }

        if (isset($this->_rawData['condition_attribute'])) {
            $data['condition_attribute'] = $this->_rawData['condition_attribute'];
        }

        if (isset($this->_rawData['condition_note_mode'])) {
            $data['condition_note_mode'] = (int)$this->_rawData['condition_note_mode'];
        }

        if (isset($this->_rawData['condition_note_template'])) {
            $data['condition_note_template'] = $this->_rawData['condition_note_template'];
        }

        if (isset($this->_rawData['product_details'])) {
            $data['product_details'] = $this->_rawData['product_details'];

            if (is_array($data['product_details'])) {
                $data['product_details'] = Mage::helper('M2ePro')->jsonEncode($data['product_details']);
            }
        }

        if (isset($this->_rawData['editor_type'])) {
            $data['editor_type'] = (int)$this->_rawData['editor_type'];
        }

        if (isset($this->_rawData['cut_long_titles'])) {
            $data['cut_long_titles'] = (int)$this->_rawData['cut_long_titles'];
        }

        if (isset($this->_rawData['hit_counter'])) {
            $data['hit_counter'] = $this->_rawData['hit_counter'];
        }

        if (isset($this->_rawData['enhancement'])) {
            $data['enhancement'] = $this->_rawData['enhancement'];

            if (is_array($data['enhancement'])) {
                $data['enhancement'] = implode(',', $this->_rawData['enhancement']);
            }
        }

        if (isset($this->_rawData['gallery_type'])) {
            $data['gallery_type'] = (int)$this->_rawData['gallery_type'];
        }

        if (isset($this->_rawData['image_main_mode'])) {
            $data['image_main_mode'] = (int)$this->_rawData['image_main_mode'];
        }

        if (isset($this->_rawData['image_main_attribute'])) {
            $data['image_main_attribute'] = $this->_rawData['image_main_attribute'];
        }

        if (isset($this->_rawData['gallery_images_mode'])) {
            $data['gallery_images_mode'] = (int)$this->_rawData['gallery_images_mode'];
        }

        if (isset($this->_rawData['gallery_images_limit'])) {
            $data['gallery_images_limit'] = (int)$this->_rawData['gallery_images_limit'];
        }

        if (isset($this->_rawData['gallery_images_attribute'])) {
            $data['gallery_images_attribute'] = $this->_rawData['gallery_images_attribute'];
        }

        if (isset($this->_rawData['variation_images_mode'])) {
            $data['variation_images_mode'] = (int)$this->_rawData['variation_images_mode'];
        }

        if (isset($this->_rawData['variation_images_limit'])) {
            $data['variation_images_limit'] = (int)$this->_rawData['variation_images_limit'];
        }

        if (isset($this->_rawData['variation_images_attribute'])) {
            $data['variation_images_attribute'] = $this->_rawData['variation_images_attribute'];
        }

        if (isset($this->_rawData['reserve_price_custom_attribute'])) {
            $data['reserve_price_custom_attribute'] = $this->_rawData['reserve_price_custom_attribute'];
        }

        if (isset($this->_rawData['default_image_url'])) {
            $data['default_image_url'] = $this->_rawData['default_image_url'];
        }

        if (isset($this->_rawData['variation_configurable_images'])) {
            $data['variation_configurable_images'] = $this->_rawData['variation_configurable_images'];

            if (is_array($data['variation_configurable_images'])) {
                $data['variation_configurable_images'] = Mage::helper('M2ePro')->jsonEncode(
                    $data['variation_configurable_images']
                );
            }
        }

        if (isset($this->_rawData['use_supersize_images'])) {
            $data['use_supersize_images'] = (int)$this->_rawData['use_supersize_images'];
        }

        if (isset($this->_rawData['watermark_mode'])) {
            $data['watermark_mode'] = (int)$this->_rawData['watermark_mode'];
        }

        // ---------------------------------------

        $watermarkSettings = array();
        $hashChange = false;

        if (isset($this->_rawData['watermark_settings']['position'])) {
            $watermarkSettings['position'] = (int)$this->_rawData['watermark_settings']['position'];

            if (isset($this->_rawData['old_watermark_settings']) &&
                $this->_rawData['watermark_settings']['position'] !==
                $this->_rawData['old_watermark_settings']['position']) {
                $hashChange = true;
            }
        }

        if (isset($this->_rawData['watermark_settings']['scale'])) {
            $watermarkSettings['scale'] = (int)$this->_rawData['watermark_settings']['scale'];

            if (isset($this->_rawData['old_watermark_settings']) &&
                $this->_rawData['watermark_settings']['scale'] !== $this->_rawData['old_watermark_settings']['scale']) {
                $hashChange = true;
            }
        }

        if (isset($this->_rawData['watermark_settings']['transparent'])) {
            $watermarkSettings['transparent'] = (int)$this->_rawData['watermark_settings']['transparent'];

            if (isset($this->_rawData['old_watermark_settings']) &&
                $this->_rawData['watermark_settings']['transparent'] !==
                $this->_rawData['old_watermark_settings']['transparent']) {
                $hashChange = true;
            }
        }

        // ---------------------------------------

        if (!empty($_FILES['watermark_image']['tmp_name'])) {
            $hashChange = true;

            $data['watermark_image'] = base64_encode(file_get_contents($_FILES['watermark_image']['tmp_name']));

            if (isset($data['id'])) {
                $varDir = new Ess_M2ePro_Model_VariablesDir(
                    array('child_folder' => 'ebay/template/description/watermarks')
                );

                $watermarkPath = $varDir->getPath().(int)$data['id'].'.png';
                if (is_file($watermarkPath)) {
                    @unlink($watermarkPath);
                }
            }
        } elseif (!empty($this->_rawData['old_watermark_image']) && isset($data['id'])) {
            $data['watermark_image'] = $this->_rawData['old_watermark_image'];
        }

        // ---------------------------------------

        if ($hashChange) {
            $watermarkSettings['hashes']['previous'] = $this->_rawData['old_watermark_settings']['hashes']['current'];
            $watermarkSettings['hashes']['current'] = substr(sha1(microtime()), 0, 5);
        } else {
            $watermarkSettings['hashes']['previous'] = $this->_rawData['old_watermark_settings']['hashes']['previous'];
            $watermarkSettings['hashes']['current'] = $this->_rawData['old_watermark_settings']['hashes']['current'];
        }

        $data['watermark_settings'] = Mage::helper('M2ePro')->jsonEncode($watermarkSettings);

        // ---------------------------------------

        return $data;
    }

    //########################################

    public function getDefaultData()
    {
        return array(

            'title_mode' => Description::TITLE_MODE_PRODUCT,
            'title_template' => '',

            'subtitle_mode' => Description::SUBTITLE_MODE_NONE,
            'subtitle_template' => '',

            'description_mode' => '',
            'description_template' => '',

            'condition_mode' => Description::CONDITION_MODE_EBAY,
            'condition_value' => Description::CONDITION_EBAY_NEW,
            'condition_attribute' => '',

            'condition_note_mode' => Description::CONDITION_NOTE_MODE_NONE,
            'condition_note_template' => '',

            'product_details' => Mage::helper('M2ePro')->jsonEncode(
                array(
                    'isbn'  => array('mode' => Description::PRODUCT_DETAILS_MODE_NONE, 'attribute' => ''),
                    'epid'  => array('mode' => Description::PRODUCT_DETAILS_MODE_NONE, 'attribute' => ''),
                    'upc'   => array('mode' => Description::PRODUCT_DETAILS_MODE_NONE, 'attribute' => ''),
                    'ean'   => array('mode' => Description::PRODUCT_DETAILS_MODE_NONE, 'attribute' => ''),
                    'brand' => array('mode' => Description::PRODUCT_DETAILS_MODE_NONE, 'attribute' => ''),
                    'mpn'   => array('mode' => Description::PRODUCT_DETAILS_MODE_DOES_NOT_APPLY, 'attribute' => ''),
                    'include_ebay_details' => 1,
                    'include_image'   => 1,
                )
            ),

            'editor_type' => Description::EDITOR_TYPE_SIMPLE,
            'cut_long_titles' => Description::CUT_LONG_TITLE_ENABLED,
            'hit_counter' => Description::HIT_COUNTER_NONE,

            'enhancement' => '',
            'gallery_type' => Description::GALLERY_TYPE_EMPTY,

            'image_main_mode' => Description::IMAGE_MAIN_MODE_PRODUCT,
            'image_main_attribute' => '',
            'gallery_images_mode' => Description::GALLERY_IMAGES_MODE_NONE,
            'gallery_images_limit' => 0,
            'gallery_images_attribute' => '',
            'variation_images_mode' => Description::VARIATION_IMAGES_MODE_PRODUCT,
            'variation_images_limit' => 1,
            'variation_images_attribute' => '',
            'default_image_url' => '',

            'variation_configurable_images' => Mage::helper('M2ePro')->jsonEncode(array()),
            'use_supersize_images' => Description::USE_SUPERSIZE_IMAGES_NO,

            'watermark_mode' => Description::WATERMARK_MODE_NO,

            'watermark_settings' => Mage::helper('M2ePro')->jsonEncode(
                array(
                    'position' => Description::WATERMARK_POSITION_TOP,
                    'scale' => Description::WATERMARK_SCALE_MODE_NONE,
                    'transparent' => Description::WATERMARK_TRANSPARENT_MODE_NO,

                    'hashes' => array(
                        'current'  => '',
                        'previous' => '',
                    )
                )
            ),

            'watermark_image' => null
        );
    }

    //########################################
}
