# ISPP-SYNC 
PHP скрипт синхронизации данных между удаленным сервером и сервером ИСПП.

[![license](https://img.shields.io/github/license/zavoloklom/ISPP-sync.svg?style=flat-square)](https://github.com/zavoloklom/ISPP-sync/LICENSE)
[![GitHub tag](https://img.shields.io/github/release/zavoloklom/ISPP-sync.svg?style=flat-square)](https://github.com/zavoloklom/ISPP-sync/tags)
[![CircleCI branch](https://img.shields.io/circleci/project/github/zavoloklom/ISPP-sync.svg?style=flat-square)](https://circleci.com/gh/zavoloklom/ISPP-sync)
[![Codecov](https://img.shields.io/codecov/c/github/zavoloklom/ISPP-sync.svg?style=flat-square)](https://codecov.io/gh/zavoloklom/ISPP-sync)
[![Gemnasium](https://img.shields.io/gemnasium/zavoloklom/ISPP-sync.svg?style=flat-square)](https://gemnasium.com/github.com/zavoloklom/ISPP-sync)
[![VersionEye](https://img.shields.io/versioneye/d/zavoloklom/ISPP-sync.svg?style=flat-square)](https://www.versioneye.com/user/projects/58e7a4fe26a5bb002b54c1b5?child=summary)

## Установка и запуск (Windows)
* Скачать и установить [PHP 7.1](http://php.net/downloads.php)
* Скачать и установить [Visual C++ Redistributable for Visual Studio 2015](http://www.microsoft.com/en-us/download/details.aspx?id=48145)
* Скопировать в папку PHP SSL сертификат `cacert.pem`
* Включить расширения в файле `php.ini`:
  ```
    extension=php_curl.dll
    extension=php_mbstring.dll
    extension=php_mysqli.dll
    extension=php_openssl.dll
    extension=php_pdo_mysql.dll
    extension=php_pdo_pgsql.dll
    extension=php_pdo_sqlite.dll
    extension=php_pgsql.dll
  ```
* Указать путь до `cacert.pem` в файле `php.ini`:
  ```
    [openssl]
    ; The location of a Certificate Authority (CA) file on the local filesystem
    ; to use when verifying the identity of SSL/TLS peers. Most users should
    ; not specify a value for this directive as PHP will attempt to use the
    ; OS-managed cert stores in its absence. If specified, this value may still
    ; be overridden on a per-stream basis via the "cafile" SSL stream context
    ; option.
    openssl.cafile= C:/PHP/cacert.pem
  ```  
* Скачать и установить [Git](https://git-scm.com/downloads)  
* Создать папку с содержимым этого репозитория (с помощью консоли)
  ```
    git clone [-b <BRANCH_NAME>] https://github.com/zavoloklom/ISPP-sync.git [FOLDER_NAME]
  ```
* Скачать и установить [Composer](https://getcomposer.org/download/)
* Перейти в папку с проектом и установить зависимости  (с помощью консоли)
  ```
    composer install --no-dev
  ```
* Т.к. при большом количестве данных скрипт будет выполнятся достаточно долго нужно увеличить лимиты таймаута для БД и PHP:
  * На 6 тысячах записей в таблице clients _один тест_ проходит примерно за 45 секунд
  * На ~3 тясячах записей учеников и ~4 тысячах остальных людей в таблице clients _синхронизация_ проходит примерно 5 минут
  * На >300 тясячах записей прохода _синхронизация_ проходит примерно 30 минут
  * Для _mysql_ на удаленном сервере это `interactive_timeout` и `wait_timeout` можно поставить 6 часов - 21600
  * Для _PHP_ на локальной машине в файле `php.ini`:   
  ```
    ; Maximum amount of memory a script may consume 1024M
    ; http://php.net/memory-limit
    memory_limit = 1024M
  ```
* Скопировать примеры команд и настройки из папки `example` в корень созданной папки и изменить значения по умолчанию на необходимые
* Настроить выполнение необходимых cmd-скриптов через планировщик задач

## Тестирование
* Должны быть уставлены PHP и MySql сервер
* Нужно прописать пути в переменные окружения до PHP и MySQL
* Нужно создать базы данных `ispp_ecafe_test` и `ispp_iseduc_test`

## Что тестируется
### Тестирование синхронизации групп
Ничего интересного

### Тестирование синхронизации учеников

_Пояснение к начальному набору данных_

|    |            |          |          |      |         |       |      |       |                                                      | 
|----|------------|----------|----------|------|---------|-------|------|-------|------------------------------------------------------|
| id | Тип        | Телефон  | Связь Р  | Фото | Связь Ф | В web | Фото | Увед. | Комментарий                                          | 
| 1  | ученик     | Нет      | Нет      | Нет  | Нет     | Да    | Нет  | Нет   |                                                      | 
| 2  | ученик     | Домашний | Нет      | Нет  | Нет     | Да    | Нет  | Нет   |                                                      | 
| 3  | ученик     | Email    | Нет      | Нет  | Нет     | Да    | Нет  | Нет   |                                                      | 
| 4  | ученик     | Нет      | Да:21    | Да   | Нет     | Да    | Да   | Да    |                                                      | 
| 5  | ученик     | Да       | Нет      | Нет  | Нет     | Да    | Нет  | Да    |                                                      | 
| 6  | ученик     | Да       | Да:22    | Нет  | Нет     | Да    | Нет  | Да    |                                                      | 
| 7  | ученик     | Нет      | Да:23    | Нет  | Да      | Да    | Да   | Нет   | Связь с родителем помечена удаленной                 | 
| 8  | ученик     | Нет      | Да:24    | Да   | Нет     | Да    | Да   | Нет   | Связь с родителем помечена устаревшей                | 
| 9  | ученик     | Нет      | Да:25,26 | Да   | Да      | Да    | Да   | Да    | Связи с двумя родителями, одна из которых устаревшая | 
| 10 | ученик     | Нет      | Да:27    | Нет  | Нет     | Да    | Нет  | Нет   | Связь с родителем без контактов                      | 
| 11 | ученик     | Нет      | Да:17    | Нет  | Нет     | Да    | Нет  | Да    | Связь с родителем-учителем                           | 
| 12 | ученик     | Нет      | Да:28    | Нет  | Нет     | Да    | Нет  | Да    | У родителя 2 связи                                   | 
| 13 | ученик     | Нет      | Да:29    | Нет  | Нет     | Да    | Нет  | Нет   | Связь с родителем из группы удаленные                | 
| 14 | ученик     | Нет      | Да:28    | Нет  | Нет     | Нет   | --   | --    | Группа 'Выбывшие'                                    |
| 15 | ученик     | Нет      | Нет      | Нет  | Нет     | Нет   | --   | --    | Группа 'Несуществующий класс'                        |
| 16 | сотрудник  | Нет      | Нет      | Нет  | Да      | Нет   | --   | --    | Группа 'Удаленные'                                   |
| 17 | учитель    | Да       | Да:11    | Нет  | Нет     | Нет   | --   | --    |                                                      | 
| 18 | учитель    | Нет      | Нет      | Нет  | Да      | Нет   | --   | --    |                                                      |
| 19 | дошкольник | Нет      | Да:20    | Нет  | Нет     | Нет   | --   | --    |                                                      |
| 20 | родитель   | Нет      | Да:19    | Нет  | Нет     | Нет   | --   | --    |                                                      |
| 21 | родитель   | Да       | Да       | Нет  | Нет     | Нет   | --   | --    |                                                      |
| 22 | родитель   | Да       | Да       | Нет  | Нет     | Нет   | --   | --    |                                                      |
| 23 | родитель   | Да       | Да       | Нет  | Нет     | Нет   | --   | --    |                                                      | 
| 24 | родитель   | Да       | Да       | Нет  | Нет     | Нет   | --   | --    |                                                      |
| 25 | родитель   | Да       | Да       | Нет  | Нет     | Нет   | --   | --    |                                                      | 
| 26 | родитель   | Да       | Да       | Нет  | Нет     | Нет   | --   | --    |                                                      |
| 27 | родитель   | Нет      | Да       | Нет  | Нет     | Нет   | --   | --    |                                                      | 
| 28 | родитель   | Да       | Да:12,14 | Нет  | Нет     | Нет   | --   | --    |                                                      |
| 29 | родитель   | Да       | Да       | Нет  | Нет     | Нет   | --   | --    | Группа 'Удаленные'                                   | 
| 30 | родитель   | Да       | Нет      | Нет  | Нет     | Нет   | --   | --    |                                                      | 

### Тестирование синхронизации событий

_Пояснение к начальному набору данных_

|       |        |            |          |     |      |        |           |                                          | 
|-------|--------|------------|----------|-----|------|--------|-----------|------------------------------------------| 
| id    | client | Дата       | Время    | Dir | Code | В базе | Опоздание | Комментарий                              | 
| 10001 | 1001   | 2016-04-01 | 08:12:00 | 0   | 17   | Нет    | Нет       | Проход ученика в прошлом учебном году    | 
| 10002 | 1001   | 2016-09-01 | 10:00:00 | 0   | 17   | Да     | Нет       | Ученик прошел после 8:30 в тот же день   | 
| 10003 | 1001   | 2016-09-01 | 08:15:00 | 0   | 17   | Да     | Нет       | Ученик первый раз прошел до 8:30         | 
| 10004 | 1002   | 2016-09-01 | 08:45:00 | 0   | 17   | Да     | Да        | Ученик первый раз вошел после 8:30       | 
| 10005 | 1003   | 2016-09-01 | 08:55:00 | 1   | 17   | Да     | Да        | Ученик первый раз вышел после 8:30       | 
| 10006 | 1004   | 2016-09-01 | 14:45:00 | 0   | 17   | Да     | Нет       | Ученик первый раз пришел после уроков    | 
| 10007 | 1002   | 2016-09-02 | 08:45:00 | 6   | 17   | Да     | Да        | Ученик первый раз вошел после 8:30       | 
| 10008 | 1003   | 2016-09-02 | 08:55:00 | 7   | 17   | Да     | Да        | Ученик первый раз вышел после 8:30       | 
| 10009 | 1001   | 2016-10-16 | 08:40:00 | 0   | 17   | Да     | Нет       | Ученик 'опоздал' в воскресенье           | 
| 10010 | 1001   | 2017-03-08 | 08:40:00 | 0   | 17   | Да     | Нет       | Ученик 'опоздал' в праздничный день      | 
| 10011 | 1001   | 2017-03-27 | 08:40:00 | 0   | 17   | Да     | Нет       | Ученик 'опоздал' в каникулы              | 
| 10012 | 1017   | 2016-09-01 | 08:15:00 | 0   | 17   | Нет    | Нет       | Учитель прошел                           | 
| 10013 | 1017   | 2016-09-02 | 10:00:00 | 0   | 17   | Нет    | Нет       | Учитель 'опоздал'                        | 
| 10014 | 1001   | 2016-10-18 | 08:15:00 | 1   | 112  | Да     | Нет       | Охранник выпустил ученика вовремя        | 
| 10015 | 1002   | 2016-10-18 | 11:15:00 | 0   | 112  | Да     | Нет       | Охранник пропустил ученика вовремя       | 
| 10016 | 1003   | 2016-10-18 | 08:45:00 | 0   | 112  | Да     | Да        | Охранник пропустил опаздывающего ученика | 
| 10017 | 1004   | 2016-10-18 | 08:45:00 | 1   | 112  | Да     | Да        | Охранник выпустил опаздывающего ученика  |
| 10018 | 1001   | 2016-10-19 | 08:15:00 | 0   | 17   | Да     | Нет       | Ученик пришел во время (см 10019)        |
| 10019 | 1001   | 2016-10-19 | 09:15:00 | 1   | 17   | Да     | Нет       | После синхронизации в 9:00 ученик вышел  |
| 10020 | 1002   | 2016-10-19 | 08:40:00 | 0   | 112  | Да     | Да        | Ученик опоздал (см 10021)                |
| 10021 | 1002   | 2016-10-19 | 09:10:00 | 1   | 112  | Да     | Нет       | После синхронизации в 9:00 ученик вышел  | 
