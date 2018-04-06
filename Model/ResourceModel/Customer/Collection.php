<?php

namespace LoganStellway\Social\Model\ResourceModel\Customer;

/**
 * Dependencies
 */
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Customer Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define Model and Resource Model
     */
    protected function _construct()
    {
        $this->_init('LoganStellway\Social\Model\Customer', 'LoganStellway\Social\Model\ResourceModel\Customer');
    }
}
