<?php

use zavoloklom\ispp\sync\src\Education;

/**
 * Class EducationUnitTest
 */
class EducationUnitTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testInstantiationWithDefaults()
    {
        $education = new Education();

        $this->assertSame('2016-09-01', $education->getYearStart());
        $this->assertSame('2017-05-01', $education->getYearFinish());
        $this->assertSame(true, $education->isSaturdayAreHoliday());
        $this->assertSame(true, $education->isSundayAreHoliday());
        $this->assertSame([], $education->getHolidays());
        $this->assertSame([], $education->getVacationIntervals());
        $this->assertSame(0, $education->getDepartmentId());
    }

    public function testInstantiationWithParams()
    {
        $options = [
            'department_id' => 1534,
            'year_start'  => '2016-09-02',
            'year_finish' => '2017-05-02',
            'isSaturdayAreHoliday' => false,
            'isSundayAreHoliday' => false,
            'holidays' => [
                '15.10.2016',
                '22.10.2016',
                '26.11.2016',
                '23.02.2017',
                '24.02.2017',
                '25.02.2017',
                '8.03.2017',
                '14.04.2017',
                '1.05.2017',
                '8.05.2017',
                '9.05.2017'
            ],
            'vacationIntervals' => [
                ['31.10.2016', '6.11.2016'],
                ['26.12.2016', '8.01.2017'],
                ['27.03.2017', '2.04.2017'],
                ['01.06.2017', '31.08.2017']
            ]
        ];

        $education = new Education($options);

        $this->assertSame($options['year_start'], $education->getYearStart());
        $this->assertSame($options['year_finish'], $education->getYearFinish());
        $this->assertSame($options['isSaturdayAreHoliday'], $education->isSaturdayAreHoliday());
        $this->assertSame($options['isSundayAreHoliday'], $education->isSundayAreHoliday());
        $this->assertSame($options['holidays'], $education->getHolidays());
        $this->assertSame($options['vacationIntervals'], $education->getVacationIntervals());
        $this->assertSame($options['department_id'], $education->getDepartmentId());
    }

    public function testIsSaturdayAHoliday()
    {
        $education = new Education(['isSaturdayAreHoliday' => false]);
        $this->assertFalse($education->checkDateAsHoliday('15.04.2017'));

        $education = new Education(['isSaturdayAreHoliday' => true]);
        $this->assertTrue($education->checkDateAsHoliday('15.04.2017'));
    }

    public function testIsSundayAHoliday()
    {
        $education = new Education(['isSundayAreHoliday' => false]);
        $this->assertFalse($education->checkDateAsHoliday('16.04.2017'));

        $education = new Education(['isSundayAreHoliday' => true]);
        $this->assertTrue($education->checkDateAsHoliday('16.04.2017'));
    }

    public function testIsFestiveDayAHoliday()
    {
        $education = new Education(['holidays' => []]);
        $this->assertFalse($education->checkDateAsHoliday('14.04.2017'));

        $education = new Education(['holidays' => ['14.04.2017']]);
        $this->assertFalse($education->checkDateAsHoliday('13.04.2017'));
        $this->assertTrue($education->checkDateAsHoliday('14.04.2017'));
    }

    public function testIsVacationDayAHoliday()
    {
        $education = new Education(['vacationIntervals' => []]);
        $this->assertFalse($education->checkDateAsHoliday('13.04.2017'));

        $education = new Education(['vacationIntervals' => [['27.03.2017', '2.04.2017']]]);
        $this->assertFalse($education->checkDateAsHoliday('22.03.2017'));
        $this->assertFalse($education->checkDateAsHoliday('3.04.2017'));
        $this->assertTrue($education->checkDateAsHoliday('27.03.2017'));
        $this->assertTrue($education->checkDateAsHoliday('29.03.2017'));
        $this->assertTrue($education->checkDateAsHoliday('2.04.2017'));
    }
}
