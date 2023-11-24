<?php

namespace CA\CARaty\Model;

class CARaty
{
    const url = 'https://ewniosek.credit-agricole.pl/eWniosek/simulator_u.jsp';

    /**
     * @var string
     */
    private $caratyPassword;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $firstProductName;

    /**
     * @var string
     */
    private $firstProductPrice;

    /**
     * @var string
     */
    public $hashString;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var array
     */
    private $products;

    /**
     * @var string
     */
    private $pspId;

    /**
     * @var string
     */
    private $randomValue;

    /**
     * @var string
     */
    private $totalOrder;

    /**
     * CARaty constructor
     * @param array $products
     * @param string $pspId
     * @param string $password
     * @param array $order
     */
    public function __construct(array $products, $pspId, $password, array $order)
    {
        $this->products = $products;
        $this->pspId = $pspId;
        $this->caratyPassword = $password;
        $this->totalOrder = $this->formatNumber($order['total']);
        $this->email = $order['email'];
        $this->orderNumber = $order['number'];
        $this->generateRandomValue();
        $this->setFirstProduct($this->products);
    }

    /**
     * @return string
     */
    private function calculateHash()
    {
        $this->hashString = sprintf(
            '%sRAT2%s%s%s%s%s',
            $this->pspId,
            $this->formatNumber($this->totalOrder),
            $this->firstProductName,
            $this->formatNumber($this->firstProductPrice),
            $this->randomValue,
            $this->caratyPassword
        );
//        var_dump($this->firstProductName);
//        var_dump($this->hashString);
//        var_dump(md5($this->hashString));
//        die();
        return md5($this->hashString);
    }

    /**
     * @param $number
     * @return string
     */
    private function formatNumber($number)
    {
        return number_format(round(floatval($number), 2), 2, '.', '');
    }

    /**
     * @return array
     */
    private function generateParamsArray()
    {
        $params = array(
            'PARAM_TYPE' => 'RAT',
            'PARAM_PROFILE' => $this->pspId,
            'POST_ATTR' => '1',
            'creditInfo.creditAmount' => number_format(
                $this->totalOrder,
                2,
                '.',
                ''
            ),
            'creditInfo.creditPeriod' => '12',
            'email.address' => $this->email,
            'cart.orderNumber' => $this->orderNumber,
            'PARAM_CREDIT_AMOUNT' => number_format(
                $this->totalOrder,
                2,
                '.',
                ''
            ),
            'PARAM_AUTH' => '2',
            'PARAM_HASH' => $this->calculateHash(),
            'randomizer' => $this->randomValue,
        );
        foreach ($this->products as $k => $product) {
            $params['cart.itemName' . ($k + 1)] = htmlentities($product['name'], ENT_QUOTES, "UTF-8");
            $params['cart.itemQty' . ($k + 1)] = $product['qty'];
            $params['cart.itemPrice' . ($k + 1)] = $this->formatNumber($product['price']);
        }

        return $params;
    }

    /**
     * @return string
     */
    private function generateRandomValue()
    {
        $this->randomValue = date('YmdHis') . rand();

        return $this->randomValue;
    }

    /**
     * @return string
     */
    private function generateQueryParamethers()
    {
        $params = $this->generateParamsArray();
        $queryString = array();
        foreach ($params as $name => $value) {
            $queryString[] = $name . '=' . $value;
        }

        return implode('&', $queryString);
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->calculateHash();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::url . '?' . $this->generateQueryParamethers();
    }

    /**
     * @return string
     */
    public function getForm()
    {
        $params = $this->generateParamsArray();
        $formString = array('<form method="POST" action="' . self::url . '">');
        foreach ($params as $name => $value) {
            $formString[] = '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }

        $formString[] = '<input type="button" name="asd" value="wyślij" />';
        $formString[] = '</form>';

        return implode('', $formString);
    }

    /**
     * @return string
     */
    public function getRandomValue()
    {
        return $this->randomValue;
    }

    public function setFirstProduct($products) {
        if (!empty($products)) {
            $this->firstProductName = $products[0]['name'];
            $this->firstProductPrice = $products[0]['price'];
        }
    }
}