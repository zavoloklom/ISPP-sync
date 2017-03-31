<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\Connection;

/**
 * Class ClientsGroup
 * @package zavoloklom\ispp\sync\src\models
 */
class ClientsGroup extends ActiveRecord
{

  const TYPE_UNDEFINED    = 0;
  const TYPE_CLASS        = 1;
  const TYPE_KINDERGARTEN = 2;

  /**
   * @inheritdoc
   */
  public static function getConnection()
  {
    return new Connection(CONFIG['local_server']['adapter'], CONFIG['local_server']['options']);
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'clients_groups';
  }

  /**
   * @inheritdoc
   */
  public static function qb()
  {
    $qb = new ClientsGroupQuery(self::getConnection());
    return $qb->table(self::tableName());
  }
}