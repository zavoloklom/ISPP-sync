<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\Connection;

/**
 * Class IsppGroup
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class IsppGroup extends ActiveRecord
{

    const STATE_INACTIVE  = 0;
    const STATE_ACTIVE    = 1;

    /**
     * @inheritdoc
     */
    public static function getConnection()
    {
        return new Connection(CONFIG['web_server']['adapter'], CONFIG['web_server']['options']);
    }

    /**
     * @inheritdoc
     * @return IsppGroupQuery
     */
    public static function qb()
    {
        $qb = new IsppGroupQuery(self::getConnection());
        return $qb->table(self::tableName());
    }

}