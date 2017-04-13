<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\Connection;

/**
 * Class Event
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class Event extends ActiveRecord
{

    // Направления турникетов (PassDirection)
    const DIRECTION_DETECTED_INSIDE     = 100;  // Поднос карты внутри здания
    const DIRECTION_NOT_FIXED_EVENT     = 4;    // Проход не фиксируется
    const DIRECTION_PASS_ENTER          = 0;    // Проход через турникет
    const DIRECTION_PASS_EXIT           = 1;    // Выход через турникет
    const DIRECTION_PASS_FORBIDDEN      = 2;    // Проход через турникет запрещен
    const DIRECTION_QUERY_FOR_ENTER     = 8;    //
    const DIRECTION_QUERY_FOR_EXIT      = 9;    //
    const DIRECTION_TURNSTILE_BREAKING  = 3;    // Произошел взлом турникета
    const DIRECTION_TURNSTILE_REFUSAL   = 5;    // Отказ турникета
    const DIRECTION_TWICE_PASS_ENTER    = 6;    // Повторный проход через турникет
    const DIRECTION_TWICE_PASS_EXIT     = 7;    // Повторный выход через турникет

    // Код события (EventCode)
    const EVT_CARD_PRESENTATION = 5;
    const EVT_COME_BY_BUTTON = 0x70;
    const EVT_COME_BY_ID = 0x11;
    const EVT_COME_BY_PC = 0x71;
    const EVT_COME_CONFIRM_BUTTON = 0x17;
    const EVT_COME_CONFIRM_VERIFY = 0x1b;
    const EVT_COME_USR_REFUSE = 0x10;
    const EVT_NO_COME_ID_EXPIRED = 4;
    const EVT_NO_COME_ID_INVALID = 1;
    const EVT_NO_COME_ID_PROHIBIT = 2;
    const EVT_NO_COME_ID_STOP = 3;
    const EVT_NO_COME_PROHIBIT = 8;
    const EVT_NOT_FIXED = 100;
    const EVT_TURNSTILE_CRUSH = 0x72;
    const MASK_0 = 0;
    const MASK_1 = 1;

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
        return 'enterevents';
    }

    /**
     * @inheritdoc
     * @return EventQuery
     */
    public static function qb()
    {
        $qb = new EventQuery(self::getConnection());
        return $qb->table(self::tableName());
    }
}
