<?php

use Codeception\Util\ReflectionHelper;
use zavoloklom\ispp\sync\src\Synchronization;
use zavoloklom\ispp\sync\src\SlackNotification;

/**
 * Class ApplicationUnitTest
 */
class ApplicationUnitTest extends \Codeception\Test\Unit
{
    /** @var \UnitTester */
    protected $tester;


    /**
     * @inheritdoc
     */
    protected function _before()
    {

    }

    /**
     * @inheritdoc
     */
    protected function _after()
    {

    }

    /**
     * Кусок кода, который повторяет инициализацию уведомлений в пиложении app.php
     * Наверное это должно быть где-то вынесено отдельно, но я не знаю как это сделать лучше
     * А протестировать этот кусок очень хочется
     *
     * @param array $config
     * @return Synchronization
     */
    private function getSyncClassWithNotifications(array $config = [])
    {
        $sync = new Synchronization();
        if (array_key_exists('slack', $config) && is_array($config['slack']) && $notification = new SlackNotification($config['slack'])) {
            $sync->notification = $notification;
            $sync->notificationEnabled = true;
        }
        return $sync;
    }

    /**
     * Для конфига без указания 'slack'
     * Уведомления должны быть выключены
     */
    public function testNotificationsIsDisabledWhenSlackConfigDoesNotExist()
    {
        $sync = $this->getSyncClassWithNotifications();
        $this->tester->assertFalse($sync->notificationEnabled);
    }

    /**
     * Для конфига где 'slack' принимает значение отличное от массива
     * Уведомления должны быть выключены
     */
    public function testNotificationsIsDisabledWhenSlackConfigIsNotArray()
    {
        $sync = $this->getSyncClassWithNotifications(['slack' => false]);
        $this->tester->assertFalse($sync->notificationEnabled);
    }

    /**
     * Для конфига где 'slack' принимает правильные значения
     * Уведомления должны быть включены
     */
    public function testNotificationsIsEnabledWhenSlackWebhookUriIsRight()
    {
        $sync = $this->getSyncClassWithNotifications(['slack' => ['webhook' => '127.0.0.1']]);
        $this->tester->assertTrue($sync->notificationEnabled);
    }

}