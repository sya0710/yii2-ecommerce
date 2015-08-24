<?php

namespace sya\ecommerce\components;

use Yii;
use yii\helpers\ArrayHelper;
use MongoDate;
use sya\ecommerce\Ecommerce;
use yii\bootstrap\Html;

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
        $this->buildLogOrder($attributes, $username, $now);
        
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
    
    /**
     * Function build log order
     * @param strinf $attributes attributes of model
     * @param string $username column username for table user
     * @param mongodate $now time of user use
     */
    protected function buildLogOrder($attributes, $username, $now){
        // Attribute default no log
        $attributeRemove = [
            'updated_at',
            'created_at',
            'note_customer',
            'ecommerce_id',
            '_id',
            'creator',
            'updater',
            'log'
        ];
        
        // Just check the update
        if (!$this->isNewRecord){
            $oldAttributes = $this->getOldAttributes();
            // Get all attribute change when update
            $changeValue = [];
            
            // Attribute after change
            $attributeNews = array_diff_key($this->getAttributes(), array_flip($attributeRemove));
            
            foreach ($attributeNews as $attribute => $attributeValue) {
                if (isset($oldAttributes[$attribute]) AND ($oldAttributes[$attribute] !== $attributeValue)){
                    if (!is_array($oldAttributes[$attribute]))
                        $changeValue[] = Html::tag('li', $this->getAttributeLabel($attribute) . ' from ' . $oldAttributes[$attribute] . ' to ' . $attributeValue);
                    else{
                        $changeValue[] = Html::tag('li', $this->getAttributeLabel($attribute) . '' . Html::tag('ul', implode('', $this->getValueAtributeArray($oldAttributes[$attribute], $attributeValue))));
                    }
                }
            }
            $action = 'update';
        } else {
            $action = 'add';
        }
        
        // IF exits log in attribute
        if (in_array('log', $attributes)){
            // IF exits log or log empty
            if (empty($this->log)) {
                $this->log = [
                    [
                        'creator' => Yii::$app->user->id,
                        'creator_name' => Yii::$app->user->identity->$username,
                        'created_at' => $now,
                        'action' => $action,
                        'note' => ucfirst($action) . ' new order: ' . $this->ecommerce_id
                    ]
                ];
            } else if (in_array('note_admin_content', $attributes) AND empty($this->note_admin_content)){
                if ($this->status === \sya\ecommerce\Module::STATUS_EMPTY){
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
                            'action' => $action,
                            'note' => ucfirst($action) . ' order: ' . $this->ecommerce_id . ' width change follow: ' . Html::tag('ul', implode('', $changeValue))
                        ]
                    ], $this->log);
                }
            }
        }
    }
    
    /**
     * Function get value after change
     * @param array $attribute Array attribute before changes
     * @param array $attributeNew Array attribute after change
     * @param array $changeValue Array value change
     * @param string $name key attribute change
     * @return array
     */
    protected function getValueAtributeArray($attribute, $attributeNew, $changeValue = [], $name = ''){
        foreach ($attributeNew as $key => $items) {
            if (is_array($items)){
                $changeValue = $this->getValueAtributeArray($attribute[$key], $items, $changeValue, $key);
            } else {
                foreach ($attributeNew as $keyItem => $item) {
                    $itemOldValue = ArrayHelper::getValue($attribute, $keyItem, $this->getAttributeLabel($keyItem));
                    if ($item !== $itemOldValue){
                        $changeValue[] = Html::tag('li', $this->getAttributeLabel($keyItem) . ' of ' . $name . ' from ' . $itemOldValue . ' to ' . $item);
                        break;
                    }
                }
                break;
            }
        }
        
        return $changeValue;
    }

}
