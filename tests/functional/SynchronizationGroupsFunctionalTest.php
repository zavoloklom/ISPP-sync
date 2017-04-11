<?php

use Codeception\Util\Fixtures;

/**
 * Class SynchronizationGroupsFunctionalTest
 */
class SynchronizationGroupsFunctionalTest extends \Codeception\Test\Unit
{
    /** @var \FunctionalTester */
    protected $tester;

    protected function _before()
    {
        // Очистить таблицы
        $connection = new \Pixie\Connection(CONFIG['web_server']['adapter'], CONFIG['web_server']['options']);
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
     * Должно быть нужное количество в веб версии
     * Должна присутствовать запись школьного класса (с GroupType == 1 )
     * Должна отсутствовать запись несуществующего класса
     * Должна отсутствовать запись несуществующей группы детского сада
     * Должна отсутствовать запись существующей группы детского сада
     * Должна отсутствовать запись с группой Администрации
     * У группы не исключенной из плана питания должно быть выставлено поле branch_id
     */
    public function testGroupsActionInsertDataToWebServer()
    {
        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->groups();

        // Проверки
        $this->tester->seeNumRecords(10, 'ispp_iseduc_test.ispp_group');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'6-Г']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'5-О']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'ДО2-Подготовительная №4']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'СДС Мордашова']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'Администрация']);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'4-Е', 'branch_id'=>0]);
    }

    /**
     * Есть записи в ИС ПП
     * Есть корректные и некорректные записи в веб версии (неправильные ИД) записи в веб версии
     * Вызывается метод groups
     * Должно быть нужное количество в веб версии
     * Должна присутствовать запись школьного класса с правильным ИД, который не изменился
     * Должна присутствовать запись школьного класса, у которой неправильный ИД изменен на правильный
     * Должна отсутствовать запись несуществующего класса
     * Должна отсутствовать запись несуществующей группы детского сада
     * Должна отсутствовать запись существующей группы детского сада
     * Должна отсутствовать запись с группой Администрации
     * У группы, у которой уже было выставлено поле branch_id оно не должно изменится, даже если оно было неверным
     * У группы не исключенной из плана питания должно быть выставлено поле branch_id
     */
    public function testGroupsActionUpdateDataToWebServer()
    {
        // Начальный набор
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '2-З', 'system_id'=>1000000001]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '6-Г', 'system_id'=>1000000002]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '2-И', 'system_id'=>1000000003]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '5-Л', 'system_id'=>1000000001]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '8-М', 'system_id'=>1000000001]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '5-Г', 'system_id'=>1000000001]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '5-Н', 'system_id'=>1000000001]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '7-З', 'system_id'=>1000000001]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '4-Е', 'system_id'=>1000000001, 'branch_id'=>1]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '3-В', 'system_id'=>9]);

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->groups();

        // Проверки
        $this->tester->seeNumRecords(10, 'ispp_iseduc_test.ispp_group');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'6-Г', 'system_id'=>1000000002]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'5-Л', 'system_id'=>1000000005]);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'ДО2-Подготовительная №4']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'СДС Мордашова']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'5-О']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'Администрация']);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'4-Е', 'branch_id'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'8-М', 'branch_id'=>0]);
    }

    /**
     * Есть записи в ИС ПП
     * Отсутствуют некоторые группы в веб версии
     * Вызывается метод groups
     * Должно быть нужное количество в веб версии
     * Должна присутствовать запись школьного класса, которая уже была
     * Должна присутствовать запись школьного класса, которой не было
     * Должна отсутствовать запись несуществующего класса
     * Должна отсутствовать запись несуществующей группы детского сада
     * Должна отсутствовать запись существующей группы детского сада
     * Должна отсутствовать запись с группой Администрации
     * У группы не исключенной из плана, у которой уже не было выставлено поле branch_id оно должно появится
     * У группы не исключенной из плана питания при добавлении должно быть выставлено поле branch_id
     */
    public function testGroupsActionUpdateAndInsertNewGroupsToWebServer()
    {
        // Начальный набор
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '2-З', 'system_id'=>1000000001]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '6-Г', 'system_id'=>1000000002]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '2-И', 'system_id'=>1000000003]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '4-Е', 'system_id'=>1000000001]);

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->groups();

        // Проверки
        $this->tester->seeNumRecords(10, 'ispp_iseduc_test.ispp_group');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'6-Г']);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'3-В']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'ДО2-Подготовительная №4']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'СДС Мордашова']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'5-О']);
        $this->tester->dontSeeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'Администрация']);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'4-Е', 'branch_id'=>0]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'8-М', 'branch_id'=>0]);
    }

    /**
     * Есть записи в ИС ПП
     * Есть некорректные записи в веб версии (лишняя группа)
     * Вызывается метод groups
     * Должно быть нужное количество в веб версии
     * Новая запись школьного класса в БД не должна быть скрыта
     * Запись школьного класса, которой была ранее в БД, но которая отсутсвует должна быть скрыта
     */
    public function testGroupsActionHideUnnecessaryGroupOnWebServer()
    {
        // Начальный набор
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '12-Я', 'system_id'=>1000000001, 'state'=>1]);
        $this->tester->haveInDatabase('ispp_iseduc_test.ispp_group', ['name' => '2-И', 'system_id'=>1000000003]);

        // Выполнение команды
        $sync = Fixtures::get('sync');
        $sync->groups();

        // Проверки
        $this->tester->seeNumRecords(11, 'ispp_iseduc_test.ispp_group');
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'6-Г', 'state'=>1]);
        $this->tester->seeInDatabase('ispp_iseduc_test.ispp_group', ['name'=>'12-Я', 'state'=>0]);
    }
}