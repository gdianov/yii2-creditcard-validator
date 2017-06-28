<?php

namespace gdianov\validators;

use yii\base\InvalidConfigException;
use yii\validators\Validator;

/**
 * Class CreditCardValidator
 *
 * ```php
 *       public function rules() {
 *           return [
 *              [['text'], 'string'],
 *              ['card', CreditCardValidator::className(), 'type' => 'mastercard', 'luhn' => true],
 *              [['date'], 'safe'],
 *           ];
 *       }
 *
 *      CreditCardValidator::validateCard('mastercard', 'some_value', true)
 * ```
 *
 * @author Dianov German <es_dianoff@yahoo.com>
 * @since 1.0
 */
class CreditCardValidator extends Validator
{
    /**
     * @var string type of card, for validate.
     */
    public $type;

    /**
     * @var bool Apply Luhn algorithm
     *
     * @see https://en.wikipedia.org/wiki/Luhn_algorithm
     */
    public $luhn = false;


    protected static $cards = [
        'visaelectron' => [
            'pattern' => '/^4(026|17500|405|508|844|91[37])/',
            'length' => [16],
        ],
        'maestro' => [
            'pattern' => '/^(5(018|0[23]|[68])|6(39|7))/',
            'length' => [12, 13, 14, 15, 16, 17, 18, 19],
        ],
        'forbrugsforeningen' => [
            'pattern' => '/^600/',
            'length' => [16],
        ],
        'dankort' => [
            'pattern' => '/^5019/',
            'length' => [16],
        ],
        'visa' => [
            'pattern' => '/^4/',
            'length' => [13, 16],
        ],
        'mastercard' => [
            'pattern' => '/^(5[0-5]|2[2-7])/',
            'length' => [16],
        ],
        'amex' => [
            'pattern' => '/^3[47]/',
            'format' => '/(\d{1,4})(\d{1,6})?(\d{1,5})?/',
            'length' => [15],
        ],
        'dinersclub' => [
            'pattern' => '/^3[0689]/',
            'length' => [14],
        ],
        'discover' => [
            'pattern' => '/^6([045]|22)/',
            'length' => [16],
        ],
        'unionpay' => [
            'pattern' => '/^(62|88)/',
            'length' => [16, 17, 18, 19],
        ],
        'jcb' => [
            'pattern' => '/^35/',
            'length' => [16],
        ],
    ];

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!is_string($this->type)) {
            throw new InvalidConfigException('The "type" property must be set.');
        }
        if ($this->message === null) {
            $this->message = \Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * @param $type
     * @param $number
     * @return int
     * @throws InvalidConfigException
     */
    protected static function validPattern($type, $number)
    {
        return preg_match(self::getType($type)['pattern'], $number);
    }

    /**
     * @param $type
     * @param $number
     * @return bool
     * @throws InvalidConfigException
     */
    protected static function validLength($type, $number)
    {
        $allowable = self::getType($type)['length'];
        return in_array(mb_strlen($number), $allowable);
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected static function getType($type)
    {
        if (!isset(self::$cards[ $type ])) {
            throw new InvalidConfigException('Unknown type of card.');
        }

        return self::$cards[ $type ];
    }

    /**
     * @param $type
     * @param $value
     * @param bool $luhn
     * @return bool
     */
    public static function validateCard($type, $value, $luhn = false)
    {
        $valid = self::validLength($type, $value)
            && self::validPattern($type, $value);
        if ($luhn) {
            $valid = $valid && self::luhnValidate($value);
        }

        return $valid;
    }

    /**
     * @param mixed $value
     * @return array|null
     */
    protected function validateValue($value)
    {
        $valid = self::validateCard($this->type, $value, $this->luhn);
        return $valid ? null : [$this->message, []];
    }

    /**
     * @param $value
     * @return bool
     */
    protected static function luhnValidate($value)
    {
        $sum = 0;
        for ($i = (2 - (mb_strlen($value) % 2)); $i <= mb_strlen($value); $i += 2) {
            $sum += (int)($value{$i - 1});
        }
        for ($i = (mb_strlen($value) % 2) + 1; $i < mb_strlen($value); $i += 2) {
            $digit = (int)($value{$i - 1}) * 2;
            if ($digit < 10) {
                $sum += $digit;
            } else {
                $sum += ($digit - 9);
            }
        }
        if (($sum % 10) == 0) {
            return true;
        } else {
            return false;
        }
    }
}