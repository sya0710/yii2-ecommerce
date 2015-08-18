<?php

namespace sya\ecommerce\components;

use Yii;
use yii\helpers\ArrayHelper;
use MongoDate;
use sya\ecommerce\Ecommerce;

class ActiveRecordMongo extends \yii\mongodb\ActiveRecord {

    /**
     * @inheritdoc
     */
    public function beforeSave($insert) {
        $attributes = array_keys($this->getAttributes());
        
        // Get namespace of model
        $ecommerce = Ecommerce::module();
        
        // User name field
        $username = ArrayHelper::getValue($ecommerce->userTable, 'nameField');

        // ID
        if ($this->isNewRecord AND empty($this->_id))
            $this->_id = uniqid();
        
        // ecommerce_id
        if ($this->isNewRecord AND empty($this->ecommerce_id))
            $this->ecommerce_id = uniqid('EM');
        
        // status
        if ($this->isNewRecord AND empty($this->status))
            $this->status = $ecommerce::STATUS_NEW;
        
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
                        'creator_name' => Yii::$app->user->identity->$username,
                        'created_at' => $now,
                        'action' => 'add',
                        'note' => 'Add new order: ' . $this->ecommerce_id
                    ]
                ];
            }
        } else {
            if (in_array('log', $attributes) AND in_array('note_admin_content', $attributes) AND empty($this->note_admin_content)){
                if ($this->status === ''){
                    $this->log = ArrayHelper::merge([
                        [
                            'creator' => Yii::$app->user->id,
                            'creator_name' => Yii::$app->user->identity->$username,
                            'created_at' => $now,
                            'action' => 'delete',
                            'note' => 'Delete order: ' . $this->ecommerce_id
                        ]
                    ], $this->log);
                } else {
                    $this->log = ArrayHelper::merge([
                        [
                            'creator' => Yii::$app->user->id,
                            'creator_name' => Yii::$app->user->identity->$username,
                            'created_at' => $now,
                            'action' => 'update',
                            'note' => 'Update order: ' . $this->ecommerce_id
                        ]
                    ], $this->log);
                }
            }
        }
        
        // Note admin
        if ($this->isNewRecord) {
            if (in_array('note_admin', $attributes) AND in_array('note_admin_content', $attributes) AND !empty($this->note_admin_content)){
                $this->note_admin = [
                    [
                        'content' => $this->note_admin_content,
                        'creator' => Yii::$app->user->id,
                        'creator_name' => Yii::$app->user->identity->$username,
                        'created_at' => $now,
                    ]
                ];
                $this->note_admin_content = '';
            }
        } else {
            if (in_array('note_admin', $attributes) AND in_array('note_admin_content', $attributes) AND !empty($this->note_admin_content)){
                $this->note_admin = ArrayHelper::merge($this->note_admin, [
                    [
                        'content' => $this->note_admin_content,
                        'creator' => Yii::$app->user->id,
                        'creator_name' => Yii::$app->user->identity->$username,
                        'created_at' => $now,
                    ]
                ]);
                $this->note_admin_content = '';
            }
        }
        
        return parent::beforeSave($insert);
    }

}
