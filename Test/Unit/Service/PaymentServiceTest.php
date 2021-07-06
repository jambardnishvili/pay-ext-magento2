<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Model\Data\OrderInterface;
use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentDataInterface;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\PaymentRequestBuilderInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Model\CMPayment;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Client\Model\CMPaymentUrl;
use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Client\Model\Request\PaymentCreate;
use CM\Payments\Client\Request\PaymentCreateRequest;
use CM\Payments\Model\ConfigProvider;
use CM\Payments\Model\Data\Payment as CMPaymentData;
use CM\Payments\Service\PaymentService;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\OrderRepository;

class PaymentServiceTest extends UnitTestCase
{
    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * @var OrderRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ApiClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiClientMock;

    /**
     * @var PaymentRequestBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentRequestBuilderMock;

    /**
     * @var CMPaymentDataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentDataFactoryMock;

    /**
     * @var CMPaymentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentFactoryMock;

    /**
     * @var CMPaymentUrlFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentUrlFactoryMock;

    /**
     * @var  CMPaymentRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentRepositoryMock;

    /**
     * @var  CMOrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmOrderRepositoryMock;

    public function testCreatePayment()
    {
        $this->apiClientMock->method('execute')->willReturn(
            [
                'order_key' => '0287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ]
        );

        $this->assertSame(
        //phpcs:ignore
            'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
            $this->paymentService->create('1')->getUrl()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiClientMock = $this->getMockBuilder(ApiClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentRequestBuilderMock = $this->getMockBuilder(PaymentRequestBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmPaymentDataFactoryMock = $this->getMockupFactory(
            CMPaymentData::class,
            CMPaymentDataInterface::class
        );

        $this->cmPaymentFactoryMock = $this->getMockupFactory(
            CMPayment::class
        );

        $this->cmPaymentUrlFactoryMock = $this->getMockupFactory(
            CMPaymentUrl::class
        );

        $this->cmPaymentRepositoryMock = $this->getMockBuilder(CMPaymentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmPaymentRepositoryMock = $this->getMockBuilder(CMPaymentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmOrderRepositoryMock = $this->getMockBuilder(CMOrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock->method('get')->willReturn($this->getOrderMock());
        $this->orderRepositoryMock->method('save');
        $this->cmOrderRepositoryMock->method('save');

        $this->paymentRequestBuilderMock->method('create')->willReturn(
            new PaymentCreateRequest(
                '0287A1617D93780EF28044B98438BF2F',
                new PaymentCreate(
                    MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_IDEAL],
                    [
                        'ideal_details' => ['issuer_id' => 'INGBNL2A']
                    ]
                )
            )
        );

        $this->paymentService = new PaymentService(
            $this->orderRepositoryMock,
            $this->apiClientMock,
            $this->paymentRequestBuilderMock,
            $this->cmPaymentDataFactoryMock,
            $this->cmPaymentFactoryMock,
            $this->cmPaymentUrlFactoryMock,
            $this->cmPaymentRepositoryMock,
            $this->cmOrderRepositoryMock
        );
    }

    /**
     * @return OrderInterface
     */
    private function getOrderMock()
    {
        $shippingAddressMock = $this->createConfiguredMock(
            OrderAddressInterface::class,
            [
                'getEmail' => static::ADDRESS_DATA['email_address'],
                'getCountryId' => static::ADDRESS_DATA['country_code']
            ]
        );

        $paymentMock = $this->getMockBuilder(OrderPaymentInterface::class)
            ->getMockForAbstractClass();
        $paymentMock->method('getAdditionalInformation')->willReturn([]);
        $paymentMock->method('setAdditionalInformation')->willReturnSelf();

        $orderMock = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->method('getIncrementId')->willReturn('000000001');
        $orderMock->method('getOrderCurrencyCode')->willReturn('EUR');
        $orderMock->method('getStoreId')->willReturn(1);
        $orderMock->method('getShippingAddress')->willReturn($shippingAddressMock);
        $orderMock->method('getGrandTotal')->willReturn(99.99);
        $orderMock->method('getPayment')->willReturn($paymentMock);

        return $orderMock;
    }
}
