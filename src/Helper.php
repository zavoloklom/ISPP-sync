<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license GPL-3.0
 * @link https://github.com/zavoloklom/ISPP-sync
 */

namespace zavoloklom\ispp\sync\src;

/**
 * Class Helper
 * @package zavoloklom\ispp\sync\src
 */
class Helper
{

    /**
     * Print logo in console
     */
    public static function printLogo()
    {
        echo '#  ###  ###     #         ### ', PHP_EOL;
        echo '#  #    #       #  #  #  #    ', PHP_EOL;
        echo '#  ###  ##   ####  #  # #     ', PHP_EOL;
        echo '#     # #    # ##  #  #  #    ', PHP_EOL;
        echo '#  ###  ###  ####   ###   ### ', PHP_EOL;
        echo 'Синхронизация данных с ИС ПП  ', PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Print help
     */
    public static function printHelp()
    {
        echo 'Справка по возможным командам: ', PHP_EOL;
        echo 'groups      Синхронизация групп пользователей', PHP_EOL;
        echo 'students    Синхронизация обучающихся в данном отделении', PHP_EOL;
        echo 'events      Синхронизация событий в данном отделении', PHP_EOL;
        echo PHP_EOL;
    }
}
