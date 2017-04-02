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
use zavoloklom\ispp\sync\src\models\IsppGroup;
use zavoloklom\ispp\sync\src\models\IsppStudent;

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

  /** @var integer The Id of department for synchronization log table */
  public $department_id = 0;

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
          ->find($localGroup->Name, 'name');

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
    echo 'Ошибок при соединении с БД ', $errors, PHP_EOL;

    // Отправка уведомления
    if ($this->notificationEnabled) {
      $this->notification->sendGroupsSynchronizationInfo($createdGroupsCount, $updatedGroupsCount, $hiddenGroupsCount, $errors);
    }

    // Запись в таблицу синхронизаций
    $this->logSynchronizationInfo('update-groups', $this->department_id, $errors);
  }


  /**
   * Students synchronization
   */
  public function students()
  {
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
        echo '['.date('Y-m-d H:i:s').'] ID '.$localStudent->ClientsGroupId.' - Ошибка подключения к БД';
        $errors++;
      }
    }
    echo PHP_EOL;

    // Посчитать количество скрытых учеников на момент окончания синхронизации
    $inactiveWebStudentsFinishCount = $webModel::qb()->inactive()->count();
    $hiddenStudentsCount = ($inactiveWebStudentsFinishCount-$inactiveWebStudentsStartCount);

    // Отчет о синхронизации
    echo 'Синхронизация идентификаторов групп выполнена.', PHP_EOL;
    echo 'Общее количество учеников ', $localStudentsCount, PHP_EOL;
    echo 'Количество созданных учеников ', $createdDataCount, PHP_EOL;
    echo 'Количество обновленных учеников ', $updatedDataCount, PHP_EOL;
    echo 'Количество скрытых учеников ', $hiddenStudentsCount, PHP_EOL;
    echo 'Ошибок при соединении с БД ', $errors, PHP_EOL;

    // Отправка уведомления
    if ($this->notificationEnabled) {
      //$this->notification->sendStudentsSynchronizationInfo($createdDataCount, $updatedDataCount, $hiddenStudentsCount, $errors);
    }

    // Запись в таблицу синхронизаций
    $this->logSynchronizationInfo('update-students', $this->department_id, $errors);
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
          echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' без контактов';
        }
        if ($parent->connection_state == 1) {
          $parent_connection = 0;
          echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' помеченная удаленной';
        }
        if ($parent->connection_disabled == 1) {
          $parent_connection = 0;
          echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' помеченная устаревшей';
        }
        if ($parent->group_system_id == 1100000060 || $parent->group_system_id == 1100000070 || $parent->group_system_id == 1100000080) {
          $parent_connection = 0;
          echo '['.date('Y-m-d H:i:s').'] ID '.$student_system_id.' - Имеется связь с родителем '.$parent->id.' из группы выбывшие/удаленные/перемещенные';
        }
        $result = $result || $parent_connection;
      }
    }
    return $result;
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

