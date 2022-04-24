<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin;

use PayPlug\SyliusPayPlugPlugin\ApiClient\PayPlugApiClientInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class PayPlugGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'payplug';

    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'PayPlug',
            'payum.http_client' => '@payplug_sylius_payplug_plugin.api_client.payplug',
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'secretKey' => null,
                'notificationUrlDev' => null,
                'oneyPaymentx3' => false,
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
                'secretKey',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var PayPlugApiClientInterface $payPlugApiClient */
                $payPlugApiClient = $config['payum.http_client'];

                $payPlugApiClient->initialise(
                    $config['secretKey'], 
                    $config['notificationUrlDev'], 
                    $config['oneyPayment'], 
                    $config['oneyFees']);

                return $payPlugApiClient;
            };
        }
    }
}
