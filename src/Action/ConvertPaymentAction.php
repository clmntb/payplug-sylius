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
        $gatewayConfig = $payment->getMethod()->getGatewayConfig()->getConfig();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $customer = $order->getCustomer();
        $billingAddress = $order->getBillingAddress();
        $shippingddress = $order->getShippingAddress();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if (isset($gatewayConfig['oneyPayment']) and $gatewayConfig['oneyPayment']) {
            $details['authorized_amount'] = $payment->getAmount();
            $details['auto_capture'] = true;
            if ($gatewayConfig['oneyFees']) {
                $details['payment_method'] = 'oney_x3_with_fees';
            } else {
                $details['payment_method'] = 'oney_x3_without_fees';
            }
        } else {
            $details['amount'] = $payment->getAmount();
        }
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
            'mobile_phone_number' => $this->canonicalizePhoneNumber($billingAddress->getPhoneNumber()),
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
            'company_name' => 'N/A',
            'mobile_phone_number' => $this->canonicalizePhoneNumber($shippingddress->getPhoneNumber()),
            'language'     => 'fr',
            "delivery_type" => "BILLING"
        );
        $details['metadata'] = [
            'customer_id' => $customer->getId(),
            'order_number' => $order->getNumber(),
        ];
        if (isset($gatewayConfig['oneyPayment']) and $gatewayConfig['oneyPayment']) {
            $cart = array();

            foreach ($order->getItems() as $order_item) {
                $name = $order_item->getVariant()->getTranslation()->getName();
                if ($name === null) {
                    $name = $order_item->getVariant()->getProduct()->getTranslation()->getName();
                }
                $date_modifier = '+5 days';
                if (!empty($gatewayConfig['oneyDateModifier'])) {
                    $date_modifier = $gatewayConfig['oneyDateModifier'];
                }
                $cart_item = array(
                    'brand' => $gatewayConfig['oneyBrand'],
                    'delivery_label' => $gatewayConfig['oneyShippingLabel'],
                    'delivery_type' => "edelivery",
                    'merchant_item_id' => $order_item->getVariant()->getCode(),
                    'name' => $name,
                    'price' => $order_item->getUnitPrice(),
                    'total_amount' => $order_item->getTotal(),
                    'quantity' => $order_item->getQuantity(),
                    'expected_delivery_date' => Date(
                        'Y-m-d', 
                        strtotime($date_modifier)
                    ),
                );
                $cart[] = $cart_item;
            } 
            $details['payment_context'] = array(
                'cart' => $cart,
            );
        }

        $request->setResult((array) $details);
    }

    private function canonicalizePhoneNumber($number) {
	if ($number === null)
	    return $number;
        if (preg_match("/\+33.*/", $number)) {
            return $number;
        } else {
            return str_replace('06', '+336', $number);
        }
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
