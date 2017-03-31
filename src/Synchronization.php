<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use zavoloklom\ispp\sync\src\models\ClientsGroup;
use zavoloklom\ispp\sync\src\models\IsppGroup;

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
  const ACTION_ALL      = 'all';

  /** @var boolean */
  public $notificationEnabled = false;

  /** @var SlackNotification */
  public $notification;

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
    echo 'Синхронизация идентификаторов групп', PHP_EOL, PHP_EOL;

    // Выборка нужных групп из таблицы ИС ПП
    $localGroupsModel = new ClientsGroup();
    $localGroups = $localGroupsModel::qb()
      ->select([
        'IdOfClientsGroup',
        'Name'
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
          ->select('*')
          ->where('name', '=', $localGroup->Name)
          ->get();

        if ($webGroupsCount && $webGroup) {
          $webGroupsModel::qb()
            ->where('ispp_group.name', '=', $localGroup->Name)
            ->update([
              'system_id' => $localGroup->IdOfClientsGroup,
              'modified'  => date("Y-m-d H:i:s"),
              'state'     => IsppGroup::STATE_ACTIVE
            ]);
          $updatedGroupsCount++;
        } else {
          $webGroupsModel::qb()
            ->insert([
              'system_id' => $localGroup->IdOfClientsGroup,
              'name'      => $localGroup->Name,
              'created'   => date("Y-m-d H:i:s"),
              'state'     => IsppGroup::STATE_ACTIVE
            ]);
          $createdGroupsCount++;
        }
        echo '.';
      } catch (\Exception $e) {
        echo 'X';
        $errors++;
      }
    }
    echo PHP_EOL;

    // Посчитать количество скрытых групп на момент окончания
    $inactiveWebGroupsFinishCount = $webGroupsModel::qb()->inactive()->count();
    $hiddenGroupsCount = ($inactiveWebGroupsFinishCount-$inactiveWebGroupsStartCount);

    // Отчет о синхронизации
    echo 'Синхронизация идентификаторов групп выполнена.', PHP_EOL;
    echo 'Общее количество групп ', $localGroupsCount, PHP_EOL;
    echo 'Количество созданных групп ', $createdGroupsCount, PHP_EOL;
    echo 'Количество обновленных групп ', $updatedGroupsCount, PHP_EOL;
    echo 'Количество скрытых групп ', $hiddenGroupsCount, PHP_EOL;

    // Отправка уведомления
    if ($this->notificationEnabled) {
      $this->notification->sendGroupsSynchronizationInfo($createdGroupsCount, $updatedGroupsCount, $hiddenGroupsCount);
    }

    // Запись в таблицу синхронизаций
    //$this->logSynchronizationInfo('update-groups', $department_id, $errors);
  }


  /**
   * Students synchronization
   */
  public function students()
  {
    return 'Students Action';
  }

  /**
   * Events synchronization
   */
  public function events()
  {
    echo 'Events Action';
  }

  /**
   * All synchronization
   */
  public function all()
  {
    $this->groups();
    $this->students();
    $this->events();
  }


  /**
   * @param string  $action
   * @param integer $department_id
   * @param integer $errors
   */
  private function logSynchronizationInfo($action, $department_id, $errors = 0)
  {
    // Запись в таблицу синхронизаций
    try {
      //$this->serverDb->createCommand()->insert('{{%ispp_sync}}', ['action'=>'update-groups', 'errors'=> $errors, 'datetime'=>date('Y-m-d H:i:s')])->execute();
      echo 'Запись в таблицу синхронизаций прошла успешно', PHP_EOL;
    } catch (\Exception $e) {
      echo 'Запись в таблицу синхронизаций не удалась', PHP_EOL;
    }
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
      self::ACTION_EVENTS,
      self::ACTION_ALL
    ];
  }

}

