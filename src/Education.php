<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

/**
 * Class Education
 *
 * @package zavoloklom\ispp\sync\src
 */
class Education
{
    /** @var string Начало учебного года */
    private $year_start;

    /** @var string Окончание учебного года */
    private $year_finish;

    /** @var bool Является ли суббота выходным днем */
    private $isSaturdayAreHoliday;

    /** @var bool Является ли воскресенье выходным днем */
    private $isSundayAreHoliday;

    /**
     * Даты праздников
     *
     * Пример:
     * [
     *    '15.10.2016',
     *    '22.10.2016'
     * ]
     *
     * @var array
     */
    private $holidays;

    /**
     * Интервалы каникул
     *
     * Пример:
     * [
     *    ['31.10.2016', '6.11.2016'],
     *    ['01.06.2017', '31.08.2017']
     * ]
     *
     * @var array
     */
    private $vacationIntervals;

    /** @var integer Идентификатор отделения для записи в таблицу синхронизаций */
    private $department_id;

    /**
     * Education constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        // setup private variables from config
        $this->year_start   = array_key_exists('year_start', $config) ? $config['year_start'] : '2016-09-01';
        $this->year_finish  = array_key_exists('year_finish', $config) ? $config['year_finish'] : '2017-05-01';

        $this->isSaturdayAreHoliday = array_key_exists('isSaturdayAreHoliday', $config) ? $config['isSaturdayAreHoliday'] : true;
        $this->isSundayAreHoliday   = array_key_exists('isSundayAreHoliday', $config) ? $config['isSundayAreHoliday'] : true;
        $this->holidays             = array_key_exists('holidays', $config) ? $config['holidays'] : [];
        $this->vacationIntervals    = array_key_exists('vacationIntervals', $config) ? $config['vacationIntervals'] : [];

        $this->id = array_key_exists('department_id', $config) ? $config['department_id'] : 0;
    }

    /**
     * @param string $datetime
     * @return bool
     */
    public function checkDateAsHoliday($datetime)
    {
        $checkDay =  date('N', strtotime($datetime));
        $checkDate = date('Y-m-d', strtotime($datetime));

        // Проверка на выходные
        if ($this->isSaturdayAreHoliday == true && $checkDay == 6) {
            return true;
        }
        if ($this->isSundayAreHoliday == true && $checkDay == 7) {
            return true;
        }

        // Проверка на праздники
        foreach ($this->holidays as $holiday) {
            $holiday = date('Y-m-d', strtotime($holiday));
            if ($checkDate == $holiday) {
                return true;
            }
        }

        // Проверка на каникулы
        foreach ($this->vacationIntervals as $vacationInterval) {
            $vacationStart = date('Y-m-d', strtotime($vacationInterval[0]));
            $vacationFinish = date('Y-m-d', strtotime($vacationInterval[1]));
            if ($vacationStart <= $checkDate and $checkDate <= $vacationFinish) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getYearStart(): string
    {
        return $this->year_start;
    }

    /**
     * @return string
     */
    public function getYearFinish(): string
    {
        return $this->year_finish;
    }

}