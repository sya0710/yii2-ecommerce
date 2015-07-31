# yii2-ecommerce
Module manager ecommerce, CRUD ecommerce

## Installation
Add 

```php
"sya/yii2-ecommerce": "dev-master"
```

to the require section of your composer.json file.

# Module
```php
use sya\gallery\Module;
'modules' => [
    'ecommerce' => [
        'class' => 'sya\ecommerce\Module',
    ],
]
```

## Demo

```php
use sya\ecommerce\Ecommerce;
echo Ecommerce::widget();
```

## License
**yii2-ecommerce** is released under the MIT License. See the bundled LICENSE.md for details.
