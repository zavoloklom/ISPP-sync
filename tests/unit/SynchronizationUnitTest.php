<?php

use Codeception\Util\ReflectionHelper;
use zavoloklom\ispp\sync\src\Synchronization;

/**
 * Class SynchronizationUnitTest
 */
class SynchronizationUnitTest extends \Codeception\Test\Unit
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
   * Для конфига без указания 'slack'
   * Уведомления должны быть выключены
   */
  public function testNotificationsIsDisabledWhenSlackConfigDoesNotExist()
  {
    $sync = new Synchronization();
    $this->tester->assertFalse($sync->notificationEnabled);
  }

  /**
   * Для конфига где 'slack' принимает значение отличное от массива
   * Уведомления должны быть выключены
   */
  public function testNotificationsIsDisabledWhenSlackConfigIsNotArray()
  {
    $sync = new Synchronization([
      'slack' => false
    ]);
    $this->tester->assertFalse($sync->notificationEnabled);
  }

  /**
   * Для конфига где 'slack' принимает значение пустого массива
   * Должно быть проброшено исключение
   */
  public function testNotificationsThrowExceptionWhenSlackConfigIsEmpty()
  {
    $this->tester->expectException(new \Exception('Не установлено значение для webhook.'), function() {
      new Synchronization([
        'slack' => []
      ]);
    });
  }

  /**
   * Для конфига где 'slack' принимает значение неправльного адреса в 'webhook'
   * Должно быть проброшено исключение
   */
  public function testNotificationsThrowExceptionWhenSlackWebhookUriIsWrong()
  {
    $this->tester->expectException(\Exception::class, function() {
      new Synchronization([
        'slack' => [
          'webhook' => 'test.test'
        ]
      ]);
    });
  }

  /**
   * Для конфига где 'slack' принимает правильные значения
   * Уведомления должны быть включены
   */
  public function testNotificationsIsEnabledWhenSlackWebhookUriIsRight()
  {
    $sync = new Synchronization([
      'slack' => [
        'webhook' => 'yandex.ru'
      ]
    ]);
    $this->tester->assertTrue($sync->notificationEnabled);
  }

  /**
   * Для пустой конфигурации
   * Приватный метод setupLocalConnection должен возвращать false
   */
  public function testLocalConnectionIsFalseWhenConfigIsEmpty()
  {
    $sync = new Synchronization();
    $setupLocalConnection = ReflectionHelper::invokePrivateMethod($sync, 'setupLocalConnection', [[]]);
    $this->tester->assertFalse($setupLocalConnection);
  }

  /**
   * Для неправильной конфигурации
   * Приватный метод setupLocalConnection должен возвращать false
   */
  public function testLocalConnectionIsFalseWhenConfigIsIncorrect()
  {
    $sync = new Synchronization();
    $setupLocalConnection = ReflectionHelper::invokePrivateMethod($sync, 'setupLocalConnection', [[
      'adapter' => 'mysql',
      'options' => [
        'driver'    => 'mysql',
        'username'  => 'root',
        'password'  => '',
      ],
    ]]);
    $this->tester->assertFalse($setupLocalConnection);
  }

  /**
   * Для правильной конфигурации
   * Приватный метод setupLocalConnection должен возвращать true
   * Приватная переменная $local_connection должна быть экземпляром класса 'Pixie\QueryBuilder\QueryBuilderHandler'
   */
  public function testLocalConnectionIsTrueWhenConfigIsCorrect()
  {
    $sync = new Synchronization();
    $setupLocalConnection = ReflectionHelper::invokePrivateMethod($sync, 'setupLocalConnection', [[
      'adapter' => 'mysql',
      'options' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'username'  => 'root',
        'password'  => '',
        'database'  => 'ispp-ecafe-test',
      ],
    ]]);
    $this->tester->assertTrue($setupLocalConnection);
    $this->tester->assertInstanceOf('Pixie\QueryBuilder\QueryBuilderHandler', $sync->getLocalConnection());
  }

  /**
   * Для пустой конфигурации
   * Приватный метод setupWebConnection должен возвращать false
   */
  public function testWebConnectionIsFalseWhenConfigIsEmpty()
  {
    $sync = new Synchronization();
    $setupWebConnection = ReflectionHelper::invokePrivateMethod($sync, 'setupWebConnection', [[]]);
    $this->tester->assertFalse($setupWebConnection);
  }

  /**
   * Для неправильной конфигурации
   * Приватный метод setupWebConnection должен возвращать false
   */
  public function testWebConnectionIsFalseWhenConfigIsIncorrect()
  {
    $sync = new Synchronization();
    $setupWebConnection = ReflectionHelper::invokePrivateMethod($sync, 'setupWebConnection', [[
      'adapter' => 'mysql',
      'options' => [
        'driver'    => 'mysql',
        'username'  => 'root',
        'password'  => '',
      ],
    ]]);
    $this->tester->assertFalse($setupWebConnection);
  }

  /**
   * Для правильной конфигурации
   * Приватный метод setupWebConnection должен возвращать true
   * Приватная переменная $web_connection должна быть экземпляром класса 'Pixie\QueryBuilder\QueryBuilderHandler'
   */
  public function testWebConnectionIsTrueWhenConfigIsCorrect()
  {
    $sync = new Synchronization();
    $setupWebConnection = ReflectionHelper::invokePrivateMethod($sync, 'setupWebConnection', [[
      'adapter' => 'mysql',
      'options' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'username'  => 'root',
        'password'  => '',
        'database'  => 'ispp-iseduc-test',
      ],
    ]]);
    $this->tester->assertTrue($setupWebConnection);
    $this->tester->assertInstanceOf('Pixie\QueryBuilder\QueryBuilderHandler', $sync->getWebConnection());
  }

  /**
   * Для неправильной конфигурации
   * Должно быть выброшено исключение
   */
  public function testSetupConnectionsThrowExceptionWhenConfigIsWrong()
  {
    $this->tester->expectException(\Exception::class, function() {
      ReflectionHelper::invokePrivateMethod(new Synchronization(), 'setupConnections', [[
        'local_server' => [
          'adapter' => 'mysql',
          'options' => [],
        ],
        'web_server' => [
          'adapter' => 'mysql',
          'options' => [],
        ]
      ]]);
    });
  }

  /**
   * Для правильной конфигурации
   * Приватная переменная $local_connection должна быть экземпляром класса 'Pixie\QueryBuilder\QueryBuilderHandler'
   * Приватная переменная $web_connection должна быть экземпляром класса 'Pixie\QueryBuilder\QueryBuilderHandler'
   */
  public function testSetupConnectionsIsOKWhenConfigIsRight()
  {
    $sync = new Synchronization();
    ReflectionHelper::invokePrivateMethod($sync, 'setupConnections', [[
      'local_server' => [
        'adapter' => 'mysql',
        'options' => [
          'driver'    => 'mysql',
          'host'      => '127.0.0.1',
          'username'  => 'root',
          'password'  => '',
          'database'  => 'ispp-ecafe-test',
        ],
      ],
      'web_server' => [
        'adapter' => 'mysql',
        'options' => [
          'driver'    => 'mysql',
          'host'      => '127.0.0.1',
          'username'  => 'root',
          'password'  => '',
          'database'  => 'ispp-iseduc-test',
        ],
      ]
    ]]);
    $this->tester->assertInstanceOf('Pixie\QueryBuilder\QueryBuilderHandler', $sync->getWebConnection());
    $this->tester->assertInstanceOf('Pixie\QueryBuilder\QueryBuilderHandler', $sync->getLocalConnection());
  }

}