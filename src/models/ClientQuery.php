<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class ClientQuery
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class ClientQuery extends QueryBuilderHandler
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
