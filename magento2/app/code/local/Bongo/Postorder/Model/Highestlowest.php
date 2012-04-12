<?php
/**
 * Used in creating options for Highest|Lowest config value selection
 *
 */
class Bongo_Postorder_Model_Highestlowest
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Highest')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Lowest')),
        );
    }

}
