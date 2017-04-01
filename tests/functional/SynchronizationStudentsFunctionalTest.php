<?php

use zavoloklom\ispp\sync\src\Synchronization;

/**
 * Class SynchronizationStudentsFunctionalTest
 */
class SynchronizationStudentsFunctionalTest extends \Codeception\Test\Unit
{
  /** @var \FunctionalTester */
  protected $tester;

  protected function _before()
  {
    // Очистить таблицы
    $connection = new \Pixie\Connection(CONFIG['web_server']['adapter'], CONFIG['web_server']['options']);
    $qb = new \Pixie\QueryBuilder\QueryBuilderHandler($connection);
    $qb->query("TRUNCATE ispp_student");
  }

  protected function _after()
  {
  }

  /**
   * Есть записи в ИС ПП
   * Нет записей в веб версии
   * Вызывается метод students
   * Должно быть нужное количество в веб версии
   * Должна присутствовать определенная запись (ученик)
   * Должна отсутствовать определенная запись (учитель, родитель, администрация, детсадовец, выбывший ученик)
   */
  public function testStudentsActionInsertDataToWebServer()
  {
    // Выполнение команды
    $sync = new Synchronization();
    $sync->students();

    // Проверки
    $this->tester->seeNumRecords(10, 'ispp-iseduc-test.ispp_student');
    //$this->tester->seeInDatabase('ispp-iseduc-test.ispp_student', ['name'=>'6-Г']);
    //$this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_student', ['name'=>'ДО2-Подготовительная №4']);
    //$this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_student', ['name'=>'СДС Мордашова']);
    //$this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_student', ['name'=>'5-О']);
    //$this->tester->dontSeeInDatabase('ispp-iseduc-test.ispp_student', ['name'=>'Администрация']);
  }

  /**
   * Есть записи в ИС ПП
   * Есть корректные и некорректные записи в веб версии (неправильные ИД) записи в веб версии
   * Вызывается метод students
   * Должно быть нужное количество в веб версии
   * Должна присутствовать определенная запись (ученик)
   * Должна отсутствовать определенная запись (учитель, родитель, администрация, детсадовец, выбывший ученик)
   */
  public function testStudentsActionUpdateDataOnWebServer()
  {

  }

  /**
   * Есть записи в ИС ПП
   * Отсутствуют некоторые ученики в веб версии
   * Вызывается метод students
   * Должно быть нужное количество в веб версии
   * Должна присутствовать определенная запись (ученик)
   * Должна отсутствовать определенная запись (учитель, родитель, администрация, детсадовец, выбывший ученик)
   */
  public function testStudentsActionUpdateAndInsertDataToWebServer()
  {

  }

  /**
   * Есть записи в ИС ПП
   * Есть некорректные записи в веб версии (ученики, которые уже ушли)
   * Вызывается метод students
   * Должно быть нужное количество в веб версии
   * Должна присутствовать определенная запись (ученик)
   * Должна отсутствовать определенная запись (учитель, родитель, администрация, детсадовец, выбывший ученик)
   */
  public function testStudentsActionHideUnnecessaryDataOnWebServer()
  {

  }
}