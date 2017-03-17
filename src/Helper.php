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
    echo "#  ###  ###     #         ### \n";
    echo "#  #    #       #  #  #  #    \n";
    echo "#  ###  ##   ####  #  # #     \n";
    echo "#     # #    # ##  #  #  #    \n";
    echo "#  ###  ###  ####   ###   ### \n";
    echo "Синхронизация данных с ИС ПП\n\n";
  }

  /**
   * Print help
   */
  public function printHelp()
  {
    echo "Ошибка параметра, воспользуйтесь справкой: \n";
    echo "groups      Синхронизация групп пользователей\n";
    echo "students    Синхронизация обучающихся в данном отделении\n";
    echo "events      Синхронизация событий в данном отделении\n";
    echo "all         Последовательная синхронизация групп, обучающихся и событий в данном отделении\n";
    echo "\n";
  }
}