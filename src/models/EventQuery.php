<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src\models;

use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class EventQuery
 *
 * @package zavoloklom\ispp\sync\src\models
 */
class EventQuery extends QueryBuilderHandler
{
    public function doneByStudents()
    {
        return $this
            ->leftJoin(Client::tableName(), Event::tableName().'.IdOfClient', '=', Client::tableName().'.IdOfClient')
            ->leftJoin(ClientsGroup::tableName(), Client::tableName().'.ClientsGroupId', '=', ClientsGroup::tableName().'.IdOfClientsGroup')
            ->where(ClientsGroup::tableName().'.GroupType', '=', ClientsGroup::TYPE_CLASS);
    }

    public function turnstileEvents()
    {
        return $this->whereIn(Event::tableName().'.PassDirection', [Event::DIRECTION_PassEnter, Event::DIRECTION_PassExit, Event::DIRECTION_TwicePassEnter, Event::DIRECTION_TwicePassExit]);
    }


}