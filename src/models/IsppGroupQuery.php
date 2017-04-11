<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class IsppGroupQuery
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class IsppGroupQuery extends QueryBuilderHandler
{
    public function active()
    {
        return $this->where(IsppGroup::tableName().'.state', '=', IsppGroup::STATE_ACTIVE);
    }

    public function inactive()
    {
        return $this->where(IsppGroup::tableName().'.state', '=', IsppGroup::STATE_INACTIVE);
    }

}