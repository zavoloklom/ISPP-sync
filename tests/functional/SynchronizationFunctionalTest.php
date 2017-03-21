<?php

use Codeception\Util\Fixtures;
use zavoloklom\ispp\sync\src\Synchronization;

/**
 * Class SynchronizationFunctionalTest
 */
class SynchronizationFunctionalTest extends \Codeception\Test\Unit
{
  /** @var \FunctionalTester */
  protected $tester;

  protected function _before()
  {
    // Очистить таблицы
    $connection = new \Pixie\Connection('mysql', [
      'driver'    => 'mysql',
      'host'      => '127.0.0.1',
      'username'  => 'root',
      'password'  => '',
      'database'  => 'ispp-iseduc-test'
    ]);
    $qb = new \Pixie\QueryBuilder\QueryBuilderHandler($connection);
    $qb->query("TRUNCATE ispp_group");
  }

  protected function _after()
  {
  }

  /**
   * Есть записи в ИС ПП
   * Нет записей в веб версии
   * Вызывается метод groups
   * Должно быть нужное количество в веб
   * Должна присутствовать определенная запись (с нужным типом)
   * Должна отсутствовать определенная запись (с типом не равным нужному)
   * Должна появится запись в таблице синхронизаций (?)
   * Должно быть выведено определенное сообщение
   */
  public function testGroupsActionInsertDataToWebServer()
  {
    // Выполнение команды
    $sync = new Synchronization(Fixtures::get('config'));
    $sync->groups();

    // Проверки
    $this->tester->seeNumRecords(10, 'ispp-iseduc-test.ispp_group');
    $this->tester->seeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'6-Г']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'ДО2-Подготовительная №4']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'СДС Мордашова']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'5-О']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'Администрация']);
  }

  /**
   * Есть записи в ИС ПП
   * Есть корректные и некорректные записи в веб версии (неправильные ИД) записи в веб версии
   * Вызывается метод groups
   * Должно быть нужное количество в веб
   * Должна присутствовать определенная запись (с нужным типом)
   * Должна отсутствовать определенная запись (с типом не равным нужному)
   * Должна появится запись в таблице синхронизаций (?)
   * Должно быть выведено определенное сообщение (данные не нуждаются в синхронизации)
   */
  public function testGroupsActionUpdateDataToWebServer()
  {
    // Начальный набор
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '2-З', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '6-Г', 'system_id'=>1000000002]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '2-И', 'system_id'=>1000000003]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '5-Л', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '8-М', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '5-Г', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '5-Н', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '7-З', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '4-Е', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '3-В', 'system_id'=>9]);

    // Выполнение команды
    $sync = new Synchronization(Fixtures::get('config'));
    $sync->groups();

    // Проверки
    $this->tester->seeNumRecords(10, 'ispp-iseduc-test.ispp_group');
    $this->tester->seeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'6-Г', 'system_id'=>1000000002]);
    $this->tester->seeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'5-Л', 'system_id'=>1000000005]);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'ДО2-Подготовительная №4']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'СДС Мордашова']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'5-О']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'Администрация']);
  }

  /**
   * Есть записи в ИС ПП
   * Отсутствуют некоторые группы в веб
   * Вызывается метод groups
   * Должно быть нужное количество в веб
   * Должна присутствовать определенная запись (с нужным типом)
   * Должна отсутствовать определенная запись (с типом не равным нужному)
   * Должна появится запись в таблице синхронизаций (?)
   * Должно быть выведено определенное сообщение
   */
  public function testGroupsActionUpdateAndInsertNewGroupsToWebServer()
  {
    // Начальный набор
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '2-З', 'system_id'=>1000000001]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '6-Г', 'system_id'=>1000000002]);
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '2-И', 'system_id'=>1000000003]);

    // Выполнение команды
    $sync = new Synchronization(Fixtures::get('config'));
    $sync->groups();

    // Проверки
    $this->tester->seeNumRecords(10, 'ispp-iseduc-test.ispp_group');
    $this->tester->seeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'6-Г']);
    $this->tester->seeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'3-В']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'ДО2-Подготовительная №4']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'СДС Мордашова']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'5-О']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'Администрация']);
  }

  /**
   * Есть записи в ИС ПП
   * Есть некорректные записи в веб версии (лишние группы)
   * Вызывается метод groups
   * Должно быть нужное количество в веб
   * Должна присутствовать определенная запись (с нужным типом)
   * Должна отсутствовать определенная запись (с типом не равным нужному)
   * Должна появится запись в таблице синхронизаций (?)
   * Должно быть выведено определенное сообщение
   */
  public function testGroupsActionHideUnnecessaryGroupOnWebServer()
  {
    // Начальный набор
    $this->tester->haveInDatabase('ispp-iseduc-test.ispp_group', ['name' => '12-Я', 'system_id'=>1000000001]);

    // Выполнение команды
    $sync = new Synchronization(Fixtures::get('config'));
    $sync->groups();

    // Проверки
    $this->tester->seeNumRecords(11, 'ispp-iseduc-test.ispp_group');
    $this->tester->seeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'6-Г']);
    $this->tester->seeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'12-Я', 'state'=>0]);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'ДО2-Подготовительная №4']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'СДС Мордашова']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'5-О']);
    $this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_group', ['name'=>'Администрация']);
  }
}