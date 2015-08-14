<?php

namespace sya\ecommerce;

class EcommerceAssets extends \yii\web\AssetBundle
{
	public $css = [
        'css/syaecommerce.css',
	];
	public $depends = [
        'yii\bootstrap\BootstrapAsset',
	];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';
        parent::init();
    }
}
