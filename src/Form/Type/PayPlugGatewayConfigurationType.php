<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckBoxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PayPlugGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('secretKey', TextType::class, [
                'label' => 'payplug_sylius_payplug_plugin.ui.secret_key',
                'constraints' => [
                    new NotBlank([
                        'message' => 'payplug_sylius_payplug_plugin.secret_key.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('notificationUrlDev', TextType::class, [
                'label' => 'payplug_sylius_payplug_plugin.ui.notification_url_for_env_dev',
                'required' => false,
            ])
            ->add('oneyPayment', CheckBoxType::class, [
                'label' => 'payplug_sylius_payplug_plugin.ui.oney_payment',
                'required' => false,
            ])
            ->add('oneyFees', CheckBoxType::class, [
                'label' => 'payplug_sylius_payplug_plugin.ui.oney_with_fees',
                'required' => false,
            ])
            ->add('oneyBrand', TextType::class, [
                'label' => 'payplug_sylius_payplug_plugin.ui.oney_brand',
                'required' => false,
            ])
            ->add('oneyShippingLabel', TextType::class, [
                'label' => 'payplug_sylius_payplug_plugin.ui.oney_shipping_label',
                'required' => false,
            ])
            ->add('oneyDateModifier', TextType::class, [
                'label' => 'payplug_sylius_payplug_plugin.ui.oney_date_modifier',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();

                $data['payum.http_client'] = '@payplug_sylius_payplug_plugin.api_client.payplug';

                $event->setData($data);
            })
        ;
    }
}
