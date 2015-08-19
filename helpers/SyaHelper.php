<?php

namespace sya\ecommerce\helpers;

use yii\helpers\ArrayHelper;

class SyaHelper {
    
    /**
     * Function create where mongodb
     * @param object $query
     * @param string $attribute Name attribute in collection
     * @param string|array $values Value of attribute
     * @param string|array $operator Where of attribute and value: like, between, gt, gte, lt, lte, ne, in
     * @return object $query
     */
    public static function addMongoFilter($query, $attribute, $values, $operator = null){
        if (!empty($values) OR $values === "0") {
            if ($operator == 'between' AND isset($values[1])) {
                $where = [
                    $attribute => [
                        '$gte' => ArrayHelper::getValue($values, 0),
                        '$lte' => ArrayHelper::getValue($values, 1),
                    ]
                ];
            } elseif (in_array($operator, ['gt', 'gte', 'lt', 'lte', 'ne', 'in', 'nin'])) {
                $where = [
                    $attribute => [
                        '$' . $operator => $values,
                    ]
                ];
            } elseif ($operator == 'like') {
                $where = [
                    $attribute => [
                        '$regex' => $values,
                    ]
                ];
            } else {
                $where = [
                    $attribute => $values
                ];
            }
            
            return $query->andFilterWhere($where);
        }
        
        return $query;
    }
}
