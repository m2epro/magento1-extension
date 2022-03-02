<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Issue
{
    /** @var string|null */
    private $message;

    /** @var array|string|null */
    private $metadata;

    public function __construct($args)
    {
        $this->message = $args['message'];
        $this->metadata = $args['metadata'];
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array|string|null
     */
    public function getMetadata()
    {
        if (empty($this->metadata)) {
            return  '';
        }

        if (is_array($this->metadata)) {
            if (is_int(key($this->metadata))) {
                return '<pre>' . implode(PHP_EOL, $this->metadata) .' </pre>';
            }

            return '<pre>' . str_replace('Array', '', print_r($this->metadata, true)) .'</pre>';
        }

        return $this->metadata;
    }
}
