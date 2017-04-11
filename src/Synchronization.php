<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use zavoloklom\ispp\sync\src\models\Client;
use zavoloklom\ispp\sync\src\models\ClientsGroup;
use zavoloklom\ispp\sync\src\models\Event;
use zavoloklom\ispp\sync\src\models\IsppEvent;
use zavoloklom\ispp\sync\src\models\IsppGroup;
use zavoloklom\ispp\sync\src\models\IsppStudent;
use zavoloklom\ispp\sync\src\models\IsppSync;

/**
 * Class Synchronization
 *
 * @package zavoloklom\ispp\sync\src
 */
class Synchronization
{

    const ACTION_GROUPS   = 'groups';
    const ACTION_STUDENTS = 'students';
    const ACTION_EVENTS   = 'events';

    /** @var boolean */
    public $notificationEnabled = false;

    /** @var SlackNotification */
    public $notification;

    /** @var integer The Id of department for synchronization log table */
    public $department_id = 0;

    /** @var  Education object with schedule */
    public $education;

    /**
     * Тестирование установки соединения
     *
     * @param array $config
     * @return bool
     */
    private function testConnection(array $config = [])
    {
        if (array_key_exists('adapter', $config) && array_key_exists('options', $config)) {
            try {
                $connection = new Connection($config['adapter'], $config['options']);
                $qb = new QueryBuilderHandler($connection);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Тестирование соединения с веб сервером и сервером ИС ПП
     * Может проводится отдельно или перед выполнением других команд.
     *
     * @param array $localConfig
     * @param array $webConfig
     * @return bool
     * @throws \Exception
     */
    public function testConnections(array $localConfig = [], array $webConfig = [])
    {
        // Установка соединения с веб сервером
        $serverConnect = $this->testConnection($localConfig);
        echo $serverConnect ? 'Соединение с веб сервером успешно установлено' : 'Не удалось установить соединение с веб сервером';
        echo PHP_EOL;

        // Установка соединения с локальным сервером ИС ПП
        $localConnect = $this->testConnection($webConfig);
        echo $localConnect ? 'Соединение с сервером ИС ПП успешно установлено' : 'Не удалось установить соединение с сервером ИС ПП';
        echo PHP_EOL;

        // Проверка возможности синхронизации
        if (($localConnect && $serverConnect) === false) {
            if ($this->notificationEnabled) {
                $this->notification->sendConnectionError($localConnect, $serverConnect);
            }
            throw new \Exception('Синхронизация невозможна.');
        }
        return true;
    }

    /**
     * Groups synchronization
     */
    public function groups()
    {
        $script_start = microtime(true);
        echo 'Синхронизация идентификаторов групп', PHP_EOL, PHP_EOL;

        // Выборка нужных групп из таблицы ИС ПП
        $localGroupsModel = new ClientsGroup();
        $localGroups = $localGroupsModel::qb()
            ->select([
                ClientsGroup::tableName().'.IdOfClientsGroup',
                ClientsGroup::tableName().'.Name',
                ClientsGroup::tableName().'.DisablePlanCreation'
            ])
            ->schoolClasses()
            ->get();
        $localGroupsCount = $localGroupsModel::qb()->schoolClasses()->count();

        // Количество групп в веб версии
        $webGroupsModel = new IsppGroup();
        $webGroupsCount = $webGroupsModel::qb()->count();

        // Посчитать количество скрытых групп на текущий момент
        $inactiveWebGroupsStartCount = $webGroupsModel::qb()->inactive()->count();

        // Установить видимость 0 перед синхронизацией
        $webGroupsModel::qb()->update(['state' => IsppGroup::STATE_INACTIVE]);

        $errors = 0;
        $createdGroupsCount = 0;
        $updatedGroupsCount = 0;
        foreach ($localGroups as $localGroup) {
            try {
                $webGroup = $webGroupsModel::qb()
                    ->find($localGroup->Name, 'name');

                if ($webGroupsCount && $webGroup) {
                    $webGroupsModel::qb()
                        ->where('ispp_group.name', '=', $localGroup->Name)
                        ->update([
                            'system_id' => $localGroup->IdOfClientsGroup,
                            'modified'  => date("Y-m-d H:i:s"),
                            'branch_id' => ($webGroup->branch_id != NULL) ? $webGroup->branch_id : ($localGroup->DisablePlanCreation == 1 ? NULL : $this->department_id),
                            'state'     => IsppGroup::STATE_ACTIVE
                        ]);
                    $updatedGroupsCount++;
                    //echo '['.date('Y-m-d H:i:s').'] ID '.$localGroup->IdOfClientsGroup.' - Информация обновлена';
                } else {
                    $webGroupsModel::qb()
                        ->insert([
                            'system_id' => $localGroup->IdOfClientsGroup,
                            'name'      => $localGroup->Name,
                            'branch_id' => $localGroup->DisablePlanCreation == 1 ? NULL : $this->department_id,
                            'created'   => date("Y-m-d H:i:s"),
                            'state'     => IsppGroup::STATE_ACTIVE
                        ]);
                    $createdGroupsCount++;
                }
                //echo '['.date('Y-m-d H:i:s').'] ID '.$localGroup->IdOfClientsGroup.' - Информация добавлена';
            } catch (\Exception $e) {
                echo '['.date('Y-m-d H:i:s').'] ID '.$localGroup->IdOfClientsGroup.' - Ошибка подключения к БД', PHP_EOL;
                $errors++;
            }
        }
        echo PHP_EOL;

        // Информация по данному скрипту
        $script_finish = microtime(true);
        $script_execution_datetime = new \DateTime('@'.(int)($script_finish - $script_start));
        $script_time = $script_execution_datetime->format("i минут s секунд");
        $script_memory_peak = round(memory_get_peak_usage() / 1024 / 1024, 2);

        // Посчитать количество скрытых групп на момент окончания
        $inactiveWebGroupsFinishCount = $webGroupsModel::qb()->inactive()->count();
        $hiddenGroupsCount = ($inactiveWebGroupsFinishCount-$inactiveWebGroupsStartCount);

        // Отчет о синхронизации
        echo 'Синхронизация идентификаторов групп выполнена.', PHP_EOL;
        echo 'Общее количество групп ', $localGroupsCount, PHP_EOL;
        echo 'Количество созданных групп ', $createdGroupsCount, PHP_EOL;
        echo 'Количество обновленных групп ', $updatedGroupsCount, PHP_EOL;
        echo 'Количество скрытых групп ', $hiddenGroupsCount, PHP_EOL;
        echo 'Ошибок при соединении с БД ', $errors, PHP_EOL, PHP_EOL;

        echo 'Время выполнения синхронизации ', $script_time, PHP_EOL;
        echo 'Пиковое потребление памяти ', $script_memory_peak, ' Мб', PHP_EOL, PHP_EOL;

        // Отправка уведомления
        if ($this->notificationEnabled) {
            $this->notification->sendGroupsSynchronizationInfo($createdGroupsCount, $updatedGroupsCount, $hiddenGroupsCount, $errors, $script_time, $script_memory_peak);
            echo 'Уведомление в Slack отправлено', PHP_EOL;
        }

        // Запись в таблицу синхронизаций
        $this->logSynchronizationInfo('update-groups', $this->department_id, $errors);
    }

    /**
     * Students synchronization
     */
    public function students()
    {
        $script_start = microtime(true);
        echo 'Синхронизация идентификаторов учеников', PHP_EOL, PHP_EOL;

        // Нужно продумать как без особых усилий можно было бы обновлять статус ученика
        // $lastUpdate = $this->serverDb->createCommand("SELECT MAX(datetime) FROM ispp_sync WHERE action='update-students'")->queryScalar();
        // $lastUpdate = $lastUpdate ? $lastUpdate : date("Y-m-d H:i:s");

        // Инициализация моделей
        $localModel = new Client();
        $webModel   = new IsppStudent();

        // Выборка учащихся из таблицы ИС ПП
        $localStudentsQuery = $localModel::qb()
            ->select([
                Client::tableName().'.IdOfClient',
                Client::tableName().'.ClientsGroupId',
                Client::tableName().'.Name',
                Client::tableName().'.SecondName',
                Client::tableName().'.Surname',
                Client::tableName().'.mobile'
            ])
            ->select([Client::tableName().'.Image'=>'internal_img'])
            ->select(['clients_photo.ImageBytes'=>'external_img'])
            ->leftJoin('clients_photo', Client::tableName().'.IdOfClient', '=', 'clients_photo.IdOfClient')
            ->innerJoin(ClientsGroup::tableName(), 'ClientsGroupId', '=', 'IdOfClientsGroup')
            ->where(ClientsGroup::tableName().'.GroupType', '=', ClientsGroup::TYPE_CLASS);
        $localStudents      = $localStudentsQuery->get();
        $localStudentsCount = $localStudentsQuery->count();

        // Количество учащихся в веб версии
        $webStudentsCount = $webModel::qb()->count();

        // Посчитать количество скрытых групп на текущий момент
        $inactiveWebStudentsStartCount = $webModel::qb()->inactive()->count();

        // Установить видимость 0 перед синхронизацией
        $webModel::qb()->update(['state' => IsppStudent::STATE_INACTIVE]);

        $errors = 0;
        $createdDataCount = 0;
        $updatedDataCount = 0;
        foreach ($localStudents as $localStudent) {
            try {
                $webStudent = $webModel::qb()
                    ->find($localStudent->IdOfClient, 'system_id');

                if ($webStudentsCount && $webStudent) {
                    $webModel::qb()
                        ->where(IsppStudent::tableName().'.system_id', '=', $localStudent->IdOfClient)
                        ->update([
                            'system_group_id' => $localStudent->ClientsGroupId,
                            'name'            => $localStudent->Name,
                            'middlename'      => $localStudent->SecondName,
                            'lastname'        => $localStudent->Surname,
                            'photo'           => $webStudent->photo || ($localStudent->internal_img != NULL || $localStudent->external_img != NULL),
                            'notify'          => $webStudent->notify || $this->checkStudentNotifications($localStudent->IdOfClient, ($localStudent->mobile ? 1 : 0)),
                            'state'           => IsppStudent::STATE_ACTIVE
                        ]);
                    $updatedDataCount++;
                    //echo '['.date('Y-m-d H:i:s').'] ID '.$localStudent->ClientsGroupId.' - Информация обновлена';
                } else {
                    $webModel::qb()
                        ->insert([
                            'system_id'       => $localStudent->IdOfClient,
                            'system_group_id' => $localStudent->ClientsGroupId,
                            'name'            => $localStudent->Name,
                            'middlename'      => $localStudent->SecondName,
                            'lastname'        => $localStudent->Surname,
                            'photo'           => ($localStudent->internal_img != NULL || $localStudent->external_img != NULL),
                            'notify'          => $this->checkStudentNotifications($localStudent->IdOfClient, ($localStudent->mobile ? 1 : 0)),
                            'state'           => IsppStudent::STATE_ACTIVE
                        ]);
                    $createdDataCount++;
                    //echo '['.date('Y-m-d H:i:s').'] ID '.$localStudent->ClientsGroupId.' - Информация добавлена';
                }
            } catch (\Exception $e) {
                echo '['.date('Y-m-d H:i:s').'] ID '.$localStudent->ClientsGroupId.' - Ошибка подключения к БД', PHP_EOL;
                $errors++;
            }
        }
        echo PHP_EOL;

        // Информация по данному скрипту
        $script_finish = microtime(true);
        $script_execution_datetime = new \DateTime('@'.(int)($script_finish - $script_start));
        $script_time = $script_execution_datetime->format("i минут s секунд");
        $script_memory_peak = round(memory_get_peak_usage() / 1024 / 1024, 2);

        // Посчитать количество скрытых учеников на момент окончания синхронизации
        $inactiveWebStudentsFinishCount = IsppStudent::qb()->inactive()->count();
        $hiddenStudentsCount = ($inactiveWebStudentsFinishCount-$inactiveWebStudentsStartCount);

        // Отчет о синхронизации
        echo 'Синхронизация идентификаторов групп выполнена.', PHP_EOL;
        echo 'Общее количество учеников ', $localStudentsCount, PHP_EOL;
        echo 'Количество созданных учеников ', $createdDataCount, PHP_EOL;
        echo 'Количество обновленных учеников ', $updatedDataCount, PHP_EOL;
        echo 'Количество скрытых учеников ', $hiddenStudentsCount, PHP_EOL;
        echo 'Ошибок при соединении с БД ', $errors, PHP_EOL, PHP_EOL;

        echo 'Время выполнения синхронизации ', $script_time, PHP_EOL;
        echo 'Пиковое потребление памяти ', $script_memory_peak, ' Мб', PHP_EOL, PHP_EOL;

        // Отправка уведомления
        if ($this->notificationEnabled) {
            $this->notification->sendStudentsSynchronizationInfo($createdDataCount, $updatedDataCount, $hiddenStudentsCount, $errors, $script_time, $script_memory_peak);
            echo 'Уведомление в Slack отправлено', PHP_EOL;
        }

        // Запись в таблицу синхронизаций
        $this->logSynchronizationInfo('update-students', $this->department_id, $errors);
    }

    /**
     * Как это должно работать
     * Сначала мы перетаскиваем все события в локальную БД
     * На этом этапе отфильтровываются проходы не учеников, но это не обязательно - просто немного сокращаем количество
     *
     * Дальнейшая обработка идет в веб таблице из-за того, что есть возможность настривать индексы так как надо
     *
     * Выбираем даты в которые происходили события и делаем цикл по датам
     * Даты находятся в промежутке от последней до текущей синхронизации
     * Если даты не указаны нужно как-то указывать начальные данные связанные с текущим учебным годом
     * Дата события не является выходным (воскресение)
     * Дата события не является праздничным днем (массив)
     * Дата события не попадает в промежуток каникул (массив)
     *
     * Что является опозданием
     * Отсечь коды событий отвечающих за поднос карты к считывателю на пункте охраны и/или администратора, т.к. это точно не опоздание
     * Дата события = %Рассматриваемая дата%
     * Время события BETWEEN TIME('8:30:00') AND TIME('10:30:00')
     * Т.к. взаимодействие с этой системой будет происходить из разных ШО - необходимо брать события, которые относятся к текущему ШО (branch_id), чтобы не было двойной работы
     * И ученик не являтся тем, кто уже как-либо взаимодействовал с турникетов в эту дату в промежутке времени от BETWEEN TIME('6:30:00') AND TIME('8:29:59')
     */
    public function events()
    {
        $script_start = microtime(true);
        echo 'Синхронизация событий', PHP_EOL, PHP_EOL;

        $qb = new QueryBuilderHandler();
        $createdEventsCount = 0;
        $latecomeEventsCount = 0;

        // Текущее время для записи в таблицу синхронизаций
        $now = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
        $newUpdateDatetime = $now->format('Y-m-d H:i:s');

        // Время последнего апдейта
        $lastUpdateQuery = IsppSync::qb()
            ->select($qb->raw('MAX(`datetime`) AS datetime'))
            ->where('action', '=', 'update-events_'.$this->department_id);

        $lastUpdateDatetime = '2000-01-01 00:00:00';
        if ($lastUpdateQuery->count()>0) {
            $lastUpdateDatetime = $lastUpdateQuery->first()->datetime;
        } elseif($this->education) {
            $lastUpdateDatetime = $this->education->getYearStart();
        }

        // Все взаимодействия с турникетами между последним и текущим обновлением
        $localEventsQuery = Event::qb()
            ->select([
                Event::tableName().'.IdOfEnterEvent',
                Event::tableName().'.IdOfClient',
                Event::tableName().'.TurnstileAddr',
                Event::tableName().'.PassDirection',
                Event::tableName().'.EventCode',
                Event::tableName().'.EvtDateTime'
            ])
            ->turnstileEvents()
            ->doneByStudents()
            ->whereNotNull(Event::tableName().'.IdOfClient')
            ->whereNotNull(Event::tableName().'.EvtDateTime')
            ->whereBetween(Event::tableName().'.EvtDateTime', $lastUpdateDatetime, $newUpdateDatetime)
            ->orderBy(Event::tableName().'.EvtDateTime', 'ASC');

        // Дальнейшее имеет смысл только если есть такие записи
        if ($localEventsQuery->count() > 0) {

            // Нужно записать эти данные в веб версию
            $localEvents = $localEventsQuery->get();
            $dataForInsert = [];
            foreach ($localEvents as $localEvent) {
                $dataForInsert[] = [
                    'system_id'         => $localEvent->IdOfEnterEvent,
                    'student_system_id' => $localEvent->IdOfClient,
                    'turnstile'         => $localEvent->TurnstileAddr,
                    'direction'         => $localEvent->PassDirection,
                    'code'              => $localEvent->EventCode,
                    'datetime'          => $localEvent->EvtDateTime,
                    'branch_id'         => $this->department_id
                ];
            }

            $insertIds = IsppEvent::qb()->insert($dataForInsert);
            $createdEventsCount = count($insertIds);

            // Выборка дат добавленных событий
            $dateInterval = IsppEvent::qb()
                ->select($qb->raw('DATE(`datetime`) AS date'))
                ->select($qb->raw('COUNT(*) AS `events`'))
                ->whereIn('id', $insertIds)
                ->groupBy('date')
                ->get();

            // Пометка событий опозданиями в цикле по дням
            // @see http://sqlinfo.ru/articles/info/18.html
            foreach ($dateInterval as $day) {
                // Дальнейшее имеет смысл, только если дата является учебным днем
                // Или если о расписании ничего неизвестно
                if (($this->education != NULL && $this->education->checkDateAsHoliday($day->date) == true) == false) {
                    $latecomes = IsppEvent::qb()
                        ->selectDistinct([
                            IsppEvent::tableName().'.student_system_id'
                        ])
                        ->select($qb->raw("MIN(`ispp_event`.`datetime`) AS datetime"))
                        ->select($qb->raw("SUBSTR(MIN(CONCAT(`ispp_event`.`datetime`, `ispp_event`.`id`)), 20) as `id`"))
                        ->where(IsppEvent::tableName().'.branch_id', '=', $this->department_id)
                        ->where($qb->raw("DATE(`ispp_event`.`datetime`) = '".$day->date."'"))
                        ->having($qb->raw("MIN(TIME(`ispp_event`.`datetime`))"), 'BETWEEN', $qb->raw("TIME('8:30:00') AND TIME('10:30:00')"))
                        ->groupBy(IsppEvent::tableName().'.student_system_id')
                        ->get();

                    // Взять только ID тех событий, которые являются первыми
                    $latecomesIds = [];
                    foreach ($latecomes as $latecome) {
                        $latecomesIds[] = $latecome->id;
                    }
                    $latecomeEventsCount += count($latecomesIds);

                    // Установить опозданиям соответствуцющий тип
                    if ($latecomesIds) {
                        IsppEvent::qb()
                            ->whereIn('id', $latecomesIds)
                            ->update(['type' => IsppEvent::TYPE_LATECOME]);
                    }
                }
            }
        }

        // Информация по данному скрипту
        $script_finish = microtime(true);
        $script_execution_datetime = new \DateTime('@'.(int)($script_finish - $script_start));
        $script_time = $script_execution_datetime->format("i минут s секунд");
        $script_memory_peak = round(memory_get_peak_usage() / 1024 / 1024, 2);

        // Отчет о синхронизации
        echo 'Синхронизация событий выполнена.', PHP_EOL;
        echo 'Количество созданных событий ', $createdEventsCount, PHP_EOL;
        echo 'Количество опозданий ', $latecomeEventsCount, PHP_EOL, PHP_EOL;

        echo 'Время выполнения синхронизации ', $script_time, PHP_EOL;
        echo 'Пиковое потребление памяти ', $script_memory_peak, ' Мб', PHP_EOL, PHP_EOL;


        // Отправка уведомления
        if ($this->notificationEnabled) {
            $this->notification->sendEventsSynchronizationInfo($createdEventsCount, $latecomeEventsCount, $script_time, $script_memory_peak);
            echo 'Уведомление в Slack отправлено', PHP_EOL;
        }

        // Запись в таблицу синхронизаций
        $this->logSynchronizationInfo('update-events', $this->department_id, 0, $newUpdateDatetime);

    }

    /**
     * Вынесено в основной запрос для оптимиации
     *
     * @param $student_system_id
     * @return int
     */
    private function checkStudentPhoto($student_system_id)
    {
        $photo = Client::qb()
            ->select([Client::tableName().'.IdOfClient'=>'id'])
            ->select([Client::tableName().'.Image'=>'internal_img'])
            ->select(['clients_photo.ImageBytes'=>'external_img'])
            ->leftJoin('clients_photo', Client::tableName().'.IdOfClient', '=', 'clients_photo.IdOfClient')
            ->where(Client::tableName().'.IdOfClient', '=', $student_system_id)
            ->first();

        if ($photo->internal_img != NULL || $photo->external_img != NULL) {
            return 1;
        }
        return 0;
    }


    /**
     * @param $student_system_id
     * @param bool $studentHasMobile
     * @return bool
     */
    private function checkStudentNotifications($student_system_id, $studentHasMobile = false)
    {
        $result = $studentHasMobile;

        //$notify = Client::qb()->find($student_system_id, 'IdOfClient');
        //if ($notify && $notify->mobile) {$result = true;}

        $parentsQuery = Client::qb()
            ->select([
                Client::tableName().'.IdOfClient'     =>'id',
                Client::tableName().'.ClientsGroupId' =>'group_system_id',
                Client::tableName().'.phone',
                Client::tableName().'.mobile',
                Client::tableName().'.email',
            ])
            ->select([
                'guardians.DeletedState'  =>'connection_state',
                'guardians.IsDisabled'    =>'connection_disabled',
            ])
            ->leftJoin('guardians', Client::tableName().'.IdOfClient', '=', 'guardians.GuardianClientId')
            ->where('guardians.ChildClientId', '=', $student_system_id);

        if ($parentsQuery->count() > 0) {
            $parents = $parentsQuery->get();
            foreach ($parents as $parent) {
                $parent_connection = 1;
                if (!$parent->mobile) {
                    $parent_connection = 0;
                    echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' без контактов', PHP_EOL;
                }
                if ($parent->connection_state == 1) {
                    $parent_connection = 0;
                    echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' помеченная удаленной', PHP_EOL;
                }
                if ($parent->connection_disabled == 1) {
                    $parent_connection = 0;
                    echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' помеченная устаревшей', PHP_EOL;
                }
                if ($parent->group_system_id == 1100000060 || $parent->group_system_id == 1100000070 || $parent->group_system_id == 1100000080) {
                    $parent_connection = 0;
                    echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' из группы выбывшие/удаленные/перемещенные', PHP_EOL;
                }
                $result = $result || $parent_connection;
            }
        }
        return $result;
    }


    /**
     * @param $action
     * @param $department_id
     * @param int $errors
     * @param $datetime
     */
    private function logSynchronizationInfo($action, $department_id, $errors = 0, $datetime = NULL)
    {
        if (!$datetime) {
            $dt = new \DateTime("now", new \DateTimeZone('Europe/Moscow'));
            $datetime = $dt->format('Y-m-d H:i:s');
        }

        IsppSync::qb()->insert([
            'action'    => $action.'_'.$department_id,
            'errors'    => $errors,
            'datetime'  => $datetime
        ]);
    }

    /**
     * Return array of public actions for CLI
     *
     * @return array
     */
    public static function actionsArray()
    {
        return [
            self::ACTION_GROUPS,
            self::ACTION_STUDENTS,
            self::ACTION_EVENTS
        ];
    }

}

