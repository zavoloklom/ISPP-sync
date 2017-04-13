<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class IsppStudentQuery
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class IsppStudentQuery extends QueryBuilderHandler
{
    public function active()
    {
        return $this->where(IsppStudent::tableName().'.state', '=', IsppStudent::STATE_ACTIVE);
    }

    public function inactive()
    {
        return $this->where(IsppStudent::tableName().'.state', '=', IsppStudent::STATE_INACTIVE);
    }
}
