<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use zavoloklom\ispp\sync\src\models\ISPPQuery;

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

    /**
     * Выборка нужных групп из таблицы ИС ПП
     *
     * GroupType - 0 (Пустые группы, администрация и т.п.)
     * GroupType - 1 (Учебные классы)
     * GroupType - 2 (Группы дет. сада)
     */
    $localGroups = $this->local_connection
      ->table('clients_groups')
      ->select([
        'clients_groups.IdOfClientsGroup',
        'clients_groups.Name'
      ])
      ->where('clients_groups.GroupType', '=', 1)
      ->get();
    // $localGroups = ClientGroup::qb()->select([)->class()->get();

    // Количество групп в веб версии
    $webGroupsCount = $this->web_connection->table('ispp_group')->count();
    // $webGroupsCount = IsppGroup::qb()->count();

    // Посчитать количество скрытых групп на текущий момент
    // Установить видимость 0 перед синхронизацией
    $hiddenWebGroupsCount = $this->web_connection->table('ispp_group')->where('state', '=', 0)->count();
    // $webGroupsCount = IsppGroup::qb()->hiddenScope()->count();


    $this->web_connection->table('ispp_group')->update(['state'=>0]);
    // $webGroupsCount = IsppGroup::qb()->update(['state'=>0]);

    $errors = 0;
    foreach ($localGroups as $localGroup) {
      try {
        $webGroup = $this->web_connection
          ->table('ispp_group')
          ->select('*')
          ->where('name', '=', $localGroup->Name)
          ->get();

        if ($webGroupsCount && $webGroup) {
          $this->web_connection
            ->table('ispp_group')
            ->where('ispp_group.name', '=', $localGroup->Name)
            ->update([
              'system_id' => $localGroup->IdOfClientsGroup,
              'modified'  => date("Y-m-d H:i:s"),
              'state'     => 1
            ]);
        } else {
          $this->web_connection
            ->table('ispp_group')
            ->insert([
              'system_id' => $localGroup->IdOfClientsGroup,
              'name'      => $localGroup->Name,
              'created'   => date("Y-m-d H:i:s")
            ]);
        }
        echo '.';
      } catch (\Exception $e) {
        echo 'X';
        $errors++;
      }
    }
    echo PHP_EOL;

    // Количество созданных групп
    // Количество обновленных групп
    // Количество скрытых групп в процессе синхронизации $hiddenWebGroupsCount
    //echo 'Количество групп ', $query->count(), PHP_EOL;

    echo 'Синхронизация идентификаторов групп выполнена', PHP_EOL;
  }


  private function updateSyncInfo($table, $name)
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

