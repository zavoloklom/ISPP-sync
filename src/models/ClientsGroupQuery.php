<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class ClientsGroupQuery
 * @package zavoloklom\ispp\sync\src\models
 */
class ClientsGroupQuery extends QueryBuilderHandler
{
    public function schoolClasses()
    {
        return $this->where(ClientsGroup::tableName().'.GroupType', '=', ClientsGroup::TYPE_CLASS);
    }

    public function kindergartens()
    {
        return $this->where(ClientsGroup::tableName().'.GroupType', '=', ClientsGroup::TYPE_KINDERGARTEN);
    }

}