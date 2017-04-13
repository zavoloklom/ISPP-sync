<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\Connection;

/**
 * Class Client
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class Client extends ActiveRecord
{

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
        return 'clients';
    }

    /**
     * @inheritdoc
     * @return ClientQuery
     */
    public static function qb()
    {
        $qb = new ClientQuery(self::getConnection());
        return $qb->table(self::tableName());
    }
}
