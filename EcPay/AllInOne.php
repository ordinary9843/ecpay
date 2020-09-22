<?php

namespace App\Ecpay;

class AllInOne
{
    /** @var string 服務位置 */
    const SERVICE_URL = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';

    /** @var string ECPay提供的HashKey */
    const HASH_KEY = '5294y06JbISpM5x9';

    /** @var string ECPay提供的HashIV */
    const HASH_IV = 'v77hoKGq4kWxNNIS';

    /** @var string ECPay提供的MerchantID */
    const MERCHANT_ID = '2000132';

    /** @var string 加密方式 */
    const ENCRYPT_TYPE = 1;

    /** @var string 付款類型 */
    const PAYMENT_TYPE = 'aio';

    /** @var string 不指定付款方式 */
    const ALL = 'ALL';

    /** @var string 信用卡付費 */
    const CREDIT = 'Credit';

    /** @var string 網路 ATM */
    const WEB_ATM = 'WebATM';

    /** @var string 自動櫃員機 */
    const ATM = 'ATM';

    /** @var string 超商代碼 */
    const CVS = 'CVS';

    /** @var string 超商條碼 */
    const BARCODE = 'BARCODE';

    /** @var string AndroidPay */
    const ANDROID_PAY = 'GooglePay';

    /** @var string GooglePay */
    const GOOGLE_PAY = 'GooglePay';

    /** @var array 基本參數 */
    protected $sendParams = [];

    /** @var array 錯誤訊息 */
    protected $error = [];

    /**
     * 建構子
     *
     * @return mixed
     */
    public function __construct()
    {
        $this->sendParams = [
            'MerchantID' => self::MERCHANT_ID,
            'EncryptType' => self::ENCRYPT_TYPE,
            'PaymentType' => self::PAYMENT_TYPE
        ];
    }

    /**
     * 取得傳送參數
     * 
     * @return array
     */
    public function getSendParams()
    {
        return $this->sendParams;
    }

    /**
     * 設定傳送參數
     * 
     * @param array $sendParams
     * 
     * @return void
     */
    public function setSendParams(array $sendParams)
    {
        $this->sendParams = array_merge($this->sendParams, $sendParams);
    }

    /**
     * 取得錯誤訊息
     * 
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 設定錯誤訊息
     * 
     * @param string $error
     * 
     * @return void
     */
    public function setError(string $error)
    {
        $this->error[] = $error;
    }

    /**
     * 結帳
     *
     * @return string
     *
     * @throws Exception
     */
    public function checkOut()
    {
        $this->validate();

        $checkMacValue = $this->getCheckMacValue($this->sendParams);

        $form = $this->getForm($checkMacValue);

        return $form;
    }

    /**
     * 驗證參數
     *
     * @return void
     *
     * @throws Exception
     */
    private function validate()
    {
        $this->validateSendParams();
        $this->validateItems();
    }

    /**
     * 驗證基本參數
     *
     * @return void
     *
     * @throws Exception
     */
    private function validateSendParams()
    {
        if (strlen($this->sendParams['MerchantID']) === 0) {
            $this->setError('MerchantID is required.');
        }

        if (strlen($this->sendParams['MerchantID']) > 10) {
            $this->setError('MerchantID max langth as 10.');
        }

        if (strlen($this->sendParams['MerchantTradeNo']) === 0) {
            $this->setError('MerchantTradeNo is required.');
        }

        if (strlen($this->sendParams['MerchantTradeNo']) > 20) {
            $this->setError('MerchantTradeNo max langth as 20.');
        }

        if (strlen($this->sendParams['ReturnURL']) === 0) {
            $this->setError('ReturnURL is required.');
        }

        if (strlen($this->sendParams['MerchantTradeDate']) === 0) {
            $this->setError('MerchantTradeDate is required.');
        }

        if (strlen($this->sendParams['TotalAmount']) === 0) {
            $this->setError('TotalAmount is required.');
        }

        if (strlen($this->sendParams['TradeDesc']) === 0) {
            $this->setError('TradeDesc is required.');
        }

        if (strlen($this->sendParams['TradeDesc']) > 200) {
            $this->setError('TradeDesc max langth as 200.');
        }

        if (strlen($this->sendParams['ChoosePayment']) === 0) {
            $this->setError('ChoosePayment is required.');
        }

        if (sizeof($this->sendParams['Items']) === 0) {
            $this->setError('Items is required.');
        }

        if (strlen($this->sendParams['EncryptType']) > 1) {
            $this->setError('EncryptType max langth as 1.');
        }

        if (isset($this->sendParams['ClientBackURL']) && strlen($this->sendParams['ClientBackURL']) > 200) {
            $this->setError('ClientBackURL max langth as 200.');
        }

        if (isset($this->sendParams['OrderResultURL']) && strlen($this->sendParams['OrderResultURL']) > 200) {
            $this->setError('OrderResultURL max langth as 200.');
        }

        if (isset($this->sendParams['NeedExtraPaidInfo']) && strlen($this->sendParams['NeedExtraPaidInfo']) === 0) {
            $this->setError('NeedExtraPaidInfo is required.');
        }

        if (count($this->error) > 0) {
            throw new Exception(implode('<br>', $this->error));
        }
    }

