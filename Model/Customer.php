<?php

namespace LoganStellway\Social\Model;

/**
 * Customer
 */
class Customer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Dependency Injection
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('LoganStellway\Social\Model\ResourceModel\Customer');
    }

    /**
     * Get social customer entity by ID and Type
     * @param int $userId
     * @param string $type
     * @return \LoganStellway\Social\Model\Customer
     */
    public function loadByIdType($userId, $type)
    {
        return $this->getCollection()->addFieldToFilter(
            'user_id', $userId
        )->addFieldToFilter(
            'type', $type
        )->getFirstItem();
    }

    /**
     * Get customer model by customer social account
     * 
     * @param int|string $id
     * @param string $type
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerBySocial($userId = null, $type = null)
    {
        $socialCustomer = $this->getId() ? $this : $this->loadByIdType($userId, $type);

        if ($socialCustomer->getId()) {
            return $this->_customerFactory->create()->load(
                $socialCustomer->getCustomerId()
            );
        }

        return null;
    }

    /**
     * Get customer model by email
     * 
     * @param string $email
     * @param string $email
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerByEmail($email, $websiteId = null)
    {
        return $this->_customerFactory->create()->setWebsiteId(
            $websiteId ?: $this->_storeManager->getWebsite()->getId()
        )->loadByEmail($email);
    }

    /**
     * Create customer social account
     * 
     * @param array $data
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Customer\Model\Customer
     */
    public function createCustomer(\Magento\Framework\DataObject $data, $store = null)
    {
        if (! $store) {
            $store = $this->_storeManager->getStore();
        }

        $customer = $this->_customerFactory->create()
            ->setEmail($data->getEmail())
            ->setFirstname($data->getFirstname())
            ->setLastname($data->getLastname())
            ->setDob($data->getDob())
            ->setStore($store);

        try {
            $customer->save();

            if ($customer->getId()) {
                $this->setData([
                    'customer_id' => $customer->getId(),
                    'user_id' => $data['user_id'],
                    'type' => $data['service_name'],
                ])->save();
            }
        } catch (\Exception $e) {
            // $customer->delete()->unsetData();
            throw $e;
        }

        return $customer;
    }
}
