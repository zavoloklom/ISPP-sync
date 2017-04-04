<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\Connection;

/**
 * Class IsppEvent
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class IsppEvent extends ActiveRecord
{

  const TYPE_EVENT      = 0;
  const TYPE_LATECOME   = 1;

  /**
   * @inheritdoc
   */
  public static function getConnection()
  {
    return new Connection(CONFIG['web_server']['adapter'], CONFIG['web_server']['options']);
  }

  /**
   * @inheritdoc
   * @return IsppEventQuery
   */
  public static function qb()
  {
    $qb = new IsppEventQuery(self::getConnection());
    return $qb->table(self::tableName());
  }

}