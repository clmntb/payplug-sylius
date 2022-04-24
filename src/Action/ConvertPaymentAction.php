<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Bridge\Spl\ArrayObject;

final class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $customer = $order->getCustomer();
        $billingAddress = $order->getBillingAddress();
        $shippingddress = $order->getShippingAddress();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['amount'] = $payment->getAmount();
        $details['currency'] = $payment->getCurrencyCode();
        $details['billing'] = array(
            'title'        => null,
            'first_name'   => $billingAddress->getFirstName(),
            'last_name'    => $billingAddress->getLastName(),
            'email'        => $customer->getEmail(),
            'address1'     => $billingAddress->getStreet(),
            'postcode'     => $billingAddress->getPostCode(),
            'city'         => $billingAddress->getCity(),
            'country'      => $billingAddress->getCountryCode(),
            'language'     => 'fr'
        );
        $details['shipping'] = array(
            'title'        => null,
            'first_name'   => $shippingddress->getFirstName(),
            'last_name'    => $shippingddress->getLastName(),
            'email'        => $customer->getEmail(),
            'address1'     => $shippingddress->getStreet(),
            'postcode'     => $shippingddress->getPostCode(),
            'city'         => $shippingddress->getCity(),
            'country'      => $shippingddress->getCountryCode(),
            'language'     => 'fr',
            "delivery_type" => "BILLING"
        );
        $details['metadata'] = [
            'customer_id' => $customer->getId(),
            'order_number' => $order->getNumber(),
        ];

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
