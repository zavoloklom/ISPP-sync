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

  /** @var array Organization data */
  private $organization;

  /** @var array Department data */
  private $department;

  protected $message_text_prefix;
  protected $message_title;


  /**
   * SlackNotification constructor.
   *
   * @param array $config
   * @param array $options Options to setup $organization and $department variables
   * @throws \Exception
   */
  public function __construct(array $config, array $options = [])
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

    // Initialize private variables
    if (array_key_exists('organization', $options)) {
      $this->organization = $options['organization'];
    }
    if (array_key_exists('department', $options)) {
      $this->department = $options['department'];
    }

    $this->message_text_prefix = $this->organization ? '<'.$this->organization['website']['url'].'|'.$this->organization['website']['name'].' ['.$this->organization['website']['ip'].']>' : 'Your Organization';
    $this->message_title = ($this->organization && $this->department) ? $this->organization['name'].' - '.$this->department['name'] : 'Your Department';

  }

  /**
   * @param boolean $localConnect
   * @param boolean $serverConnect
   */
  public function sendConnectionError($localConnect, $serverConnect)
  {
    $message = $this->slack->createMessage();
    $message
      ->setText($this->message_text_prefix.' - Не удалось синхронизировать данные')
      ->setAttachments([[
        'fallback' => 'Проблема с синхронизацией данных',
        'title'  => $this->message_title,
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
        'footer_icon' => 'http://1534.org/icons/favicon-32x32.png'
      ]])
      ->send();
  }

  /**
   * @param integer $createdGroupsCount Количество созданных групп в процессе синхронизации
   * @param integer $updatedGroupsCount Количество обновленных групп в процессе синхронизации
   * @param integer $hiddenGroupsCount  Количество скрытых групп в процессе синхронизации
   */
  public function sendGroupsSynchronizationInfo($createdGroupsCount, $updatedGroupsCount, $hiddenGroupsCount)
  {
    $message = $this->slack->createMessage();
    $message
      ->setText($this->message_text_prefix.' - Синхронизация групп завершена')
      ->setAttachments([[
        'fallback' => 'Синхронизация групп завершена',
        'title'  => $this->message_title,
        'text' => 'Синхронизация групп завершена:',
        'color' => 'good',
        'fields' => [
          [
            'title' => 'Создано групп',
            'value' => $createdGroupsCount,
            'short' => true
          ],
          [
            'title' => 'Обновлено групп',
            'value' => $updatedGroupsCount,
            'short' => true
          ],
          [
            'title' => 'Скрыто групп',
            'value' => $hiddenGroupsCount,
            'short' => true
          ]
        ],
        'footer' => 'ISEduC API',
        'footer_icon' => 'http://1534.org/icons/favicon-32x32.png'
      ]])
      ->send();
  }
}