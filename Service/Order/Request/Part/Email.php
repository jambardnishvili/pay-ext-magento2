<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Order\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Sales\Api\Data\OrderInterface;

class Email implements RequestPartByOrderInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderInterface $order, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setEmail($order->getShippingAddress()->getEmail());

        return $orderCreate;
    }
}
