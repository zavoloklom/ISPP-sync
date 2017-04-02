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
 * Class IsppSync
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class IsppSync extends ActiveRecord
{

  /**
   * @inheritdoc
   */
  public static function getConnection()
  {
    return new Connection(CONFIG['web_server']['adapter'], CONFIG['web_server']['options']);
  }

  /**
   * @inheritdoc
   * @return QueryBuilderHandler
   */
  public static function qb()
  {
    $qb = new QueryBuilderHandler(self::getConnection());
    return $qb->table(self::tableName());
  }

}