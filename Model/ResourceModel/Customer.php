<?php

namespace LoganStellway\Social\Model\ResourceModel;

/**
 * Dependencies
 */
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Customer
 */
class Customer extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('loganstellway_social_customer', 'entity_id');
    }
}
