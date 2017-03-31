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
   * Для пустой конфигурации
   * Приватный метод testConnection должен возвращать false
   */
  public function testConnectionIsFalseWhenConfigIsEmpty()
  {
    $sync = new Synchronization();
    $testConnection = ReflectionHelper::invokePrivateMethod($sync, 'testConnection', [[]]);
    $this->tester->assertFalse($testConnection);
  }

  /**
   * Для неправильной конфигурации
   * Приватный метод testConnection должен возвращать false
   */
  public function testConnectionIsFalseWhenConfigIsIncorrect()
  {
    $sync = new Synchronization();
    $testConnection = ReflectionHelper::invokePrivateMethod($sync, 'testConnection', [[
      'adapter' => 'mysql',
      'options' => [
        'driver'    => 'mysql',
        'username'  => 'root',
        'password'  => '',
      ],
    ]]);
    $this->tester->assertFalse($testConnection);
  }

  /**
   * Для правильной конфигурации
   * Приватный метод testConnection должен возвращать true
   */
  public function testConnectionIsTrueWhenConfigIsCorrect()
  {
    $sync = new Synchronization();
    $testConnection = ReflectionHelper::invokePrivateMethod($sync, 'testConnection', [[
      'adapter' => 'mysql',
      'options' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'username'  => 'root',
        'password'  => '',
        'database'  => 'ispp-ecafe-test',
      ],
    ]]);
    $this->tester->assertTrue($testConnection);
  }

  /**
   * Для неправильной конфигурации обоих подключений
   * Должно быть выброшено исключение при проверки возможности проведения синхронизации
   */
  public function testConnectionsThrowExceptionWhenBothConfigsIsWrong()
  {
    $this->tester->expectException(\Exception::class, function() {
      $sync = new Synchronization();
      $sync->testConnections(
        [
          'adapter' => 'mysql',
          'options' => [],
        ],
        [
          'adapter' => 'mysql',
          'options' => [],
        ]
      );
    });
  }

  /**
   * Для неправильной конфигурации одного из подключений
   * Должно быть выброшено исключение при проверки возможности проведения синхронизации
   */
  public function testConnectionsThrowExceptionWhenOneOfConfigsIsWrong()
  {
    $this->tester->expectException(\Exception::class, function() {
      $sync = new Synchronization();
      $sync->testConnections(
        [
          'adapter' => 'mysql',
          'options' => [
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'username'  => 'root',
            'password'  => '',
            'database'  => 'ispp-ecafe-test',
          ],
        ],
        [
          'adapter' => 'mysql',
          'options' => [],
        ]
      );
    });
  }

  /**
   * Для правильной конфигурации
   * Метод testConnections должен вернуть true
   */
  public function testConnectionsIsTrueWhenBothConfigsIsRight()
  {
    $sync = new Synchronization();
    $testConnections = $sync->testConnections(
      [
        'adapter' => 'mysql',
        'options' => [
          'driver'    => 'mysql',
          'host'      => '127.0.0.1',
          'username'  => 'root',
          'password'  => '',
          'database'  => 'ispp-ecafe-test',
        ],
      ],
      [
        'adapter' => 'mysql',
        'options' => [
          'driver'    => 'mysql',
          'host'      => '127.0.0.1',
          'username'  => 'root',
          'password'  => '',
          'database'  => 'ispp-iseduc-test',
        ],
      ]
    );
    $this->tester->assertTrue($testConnections);
  }

  /**
   * Для глобальной тестовой конфигурации
   * Метод testConnections должен вернуть true
   */
  public function testConnectionsIsTrueForGlobalTestingConfig()
  {
    $sync = new Synchronization();
    $testConnections = $sync->testConnections(CONFIG['local_server'], CONFIG['web_server']);
    $this->tester->assertTrue($testConnections);
  }


}