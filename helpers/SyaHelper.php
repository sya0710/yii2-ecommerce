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
     * @param array $fieldColumns Array columns in field collection
     * @return mixed
     */
    public static function addMongoFilter($query, $attribute, $values, $operator = null, $fieldColumns = []){
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
            } elseif ($operator == 'or'){
                $valueArr = self::buildFieldOperatorOr($attribute, $fieldColumns, $values);
                $where = [
                    '$' . $operator => $valueArr,
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

    /**
     * Function gengerate where of attribute
     * @param $attribute Name attribute
     * @param $fieldColumns Column in array attribute
     * @param $value Value in attribute
     * @return array
     */
    private function buildFieldOperatorOr($attribute, $fieldColumns, $value){
        $conditionColumns = [];

        foreach ($fieldColumns as $filedCustomerOrder => $fieldCustomerTable) {
            $conditionColumns[] = [
                $attribute . '.' . $filedCustomerOrder => [
                    '$regex' => $value,
                ]
            ];
        }

        return $conditionColumns;
    }
}
