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
   * Returns the database connection used by this AR class.
   * By default, the "db" application component is used as the database connection.
   * You may override this method if you want to use a different database connection.
   * @return Connection the database connection used by this AR class.
   */
  public static function getConnection()
  {
    return new Connection('mysql', [
      'driver'    => 'mysql',
      'host'      => '127.0.0.1',
      'username'  => 'root',
      'password'  => '',
      'database'  => 'ispp-ecafe-test'
    ]);

    // return new localConnection($GLOBALS['ISPP_SYNC_CONFIG'])
    // return new Connection($GLOBALS['ISPP_SYNC_CONFIG']['local_server']['adapter'], $GLOBALS['ISPP_SYNC_CONFIG']['local_server']['options'])
  }

  /**
   * Return table name
   *
   * @return string
   */
  public static function tableName()
  {
    return 'clients_groups';
  }

  /**
   * QueryBuilder
   *
   * @return ISPPQuery
   */
  public static function qb()
  {
    $qb = new ClientsGroupQuery(self::getConnection());
    return $qb->table(self::tableName());
  }
}