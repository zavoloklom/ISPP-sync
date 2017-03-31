<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

use GuzzleHttp\Exception\ConnectException;
use Maknz\Slack\Client;

/**
 * Class SlackNotification
 *
 * @package zavoloklom\ispp\sync\src
 */
class SlackNotification
{

  /** @var Client */
  private $slack;

  /** @var array */
  private $config;

  public $department_name = 'Department Name';


  public function __construct(array $config)
  {
    // Инициализация Slack клиента
    if (array_key_exists('webhook', $config)) {
      $webhook = $config['webhook'];
      $options = array_key_exists('options', $config) ? $config['options'] : [];

      $this->slack = new Client($webhook, $options);

      // Проверка корректности URI
      $guzzle = new \GuzzleHttp\Client();
      try {
        $guzzle->head($webhook);
      } catch(ConnectException $e) {
        throw new \Exception('Не удалось установить соединение со Slack сервисом.', $e->getCode(), $e);
      }
    } else {
      throw new \Exception('Не установлено значение для webhook.');
    }

    // Загрузка конфигурации
    $this->config = $config;
  }

  /**
   * @param boolean $localConnect
   * @param boolean $serverConnect
   */
  public function sendConnectionError($localConnect, $serverConnect)
  {
    $message = $this->slack->createMessage();
    $message
      ->setText('Не удалось синхронизировать данные')
      ->setAttachments([[
        'fallback' => 'Проблема с синхронизацией данных в '.$this->department_name,
        'title'  => $this->department_name,
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