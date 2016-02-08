<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\ar\eagerjoin;

use yii\db\ActiveQuery;

/**
 * EagerJoinQueryTrait simplifies building of the query, which is suitable for [[EagerJoinBehavior]] usage.
 * This trait should be used in the [[\yii\db\ActiveQuery]] descendant class, while related ActiveRecord class
 * should use [[EagerJoinTrait]] trait.
 *
 * Setup example:
 *
 * ```php
 * use yii\db\ActiveRecord;
 * use yii2tech\ar\eagerjoin\EagerJoinTrait;
 * use yii\db\ActiveQuery;
 * use yii2tech\ar\eagerjoin\EagerJoinQueryTrait;
 *
 * class Item extends ActiveRecord
 * {
 *     use EagerJoinTrait;
 *
 *     public function find()
 *     {
 *         return new ItemQuery(get_called_class());
 *     }
 *
 *     // ...
 * }
 *
 * class ItemQuery extends ActiveQuery
 * {
 *     use EagerJoinQueryTrait;
 *     // ...
 * }
 * ```
 *
 * Usage example:
 *
 * ```php
 * $items = Item::find()->eagerJoinWith('group')->all();
 * ```
 *
 * @see EagerJoinTrait
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait EagerJoinQueryTrait
{
    /**
     * Performs eager joins with the specified relations.
     * @param @param string|array $with the relations to be joined.
     * @param string|array $joinType the join type of the relations specified in `$with`.
     * @return ActiveQuery|static query self reference.
     */
    public function eagerJoinWith($with, $joinType = 'LEFT JOIN')
    {
        /* @var $this ActiveQuery|static */
        if ($this->select === null) {
            $mainTableName = call_user_func([$this->modelClass, 'tableName']);
            $this->select(['{{' . $mainTableName . '}}.*']);
        }

        /* @var $mainModel \yii\db\ActiveRecord */
        $mainModel = new $this->modelClass();
        $boundary = '__';

        foreach ((array)$with as $relation) {
            $relationQuery = $mainModel->getRelation($relation);
            $relationTableName = call_user_func([$relationQuery->modelClass, 'tableName']);
            /* @var $relationTableSchema \yii\db\TableSchema */
            $relationTableSchema = call_user_func([$relationQuery->modelClass, 'getTableSchema']);

            $relatedAttributes = $relationTableSchema->getColumnNames();
            $selectColumns = [];
            foreach ($relatedAttributes as $attribute) {
                $selectColumns[] = '{{' . $relationTableName . '}}.[[' . $attribute . ']] AS ' . $relation . $boundary . $attribute;
            }
            $this->addSelect($selectColumns);
        }
        return $this->joinWith($with, false, $joinType);
    }

    /**
     * Performs eager left joins with the specified relations.
     * @param @param string|array $with the relations to be joined.
     * @return ActiveQuery|static query self reference.
     */
    public function eagerLeftJoinWith($with)
    {
        return $this->eagerJoinWith($with, 'LEFT JOIN');
    }

    /**
     * Performs eager inner joins with the specified relations.
     * @param @param string|array $with the relations to be joined.
     * @return ActiveQuery|static query self reference.
     */
    public function eagerInnerJoinWith($with)
    {
        return $this->eagerJoinWith($with, 'INNER JOIN');
    }
}