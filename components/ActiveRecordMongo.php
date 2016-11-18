<?php

namespace sya\ecommerce\components;

use Yii;
use yii\helpers\ArrayHelper;
use DateTime;
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
        
        // date time
        $now = (new DateTime())->getTimestamp();
        if (in_array('created_at', $attributes) AND empty($this->created_at))
            $this->created_at = $now;
        if (in_array('updated_at', $attributes))
            $this->updated_at = $now;
        
        if ($this->isNewRecord) {
            // creator
            if (in_array('creator', $attributes))
                $this->creator = Yii::$app->user->id;
            
            // ID
            if (empty($this->_id))
                $this->_id = uniqid();

            // ecommerce_id
            if (in_array('ecommerce_id', $attributes) AND empty($this->ecommerce_id))
                $this->ecommerce_id = uniqid('EM');

            // status
            if (in_array('status', $attributes) AND empty($this->status))
                $this->status = $ecommerce::STATUS_NEW;
        }
        
        // Write log order
        $this->buildLogOrder($attributes, $username, $now);
        
        // Note admin
        if (in_array('note_admin', $attributes) AND in_array('note_admin_content', $attributes) AND !empty($this->note_admin_content)){
            if ($this->isNewRecord) {
                $this->note_admin = [
                    [
                        'content' => $this->note_admin_content,
                        'creator' => Yii::$app->user->id,
                        'creator_name' => isset(Yii::$app->user->identity) ? Yii::$app->user->identity->$username : Yii::t('ecommerce', 'Customer'),
                        'created_at' => $now,
                    ]
                ];
            } else {
                $this->note_admin = ArrayHelper::merge([
                    [
                        'content' => $this->note_admin_content,
                        'creator' => Yii::$app->user->id,
                        'creator_name' => isset(Yii::$app->user->identity) ? Yii::$app->user->identity->$username : Yii::t('ecommerce', 'Customer'),
                        'created_at' => $now,
                    ]
                ], $this->note_admin);
            }
            $this->note_admin_content = '';
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
            'log',
            'note_admin_content',
            'note_admin',
            'product_text',
        ];
        
        $action = 'create';
        
        // Just check the update
        if (!$this->isNewRecord){
            $oldAttributes = $this->getOldAttributes();
            // Get all attribute change when update
            $changeValue = [];
            
            // Attribute after change
            $attributeNews = array_diff_key($this->getAttributes(), array_flip($attributeRemove));

            foreach ($attributeNews as $attribute => $attributeValue) {
                // Value of item before change
                $oldAttribute = ArrayHelper::getValue($oldAttributes, $attribute);
//                echo $attributeValue . "<br>";
                if (!empty($attributeValue) AND ($oldAttribute !== $attributeValue)){
                    if (!is_array($attributeValue)) {
                        if (!empty($oldAttribute))
                            $changeValue[] = Html::tag('li', '{attribute_' . $attribute . '} {from} ' . Yii::t('ecommerce', ucwords(str_replace('_', ' ', $oldAttribute))) . ' {to} ' . Yii::t('ecommerce', ucwords(str_replace('_', ' ', $attributeValue))));
                        else
                            $changeValue[] = Html::tag('li', '{' . $action . '} {attribute_' . $attribute . '}: ' . Yii::t('ecommerce', ucwords(str_replace('_', ' ', $attributeValue))));
                    } else {
                        $changeValue[] = Html::tag('li', '{attribute_' . $attribute . '}' . Html::tag('ul', implode('', $this->getValueAtributeArray($oldAttribute, $attributeValue))));
                    }
                } else if (empty($attributeValue) AND !empty($oldAttribute)) {
                    $changeValue[] = Html::tag('li', '{delete} {attribute_' . $attribute . '}');
                }
            }
            $action = 'update';
        }

        // IF exits log in attribute
        if (($action == 'create' OR !empty($changeValue)) AND in_array('log', $attributes)){
            // IF exits log or log empty
            if (empty($this->log)) {
                $this->log = [
                    [
                        'creator' => Yii::$app->user->id,
                        'creator_name' => isset(Yii::$app->user->identity) ? Yii::$app->user->identity->$username : Yii::t('ecommerce', 'Customer'),
                        'created_at' => $now,
                        'action' => $action,
                        'note' => $this->ecommerce_id
                    ]
                ];
            } else if (in_array('note_admin_content', $attributes) AND empty($this->note_admin_content)){
                // Note log
                $note = $this->ecommerce_id . ' {follow}' . Html::tag('ul', implode('', $changeValue));

                // If status is null then action set delete
                if ($this->status === \sya\ecommerce\Module::STATUS_EMPTY){
                    $action = 'delete';
                    $note = $this->ecommerce_id;
                }

                $this->log = ArrayHelper::merge([
                    [
                        'creator' => Yii::$app->user->id,
                        'creator_name' => isset(Yii::$app->user->identity) ? Yii::$app->user->identity->$username : Yii::t('ecommerce', 'Customer'),
                        'created_at' => $now,
                        'action' => $action,
                        'note' => $note
                    ]
                ], $this->log);
            }
        }
    }
    
    /**
     * Function get value after change
     * @param array $attribute Array attribute before changes
     * @param array $attributeNew Array attribute after change
     * @param array $changeValue Array value change
     * @param string $name key attribute change
     * @param string $action Action when change value
     * @return array
     */
    protected function getValueAtributeArray($attribute = [], $attributeNew, $changeValue = [], $name = '', $action = 'delete'){
        // Set default value $action, $attributeLong, $attributeSmall
        $attributeLong = $attribute;
        $attributeSmall = $attributeNew;

        // Check if length $attribute > $attributeNew then key not exits is delete. If length $attributeNew > $attribute then key not exits is add
        if (count($attribute) < count($attributeNew)){
            // Action when have key not exits in $attributeLong
            $action = 'create';
            
            // Attribute have long length
            $attributeLong = $attributeNew;
            
            // Attribute have small length
            $attributeSmall = $attribute;
        }

        if (!empty($name))
            $name = ' {of} ' . $name;

        foreach ($attributeLong as $key => $items) {
            if (is_array($items)){
                if (isset($attributeSmall[$key])) {
                    // Set value new and value old when change infomation of order
                    $itemOld = $items;
                    $itemNew = $attributeSmall[$key];
                    if ('create' === $action){
                        $itemOld = $attributeSmall[$key];
                        $itemNew = $items;
                    }

                    $changeValue = $this->getValueAtributeArray($itemOld, $itemNew, $changeValue, $key);
                } else
                    $changeValue[] = Html::tag('li', '{' . $action . '} id: ' . $key . '');
            } else {
                $itemSmallValue = ArrayHelper::getValue($attributeSmall, $key, $this->getAttributeLabel($key));

                // Set default $oldItemValue and $newItemValue
                $oldItemValue = $items;
                $newItemValue = $itemSmallValue;

                // Set old item value and new item value when $action = Delete
                if ($action == 'create'){
                    $oldItemValue = $itemSmallValue;
                    $newItemValue = $items;
                }

                if ($items !== $itemSmallValue){
                    if (!empty($oldItemValue) AND empty($newItemValue))
                        $changeValue[] = Html::tag('li', '{delete} ' . '{ecommerce_' . $key . '}');
                    else if (!empty($oldItemValue) AND !empty($newItemValue))
                        $changeValue[] = Html::tag('li', '{ecommerce_' . $key . '}' . $name . ' {from} ' . Yii::t('ecommerce', ucwords(str_replace ('_', ' ', $oldItemValue))) . ' {to} ' . Yii::t('ecommerce', ucwords(str_replace ('_', ' ', $newItemValue))));
                    else if (empty($oldItemValue) AND !empty($newItemValue))
                        $changeValue[] = Html::tag('li', '{create} {ecommerce_' . $key . '}: ' . Yii::t('ecommerce', ucwords(str_replace ('_', ' ', $newItemValue))));
                    else
                        $changeValue[] = Html::tag('li', '{' . $action . '} {ecommerce_' . $key . '}: ' . Yii::t('ecommerce', ucwords(str_replace ('_', ' ', $newItemValue))));
                }
            }
        }
        return $changeValue;
    }

}
