<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

use Maknz\Slack\Client;

/**
 * Class SlackNotification
 *
 * @package zavoloklom\ispp\sync\src
 */
class SlackNotification
{

  /** @var Client */
  private $client;

  /** @var array */
  private $config;

  /**
   * SlackNotification constructor.
   * @param array $config
   */
  public function __construct(array $config)
  {
    // Инициализация Slack клиента
    try {
      $this->client = new Client($config['slack']['webhook'], $config['slack']['options']);
      echo 'Соединение со Slack сервисом успешно установлено'.PHP_EOL.PHP_EOL;
    } catch (\Exception $e) {
      echo 'Не удалось установить соединение со Slack сервисом: ',  $e->getMessage(), PHP_EOL;
    }

    // Загрузка конфигурации
    $this->config = $config;
  }

  /**
   * @param boolean $localConnect
   * @param boolean $serverConnect
   */
  public function connectionError($localConnect, $serverConnect)
  {
    $message = $this->client->createMessage();
    $message
      ->setText('Не удалось синхронизировать данные')
      ->setAttachments([[
        'fallback' => 'Проблема с синхронизацией данных в '.$this->config['department']['name'],
        'title'  => $this->config['department']['name'],
        'text' => 'Проблема с синхронизацией данных',
        'color' => 'danger',
        'fields' => [
          [
            'title' => 'Соединение с ИС ПП',
            'value' => $localConnect ? 'Работает' : 'Не работает',
            'short' => true
          ],
          [
            'title' => 'Соединение с сервером',
            'value' => $serverConnect ? 'Работает' : 'Не работает',
            'short' => true
          ]
        ],
        'footer' => 'ISEduC API',
        'footer_icon' => 'http://1534.org/icons/favicon-32x32.png',
        'ts' => time()
      ]])
      ->send();
  }
}