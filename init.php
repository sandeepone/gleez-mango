<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Карта маршрутов
 *
 * @package   Mango
 * @category  Routing/Mango
 * @author    Яковлев Сергей (me@klay.me)
 * @version   0.1.1.1
 * @copyright (c) 2013 Яковлев Сергей
 * @license   GPLv3
 */

/** Установка маршрутизации */
if (! Route::cache())
{
  /**
   * Управление журналом
   *
   *  - Просмотр списка сообщений:      `site.com/admin/logs/list`
   *  - Просмотр конкретного сообщения: `site.com/admin/logs/view/<id>`
   *  - Удаление сообщения:             `site.com/admin/logs/delete/<id>`
   *  - Очистка всех сообщений:         `site.com/admin/logs/clear`
   */
  Route::set('admin/log', 'admin/logs(/<action>)(/p<page>)(/<id>)',
    array(
      'id'      => '([A-Za-z0-9]+)',
      'page'    => '\d+',
      'action'  => 'list|view|delete|clear',
    ))
    ->defaults(array(
      'directory'   => 'admin/mango',
      'controller'  => 'log',
      'action'      => 'list',
  ));

}

/**
 * Определение привилегий специфичных модулю если ACL присутствует в системе.
 *
 * Внимание: параметр `restrict access` указывает на то,
 * что данные привилегии имеют серьёзные последствия для безопасности.
 *
 * @uses ACL Используется для определения привилегий
 */
if ( class_exists('ACL') && ! ACL::cache() )
  {
    // Привелегии связанные с управлением журналами
    ACL::set('Mango Reader', array
    (
      'view logs' =>  array
      (
        'title'           => 'Просмотр журнал',
        'restrict access' => FALSE,
        'description'     => 'Просмотр всех событий заносящихся в журнал',
      ),
      'delete logs' =>  array
      (
        'title'           => 'Очистка журнала',
        'restrict access' => TRUE,
        'description'     => 'Удаление выборочных событий или очистка всего журнала',
      ),
    ));

  // В продакшене кешировать разрешения
  ACL::cache(Kohana::$environment === Kohana::PRODUCTION);
}
