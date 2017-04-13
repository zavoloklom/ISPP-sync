<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\Connection;

/**
 * Class IsppStudent
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class IsppStudent extends ActiveRecord
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
     * @return IsppStudentQuery
     */
    public static function qb()
    {
        $qb = new IsppStudentQuery(self::getConnection());
        return $qb->table(self::tableName());
    }
}
