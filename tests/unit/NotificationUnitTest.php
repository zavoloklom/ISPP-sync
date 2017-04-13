<?php

use zavoloklom\ispp\sync\src\SlackNotification;

/**
 * Class NotificationUnitTest
 */
class NotificationUnitTest extends \Codeception\Test\Unit
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
     * Для конфигурации без указания 'webhook'
     * Должно быть выброшено исключение
     */
    public function testNotificationClassThrowExceptionWhenConfigWithoutWebhook()
    {
        $this->tester->expectException(new \Exception('Не установлено значение для webhook.'), function() {
            new SlackNotification([]);
        });
    }

    /**
     * Для конфига где 'webhook' принимает значение неправльного адреса
     * Должно быть проброшено исключение
     */
    //public function testNotificationClassThrowExceptionWhenSlackWebhookUriIsWrong()
    //{
    //  $this->tester->expectException(\Exception::class, function() {
    //    new SlackNotification(['webhook' => 'test.test']);
    //  });
    //}

    /**
     * Для конфига где 'webhook' принимает значение правльного адреса
     * Должен быть создан экземпляр класса
     */
    public function testNotificationClassIsCreatedWhenSlackWebhookUriIsRight()
    {
        $this->tester->assertInstanceOf('zavoloklom\ispp\sync\src\SlackNotification', new SlackNotification(['webhook' => '127.0.0.1']));
    }
}
