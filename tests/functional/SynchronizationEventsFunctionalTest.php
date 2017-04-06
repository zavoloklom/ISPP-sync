<?php

use zavoloklom\ispp\sync\src\Synchronization;
use Codeception\Util\Fixtures;

/**
 * Class SynchronizationEventsFunctionalTest
 */
class SynchronizationEventsFunctionalTest extends \Codeception\Test\Unit
{
  /** @var \FunctionalTester */
  protected $tester;

  protected function _before()
  {
    // Очистить таблицы
    $connection = new \Pixie\Connection(CONFIG['web_server']['adapter'], CONFIG['web_server']['options']);
    $qb = new \Pixie\QueryBuilder\QueryBuilderHandler($connection);
    $qb->query("TRUNCATE ispp_event");
    $qb->query("TRUNCATE ispp_sync");
  }

  protected function _after()
  {

  }

  /**
   * Есть записи в ИС ПП
   * Нет записей в веб версии
   * Вызывается метод events
   * Должно быть нужное количество в веб версии
   * Должна присутствовать запись (Проход ученика в этом учебном году)
   * Должна присутствовать запись (Проход с пульта охраны в этом учебном году)
   * Должна отсутствовать запись (Проход ученика в прошлом учебном году)
   * Должна отсутствовать запись (Проход учителя в этом учебном году)
   */
  public function testEventsActionInsertDataToEmptyWebServer()
  {
    // Выполнение команды
    $sync = Fixtures::get('sync');
    $sync->events();

    // Проверки
    $this->tester->seeNumRecords(18, 'ispp_iseduc_test.ispp_event');
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10004]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10015]);
    $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10001]);
    $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10012]);
  }

  /**
   * Есть записи в ИС ПП
   * Есть запись в веб таблице синхронизаций
   * Вызывается метод events
   * Должно быть нужное количество в веб версии
   * Должна присутствовать запись (Проход ученика после даты последней синхронизации)
   * Должна отсутствовать запись (Проход ученика до даты последней синхронизации)
   */
  public function testEventsActionInsertDataToWebServerWithData()
  {
    // Начальный набор
    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_sync', ['action'=>'update-events_0', 'errors'=>0, 'datetime'=>'2016-09-02 08:51:31']);

    // Выполнение команды
    $sync = Fixtures::get('sync');
    $sync->events();

    // Проверки
    $this->tester->seeNumRecords(12, 'ispp_iseduc_test.ispp_event');
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10008]);
    $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10007]);
  }

  /**
   * Есть записи в ИС ПП
   * Присутсвуют некоторые события в веб версии (с наличием и отсутсвием верного и неверного признака опозданий)
   * Вызывается метод events
   * Для записей, которые уже есть в БД ничего не должно поменятся
   * Опозданием помечено событие - Ученик первый раз вошел после 8:30 и до 10:30
   * Опозданием помечено событие - Ученик первый раз вышел после 8:30 и до 10:30
   * Опозданием помечено событие - Ученик первый раз вошел (повторный вход) после 8:30 и до 10:30
   * Опозданием помечено событие - Ученик первый раз вышел (повторный выход) после 8:30 и до 10:30
   * Опозданием помечено событие - Охранник пропустил ученика после 8:30 и до 10:30
   * Опозданием помечено событие - Охранник выпустил ученика после 8:30 и до 10:30
   * Событие не является опозданием - Ученик пришел до 8:30
   * Событие не является опозданием - Ученик прошел после 8:30 и до 10:30 в тот же день, когда уже входил в положенное время
   * Событие не является опозданием - Ученик первый раз пришел после уроков
   * Событие не является опозданием - Ученик 'опоздал' в воскресенье
   * Событие не является опозданием - Ученик 'опоздал' в праздничный день
   * Событие не является опозданием - Ученик 'опоздал' в каникулы
   * Событие не является опозданием - Охранник пропустил ученика до 8:30
   * Событие не является опозданием - Охранник выпустил ученика до 8:30
   */
  public function testEventsActionCheckStudentLatecome()
  {
    // Начальный набор
    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1000, 'student_system_id'=>1001, 'branch_id'=>0, 'direction'=>0, 'code'=>16, 'datetime'=>'2016-09-12 08:15:01', 'type'=>0]);
    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1001, 'student_system_id'=>1002, 'branch_id'=>0, 'direction'=>0, 'code'=>16, 'datetime'=>'2016-09-12 08:15:02', 'type'=>1]);
    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1002, 'student_system_id'=>1003, 'branch_id'=>0, 'direction'=>0, 'code'=>16, 'datetime'=>'2016-09-12 09:15:03', 'type'=>0]);
    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1003, 'student_system_id'=>1004, 'branch_id'=>0, 'direction'=>0, 'code'=>16, 'datetime'=>'2016-09-12 09:15:04', 'type'=>1]);

    // Выполнение команды
    $sync = Fixtures::get('sync');
    $sync->events();

    // Проверки
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1000, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1001, 'type'=>1]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1002, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>1003, 'type'=>1]);

    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10004, 'type'=>1]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10005, 'type'=>1]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10007, 'type'=>1]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10008, 'type'=>1]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10016, 'type'=>1]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10017, 'type'=>1]);

    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10003, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10002, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10006, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10009, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10010, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10011, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10015, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10014, 'type'=>0]);
  }

  /**
   * Есть записи в ИС ПП
   * Присутсвуют некоторые события в веб версии (с наличием и отсутсвием признака опозданий)
   * Вызывается метод events
   * Событие не является опозданием - Ученик уже был в школе в этот день
   * Событие не является опозданием - Ученик уже был в школе в этот день
   */
  public function testEventsActionCheckStudentLatecomeAfterSynchronizationAtTheSameDay()
  {
    // Начальный набор
    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10018, 'student_system_id'=>1001, 'branch_id'=>0, 'direction'=>0, 'code'=>17, 'datetime'=>'2016-10-19 08:15:00', 'type'=>0]);
    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10020, 'student_system_id'=>1002, 'branch_id'=>0, 'direction'=>0, 'code'=>17, 'datetime'=>'2016-10-19 09:10:00', 'type'=>1]);

    $this->tester->haveInDatabase('ispp_iseduc_test.ispp_sync', ['action'=>'update-events_0', 'errors'=>0, 'datetime'=>'2016-10-19 09:00:00']);

    // Выполнение команды
    $sync = Fixtures::get('sync');
    $sync->events();

    // Проверки
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10019, 'type'=>0]);
    $this->tester->seeInDatabase('ispp_iseduc_test.ispp_event', ['system_id'=>10021, 'type'=>0]);
  }
}