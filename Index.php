<?php

namespace App;

use App\EcPay\AllInOne;

class index
{
    public function index()
    {
        try {
            $ecPay = new AllInOne();

            // 基本參數
            $sendParams = [
                'ReturnURL' => 'http://localhost/index/server',
                'OrderResultURL' => 'http://localhost/index/complete',
                'MerchantTradeNo' => 'TT20200901',
                'MerchantTradeDate' => date('Y/m/d H:i:s'),
                'TotalAmount' => 5,
                'TradeDesc' => '付款描述',
                'ChoosePayment' => $ecPay::ALL,
                'IgnorePayment' => $ecPay::CREDIT . '#' . $ecPay::ANDROID_PAY . '#' . $ecPay::GOOGLE_PAY
            ];

            // 商品參數
            $sendParams['Items'] = [
                [
                    'Name' => '歐付寶黑芝麻豆漿',
                    'Price' => 5,
                    'Currency' => '元',
                    'Quantity' => 1,
                    'URL' => 'dedwed'
                ]
            ];

            $ecPay->setSendParams($sendParams);
            $form = $ecPay->checkOut();

            echo $form;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 前景接收綠界回應
     */
    public function complete()
    {
        try {
            $ecPay = new AllInOne();
            $response = $ecPay->getResponse();

            echo '1|OK';
        } catch (\Exception $e) {
            echo '0|' . $e->getMessage();
        }
    }

    /**
     * 背景接收綠界回應
     */
    public function server()
    {
        try {
            $ecPay = new AllInOne();
            $response = $ecPay->getResponse();

            echo '1|OK';
        } catch (\Exception $e) {
            echo '0|' . $e->getMessage();
        }
    }
}
