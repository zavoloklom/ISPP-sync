<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
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
    private $slack;

    /** @var array Organization data */
    private $organization;

    /** @var array Department data */
    private $department;

    private $message_text_prefix;
    private $message_title;


    /**
     * SlackNotification constructor.
     *
     * @param array $config
     * @param array $options Options to setup $organization and $department variables
     * @throws \Exception
     */
    public function __construct(array $config, array $options = [])
    {
        // Initialize Slack client
        if (array_key_exists('webhook', $config)) {
            $webhook = $config['webhook'];
            $this->slack = new Client($webhook, $options);
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
     * @param integer $errors             Количество ошибок при записи/обновлении данных в БД
     * @param string  $script_time        Время выполнения скрипта
     * @param integer $script_memory_peak Пиковая нагрузка на оперативную память
     */
    public function sendGroupsSynchronizationInfo($createdGroupsCount, $updatedGroupsCount, $hiddenGroupsCount, $errors, $script_time, $script_memory_peak)
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
                    ],
                    [
                        'title' => 'Ошибок',
                        'value' => $errors,
                        'short' => true
                    ],
                    [
                        'title' => 'Время исполнения',
                        'value' => $script_time,
                        'short' => true
                    ],
                    [
                        'title' => 'Пиковая нагрузка',
                        'value' => $script_memory_peak.' Мб',
                        'short' => true
                    ]
                ],
                'footer' => 'ISEduC API',
                'footer_icon' => 'http://1534.org/icons/favicon-32x32.png'
            ]])
            ->send();
    }

    /**
     * @param integer $createdStudentsCount Количество созданных учеников в процессе синхронизации
     * @param integer $updatedStudentsCount Количество обновленных учеников в процессе синхронизации
     * @param integer $hiddenStudentsCount  Количество скрытых учеников в процессе синхронизации
     * @param integer $errors               Количество ошибок при записи/обновлении данных в БД
     * @param string  $script_time          Время выполнения скрипта
     * @param integer $script_memory_peak   Пиковая нагрузка на оперативную память
     */
    public function sendStudentsSynchronizationInfo($createdStudentsCount, $updatedStudentsCount, $hiddenStudentsCount, $errors, $script_time, $script_memory_peak)
    {
        $message = $this->slack->createMessage();
        $message
            ->setText($this->message_text_prefix.' - Синхронизация учеников завершена')
            ->setAttachments([[
                'fallback' => 'Синхронизация учеников завершена',
                'title'  => $this->message_title,
                'text' => 'Синхронизация учеников завершена:',
                'color' => 'good',
                'fields' => [
                    [
                        'title' => 'Создано учеников',
                        'value' => $createdStudentsCount,
                        'short' => true
                    ],
                    [
                        'title' => 'Обновлено учеников',
                        'value' => $updatedStudentsCount,
                        'short' => true
                    ],
                    [
                        'title' => 'Скрыто учеников',
                        'value' => $hiddenStudentsCount,
                        'short' => true
                    ],
                    [
                        'title' => 'Ошибок',
                        'value' => $errors,
                        'short' => true
                    ],
                    [
                        'title' => 'Время исполнения',
                        'value' => $script_time,
                        'short' => true
                    ],
                    [
                        'title' => 'Пиковая нагрузка',
                        'value' => $script_memory_peak.' Мб',
                        'short' => true
                    ]
                ],
                'footer' => 'ISEduC API',
                'footer_icon' => 'http://1534.org/icons/favicon-32x32.png'
            ]])
            ->send();
    }

    /**
     * @param integer $createdEventsCount   Количество добавленных событий в процессе синхронизации
     * @param integer $latecomeEventsCount  Количество опозданий выявленных в процессе синхронизации
     * @param string  $script_time          Время выполнения скрипта
     * @param integer $script_memory_peak   Пиковая нагрузка на оперативную память
     */
    public function sendEventsSynchronizationInfo($createdEventsCount, $latecomeEventsCount, $script_time, $script_memory_peak)
    {
        $message = $this->slack->createMessage();
        $message
            ->setText($this->message_text_prefix.' - Синхронизация событий завершена')
            ->setAttachments([[
                'fallback' => 'Синхронизация событий завершена',
                'title'  => $this->message_title,
                'text' => 'Синхронизация событий завершена:',
                'color' => 'good',
                'fields' => [
                    [
                        'title' => 'Добавлено событий',
                        'value' => $createdEventsCount,
                        'short' => true
                    ],
                    [
                        'title' => 'Кол-во опозданий',
                        'value' => $latecomeEventsCount,
                        'short' => true
                    ],
                    [
                        'title' => 'Время исполнения',
                        'value' => $script_time,
                        'short' => true
                    ],
                    [
                        'title' => 'Пиковая нагрузка',
                        'value' => $script_memory_peak.' Мб',
                        'short' => true
                    ]
                ],
                'footer' => 'ISEduC API',
                'footer_icon' => 'http://1534.org/icons/favicon-32x32.png'
            ]])
            ->send();
    }
}
