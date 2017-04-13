<?php

use Codeception\Util\Fixtures;

/**
 * Class SynchronizationStudentsFunctionalTest
 */
class SynchronizationStudentsFunctionalTest extends \Codeception\Test\Unit
{
    /** @var \FunctionalTester */
    protected $tester;

    protected function _before()
    {
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
     * Должна отсутствовать определенная запись (учитель, родитель, дошкольник, выбывший ученик, ученик из удаленного класса)
     */
    public function testStudentsActionInsertDataToWebServer()
    {
        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->students();

        // Проверки
        $this->tester->seeNumRecords(13, 'ispp_iseduc_test.ispp_student');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1001]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1017]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1021]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1019]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1014]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1015]);
    }

    /**
     * Есть записи в ИС ПП
     * Есть корректные и некорректные записи в веб версии (неправильные ИД) записи в веб версии
     * Вызывается метод students
     * Должно быть нужное количество в веб версии
     * Должна присутствовать определенная запись (ученик)
     * Должна отсутствовать определенная запись (учитель, родитель, дошкольник, выбывший ученик, ученик из удаленного класс)
     */
    public function testStudentsActionUpdateDataOnWebServer()
    {
        // Начальный набор
        for ($i=1; $i<=13; $i++) {
            $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1000+$i, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname']);
        }

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->students();

        // Проверки
        $this->tester->seeNumRecords(13, 'ispp_iseduc_test.ispp_student');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1010]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1017]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1021]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1019]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1014]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1015]);
    }

    /**
     * Есть записи в ИС ПП
     * Отсутствуют некоторые ученики в веб версии
     * Вызывается метод students
     * Должно быть нужное количество в веб версии
     * Должна присутствовать определенная запись (ученик)
     * Должна отсутствовать определенная запись (учитель, родитель, дошкольник, выбывший ученик, ученик из удаленного класс)
     */
    public function testStudentsActionUpdateAndInsertDataToWebServer()
    {
        // Начальный набор
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1010, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname']);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1001, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname']);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1009, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname']);

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->students();

        // Проверки
        $this->tester->seeNumRecords(13, 'ispp_iseduc_test.ispp_student');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1010, 'system_group_id'=>1000000010]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1017]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1021]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1019]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1014]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1015]);
    }

    /**
     * Есть записи в ИС ПП
     * Есть некорректные записи в веб версии (ученики, которые уже ушли)
     * Вызывается метод students
     * Должно быть нужное количество в веб версии
     * Должна присутствовать определенная запись (ученик)
     * Должна присутствовать запись из начального набора, но со статусом 'неактивен'
     * Должна отсутствовать определенная запись (учитель, родитель, дошкольник, выбывший ученик, ученик из удаленного класс)
     */
    public function testStudentsActionHideUnnecessaryDataOnWebServer()
    {
        // Начальный набор
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1040, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname', 'state'=>1]);

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->students();

        // Проверки
        $this->tester->seeNumRecords(14, 'ispp_iseduc_test.ispp_student');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1010]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1040, 'state'=>0]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1017]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1021]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1019]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1014]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1015]);
    }

    /**
     * Есть записи в ИС ПП
     * Присутсвуют некоторые ученики в веб версии (с наличием и отсутсвием фотографий)
     * Вызывается метод students
     * Для записи у которой уже помечено что есть фото ничего не меняется
     * У определенных записей должно быть помечено, что фотография присутствует
     * У определенных записей должно быть помечено, что фотография отсутсвует
     */
    public function testStudentsActionCheckStudentPhoto()
    {
        // Начальный набор
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1004, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname', 'photo'=>0]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1011, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname', 'photo'=>0]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1013, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname', 'photo'=>1]);

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->students();

        // Проверки
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1004, 'photo'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1007, 'photo'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1008, 'photo'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1009, 'photo'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1013, 'photo'=>1]);

        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1001, 'photo'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1011, 'photo'=>0]);
    }

    /**
     * Есть записи в ИС ПП
     * Нет записей в веб версии
     * Вызывается метод students
     * Для записи у которой уже помечено что есть уведомления ничего не меняется
     * У определенных записей должно быть помечено, что уведомления включены
     * У определенных записей должно быть помечено, что уведомления выключены
     */
    public function testStudentsActionCheckStudentNotificationsOnInsert()
    {
        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->students();

        // Проверки
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1001, 'notify'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1002, 'notify'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1003, 'notify'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1004, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1005, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1006, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1007, 'notify'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1008, 'notify'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1009, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1010, 'notify'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1011, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1012, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1013, 'notify'=>0]);
    }

    /**
     * Есть записи в ИС ПП
     * Присутсвуют некоторые ученики в веб версии (с включенными и выключенными уведомлениями)
     * Вызывается метод students
     * Для записи у которой уже помечено что есть уведомления ничего не меняется
     * У определенных записей должно быть помечено, что уведомления включены
     * У определенных записей должно быть помечено, что уведомления выключены
     */
    public function testStudentsActionCheckStudentNotificationsOnUpdate()
    {
        // Начальный набор
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1001, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname', 'notify'=>1]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1004, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname', 'notify'=>0]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1013, 'system_group_id'=>1000000007, 'name'=>'name', 'middlename'=>'middlename', 'lastname'=>'lastname', 'notify'=>0]);

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->students();

        // Проверки
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1001, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1004, 'notify'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_student', ['system_id'=>1013, 'notify'=>0]);
    }
}
