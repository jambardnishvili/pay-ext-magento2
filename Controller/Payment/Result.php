<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Client\Model\OrderCreate;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Psr\Log\LoggerInterface;

class Result extends Action implements HttpGetActionInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Result constructor

     * @param Context $context
     * @param RequestInterface $request,
     * @param MessageManagerInterface $messageManager,
     * @param CheckoutSession $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        MessageManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        RedirectFactory $redirectFactory,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->orderManagement = $orderManagement;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $referenceOrderId = $this->request->getParam('order_reference');
        $status = $this->request->getParam('status');

        try {
            if (!$referenceOrderId || !$status) {
                $this->messageManager->addErrorMessage(__("The order reference is not valid!"));

                return $this->redirectToCheckout();
            }

            $orderIncrementId = $this->checkoutSession->getLastRealOrder()->getIncrementId();
            if (!$orderIncrementId || $referenceOrderId !== $orderIncrementId) {
                $this->messageManager->addErrorMessage(__("The order reference is not valid!"));

                return $this->redirectToCheckout();
            }

            if (in_array($status, [OrderCreate::STATUS_ERROR, OrderCreate::STATUS_CANCELLED])) {
                $this->orderManagement->cancel($this->checkoutSession->getLastRealOrder()->getId());
                $this->messageManager->addErrorMessage(__("The order was cancelled because of payment errors!"));

                return $this->redirectToCheckout();
            };

            return $this->redirectFactory->create()
                ->setPath('checkout/onepage/success');
        } catch (Exception $exception) {
            $this->logger->error($exception);
            $this->messageManager->addErrorMessage(__('Something went wrong with processing the order.'));

            return $this->redirectToCheckout();
        }
    }

    /**
     * @return Redirect
     */
    private function redirectToCheckout(): Redirect
    {
        $this->checkoutSession->restoreQuote();

        return $this->redirectFactory->create()
            ->setPath('checkout/cart');
    }
}