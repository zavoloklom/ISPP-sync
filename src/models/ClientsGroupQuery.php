<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class ClientsGroupQuery
 * @package zavoloklom\ispp\sync\src\models
 */
class ClientsGroupQuery extends QueryBuilderHandler
{
  public function active()
  {
    return $this->where('state', '=', 1);
  }
}