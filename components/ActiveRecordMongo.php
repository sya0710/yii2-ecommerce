<?php

namespace sya\ecommerce\components;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use MongoDate;

class ActiveRecordMongo extends \yii\mongodb\ActiveRecord {

    /**
     * @inheritdoc
     */
    public function beforeSave($insert) {
        $attributes = array_keys($this->getAttributes());

        // ID
        if ($this->isNewRecord AND empty($this->_id))
            $this->_id = uniqid();
        
        // ecommerce_id
        if ($this->isNewRecord AND empty($this->ecommerce_id))
            $this->ecommerce_id = uniqid('EM');
        
        // status
        if ($this->isNewRecord AND empty($this->status))
            $this->status = 'new';
        
        // date time
        $now = new MongoDate();
        if (in_array('created_at', $attributes) AND empty($this->created_at))
            $this->created_at = $now;
        if (in_array('updated_at', $attributes))
            $this->updated_at = $now;
        
        // creator
        if ($this->isNewRecord) {
            if (in_array('creator', $attributes))
                $this->creator = Yii::$app->user->id;
        }
        
        // Write log order
        if ($this->isNewRecord) {
            if (in_array('log', $attributes)){
                $this->log = [
                    [
                        'creator' => Yii::$app->user->id,
                        'created_at' => $now,
                        'action' => 'add',
                        'note' => Yii::$app->user->id . ' add new order: ' . $this->ecommerce_id
                    ]
                ];
            }
        } else {
            if ($this->status === ''){
                $this->log = ArrayHelper::merge([
                    [
                        'creator' => Yii::$app->user->id,
                        'created_at' => $now,
                        'action' => 'delete',
                        'note' => Yii::$app->user->id . ' delete order: ' . $this->ecommerce_id
                    ]
                ], $this->log);
            } else {
                $this->log = ArrayHelper::merge([
                    [
                        'creator' => Yii::$app->user->id,
                        'created_at' => $now,
                        'action' => 'update',
                        'note' => Yii::$app->user->id . ' update order: ' . $this->ecommerce_id
                    ]
                ], $this->log);
            }
        }

        return parent::beforeSave($insert);
    }

}
