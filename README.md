# yii2-creditcard-validator

Supported cards :
--------------
1. `visaelectron`
2. `maestro`
3. `forbrugsforeningen`
4. `dankort`
5. `visa`
6. `mastercard`
7. `amex`
8. `dinersclub`
9. `discover`
10. `unionpay`
11. `jcb`

# How to use?

You can attach the validator as a model or use it directly.

```php
public function rules() {
       return [
           [['text'], 'string'],
           ['card', CreditCardValidator::className(), 'type' => 'mastercard', 'luhn' => true],
           [['date'], 'safe'],
       ];
    }
    
CreditCardValidator::validateCard('mastercard', 5100000000000000, true); //return true or false

```
Params:
1. type of card.
2. value for validation
3. apply luhn algorithm (https://en.wikipedia.org/wiki/Luhn_algorithm)
