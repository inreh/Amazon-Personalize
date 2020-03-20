<?php
namespace CustomerParadigm\AmazonPersonalize\Model\ResourceModel\Data\Interaction\PurchaseEvent;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_eventPrefix = 'customerparadigm_amazonpersonalize_purchaseevent_collection';
    protected $_eventObject = 'amazonpersonalize_purchaseevent_collection';

    protected function _construct()
    {
        $this->_init('CustomerParadigm\AmazonPersonalize\Model\Data\Interaction\PurchaseEvent',
            'CustomerParadigm\AmazonPersonalize\Model\ResourceModel\Data\Interaction\PurchaseEvent');
    }

    protected function _initSelect()
    {
        $this->getSelect()
            ->from(['main_table' => $this->getMainTable()],
                [
                    'user_id' => 'sales_order.customer_id',
                    'item_id' => 'main_table.product_id',
                    'item_type' => 'main_table.product_type',
                    'timestamp' => 'UNIX_TIMESTAMP(main_table.updated_at)',
                ]
            )
            ->join('sales_order',
                'main_table.order_id = sales_order.entity_id', []);;
        return $this;
    }
}
