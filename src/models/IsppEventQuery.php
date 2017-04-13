<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class IsppEventQuery
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class IsppEventQuery extends QueryBuilderHandler
{
    public function latecomes()
    {
        return $this->where(IsppEvent::tableName().'.state', '=', IsppEvent::STATE_LATECOME);
    }
}
