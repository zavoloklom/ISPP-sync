<?php
/**
 * @copyright Copyright (c) 2017 Sergey Kupletsky
 * @license MIT
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
  public function printLogo()
  {
    echo '#  ###  ###     #         ### '.PHP_EOL ;
    echo '#  #    #       #  #  #  #    '.PHP_EOL;
    echo '#  ###  ##   ####  #  # #     '.PHP_EOL;
    echo '#     # #    # ##  #  #  #    '.PHP_EOL;
    echo '#  ###  ###  ####   ###   ### '.PHP_EOL;
    echo 'Синхронизация данных с ИС ПП  '.PHP_EOL.PHP_EOL;
  }

  /**
   * Print help
   */
  public function printHelp()
  {
    echo 'Ошибка параметра, воспользуйтесь справкой: '.PHP_EOL;
    echo 'groups      Синхронизация групп пользователей'.PHP_EOL;
    echo 'students    Синхронизация обучающихся в данном отделении'.PHP_EOL;
    echo 'events      Синхронизация событий в данном отделении'.PHP_EOL;
    echo 'all         Последовательная синхронизация групп, обучающихся и событий в данном отделении'.PHP_EOL;
    echo PHP_EOL;
  }
}