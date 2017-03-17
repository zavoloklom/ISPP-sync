<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class Synchronization
 *
 * @package zavoloklom\ispp\sync\src
 */
class Synchronization
{

  /** @var SlackNotification */
  public $notify;

  /** @var QueryBuilderHandler */
  private $local_connection;

  /** @var QueryBuilderHandler */
  private $web_connection;

  /**
   * Synchronization constructor.
   * @param array $config
   */
  public function __construct(array $config)
  {
    // Инициализация уведомлений
    $this->notify = new SlackNotification($config);

    // Установка соединения с локальным сервером
    $localConnect = false;
    try {
      $local_connection = new Connection($config['local_server']['adapter'], $config['local_server']['options'], 'local');
      $this->local_connection = new QueryBuilderHandler($local_connection);
      echo "Соединение с сервером ИС ПП успешно установлено\n";
      $localConnect = true;
    } catch (\Exception $e) {
      echo 'Не удалось установить соединение с сервером ИС ПП: ',  $e->getMessage(), "\n";
    }

    // Установка соединения с веб сервером
    $serverConnect = false;
    try {
      $web_connection = new Connection($config['web_server']['adapter'], $config['web_server']['options'], 'web');
      $this->web_connection = new QueryBuilderHandler($web_connection);
      echo "Соединение с веб сервером успешно установлено\n";
      $serverConnect = true;
    } catch (\Exception $e) {
      echo 'Не удалось установить соединение с веб сервером: ',  $e->getMessage(), "\n";
    }

    if (($localConnect && $serverConnect) === false) {
      $this->notify->connectionError($localConnect, $serverConnect);
      exit("Синхронизация невозможна\n");
    };
  }

  /**
   * Groups synchronization
   */
  public function groups()
  {
    echo "\nСинхронизация идентификаторов групп\n";

    $query = $this->local_connection
      ->table('clients_groups')
      ->select(['clients_groups.IdOfClientsGroup', 'clients_groups.Name'])
      ->where('clients_groups.GroupType', '=', 1);

    $groups = $query->get();

    $errors = 0;
    foreach ($groups as $group) {
      try {
        //$this->serverDb->createCommand("UPDATE `ispp_group` SET `system_id` = ".$group['IdOfClientsGroup']." WHERE `ispp_group`.`name` = '".$group['Name']."'")->execute();
        echo ".";
      } catch (\Exception $e) {
        echo "X";
        $errors++;
      }
    }

    echo "\n\n Количество групп ".$query->count();

    // Запись в таблицу синхронизаций
    try {
      //$this->serverDb->createCommand()->insert('{{%ispp_sync}}', ['action'=>'update-groups', 'errors'=> $errors, 'datetime'=>date("Y-m-d H:i:s")])->execute();
      echo "\nЗапись в таблицу синхронизаций прошла успешно";
    } catch (\Exception $e) {
      echo "\nЗапись в таблицу синхронизаций не удалась";
    }

    echo "\nСинхронизация идентификаторов групп выполнена\n";
  }

  /**
   * Students synchronization
   */
  public function students()
  {
    echo "Students Action";
  }

  /**
   * Events synchronization
   */
  public function events()
  {
    echo "Events Action";
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

}