    /**
     * 驗證項目
     *
     * @return void
     *
     * @throws Exception
     */
    private function validateItems()
    {
        if (count($this->sendParams['Items']) > 0) {
            $itemName = '';

            foreach ($this->sendParams['Items'] as $keys => $value) {
                $itemName .= vsprintf('#%s %d %s x %u', $this->sendParams['Items'][$keys]);
                if (!array_key_exists('ItemURL', $this->sendParams)) {
                    if (array_key_exists('URL', $this->sendParams['Items'][$keys])) {
                        $this->sendParams['ItemURL'] = $this->sendParams['Items'][$keys]['URL'];
                    }
                }
            }

            if (strlen($itemName) > 0) {
                $itemName = mb_substr($itemName, 1, 200);
                $this->sendParams['ItemName'] = $itemName;
            }
        } else {
            $this->setError('Items information not found.');
        }

        if (count($this->error) > 0) {
            throw new Exception(implode('<br>', $this->error));
        }

        unset($this->sendParams['Items']);
    }

    /**
     * 產生檢查碼
     * 
     * @param array $sendParams
     * 
     * @return string
     */
    private function getCheckMacValue(array $sendParams)
    {
        $checkMacValue = '';

        if (!empty($sendParams)) {
            unset($sendParams['CheckMacValue']);
            uksort($sendParams, function ($a, $b) {
                return strcasecmp($a, $b);
            });

            $checkMacValue = 'HashKey=' . self::HASH_KEY;
            foreach ($sendParams as $key => $value) {
                $checkMacValue .= '&' . $key . '=' . $value;
            }
            $checkMacValue .= '&HashIV=' . self::HASH_IV;
            $checkMacValue = $this->ecpayUrlEncode($checkMacValue);

            switch (self::ENCRYPT_TYPE) {
                case 1:
                    $checkMacValue = hash('sha256', $checkMacValue);
                    break;
                case 0:
                default:
                    $checkMacValue = md5($checkMacValue);
                    break;
            }

            $checkMacValue = strtoupper($checkMacValue);
        }

        return $checkMacValue;
    }

    /**
     * URL Encode編碼，特殊字元取代
     *
     * @param string $value
     * 
     * @return string
     */
    private function ecpayUrlEncode(string $value)
    {
        $value = urlencode($value);
        $value = strtolower($value);
        $value = $this->replaceSymbol($value);

        return $value;
    }

    /**
     * 取代參數特殊字元
     * 
     * @param string $value
     * 
     * @return string
     */
    private function replaceSymbol($value)
    {
        if (!empty($value)) {
            $value = str_replace('%2D', '-', $value);
            $value = str_replace('%2d', '-', $value);
            $value = str_replace('%5F', '_', $value);
            $value = str_replace('%5f', '_', $value);
            $value = str_replace('%2E', '.', $value);
            $value = str_replace('%2e', '.', $value);
            $value = str_replace('%21', '!', $value);
            $value = str_replace('%2A', '*', $value);
            $value = str_replace('%2a', '*', $value);
            $value = str_replace('%28', '(', $value);
            $value = str_replace('%29', ')', $value);
        }

        return $value;
    }

    /**
     * 產生 form
     * 
     * @param string $checkMacValue
     * 
     * @return string
     */
    private function getForm(string $checkMacValue)
    {
        $form = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>';
        $form .= '<form id="__ecpayForm" method="post" target="_self" action="' . self::SERVICE_URL . '">';

        foreach ($this->sendParams as $keys => $value) {
            $form .= '<input type="hidden" name="' . $keys . '" value="' . htmlentities($value) . '" />';
        }

        $form .= '<input type="hidden" name="CheckMacValue" value="' . $checkMacValue . '" />';
        $form .= '</body></html>';
        $form .= '<script type="text/javascript">document.getElementById("__ecpayForm").submit();</script>';

        return $form;
    }

    /**
     * 取得回應
     *
     * @return array
     *
     * @throws Exception
     */
    public function getResponse()
    {
        $response = [];
        $params = request()->all();

        foreach ($params as $keys => $value) {
            if ($keys !== 'CheckMacValue') {
                if ($keys == 'PaymentType') {
                    $value = str_replace('_CVS', '', $value);
                    $value = str_replace('_BARCODE', '', $value);
                    $value = str_replace('_CreditCard', '', $value);
                }

                if ($keys == 'PeriodType') {
                    $value = str_replace('Y', 'Year', $value);
                    $value = str_replace('M', 'Month', $value);
                    $value = str_replace('D', 'Day', $value);
                }

                $response[$keys] = $value;
            }
        }

        $this->validateResponse($params);

        return $response;
    }

    /**
     * 驗證回應
     *
     * @param array $params
     *
     * @return void
     *
     * @throws Exception
     */
    private function validateResponse(array $params)
    {
        $checkMacValue = $this->getCheckMacValue($params);

        if ($checkMacValue != $params['CheckMacValue']) {
            $this->setError('CheckMacValue verify fail.');
        }

        if (!isset($params['RtnCode']) || $params['RtnCode'] !== '1') {
            $this->setError(sprintf('#%s: %s', $params['RtnCode'], $params['RtnMsg']));
        }

        if (count($this->error) > 0) {
            throw new Exception(implode('<br>', $this->error));
        }
    }
}
