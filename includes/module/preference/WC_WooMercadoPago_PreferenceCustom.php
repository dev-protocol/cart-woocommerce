<?php
/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

class WC_WooMercadoPago_PreferenceCustom extends WC_WooMercadoPago_PreferenceAbstract
{
    /**
     * WC_WooMercadoPago_PreferenceCustom constructor.
     * @param $order
     * @param $custom_checkout
     */
    public function __construct($order, $custom_checkout)
    {
        parent::__construct($order, $custom_checkout);
        $this->preference['transaction_amount'] = $this->get_transaction_amount();
        $this->preference['token'] = $this->checkout['token'];
        $this->preference['description'] = implode(', ', $this->list_of_items);
        $this->preference['installments'] = (int)$this->checkout['installments'];
        $this->preference['payment_method_id'] = $this->checkout['paymentMethodId'];
        $this->preference['payer']['email'] = $this->get_email();
        if (array_key_exists('token', $this->checkout)) {
            $this->preference['metadata']['token'] = $this->checkout['token'];
            if (!empty($this->checkout['CustomerId'])) {
                $this->preference['payer']['id'] = $this->checkout['CustomerId'];
            }
            if (!empty($this->checkout['issuer'])) {
                $this->preference['issuer_id'] = (integer)$this->checkout['issuer'];
            }
        }
        $this->preference['statement_descriptor'] = get_option('_mp_statement_descriptor', 'Mercado Pago');
        $this->preference['additional_info']['items'] = $this->items;
        $this->preference['additional_info']['payer'] = $this->get_payer_custom();
        $this->preference['additional_info']['shipments'] = $this->shipments_receiver_address();
        if ($this->ship_cost > 0) {
            $this->preference['additional_info']['items'][] = $this->ship_cost_item();
        } 
        if (
            isset($this->checkout['discount']) && !empty($this->checkout['discount']) &&
            isset($this->checkout['coupon_code']) && !empty($this->checkout['coupon_code']) &&
            $this->checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-custom'
        ) {
            $this->preference['additional_info']['items'][] = $this->add_discounts();
        }
        $this->add_discounts_campaign();
    }

    /**
     * @return array
     */
    public function add_discounts()
    {
        $item = array(
            'title' => __('Discount provided by store', 'woocommerce-mercadopago'),
            'description' => __('Discount provided by store', 'woocommerce-mercadopago'),
            'quantity' => 1,
            'category_id' => get_option('_mp_category_name', 'others'),
            'unit_price' => ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                -floor($this->checkout['discount'] * $this->currency_ratio) : -floor($this->checkout['discount'] * $this->currency_ratio * 100) / 100
        );
        return $item;
    }

}