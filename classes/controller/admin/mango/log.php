<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Контролер управления системным журналом
 *
 * ### Системные требования
 *
 *  - PHP 5.3 или старше
 *  - PHP-расширение Mongodb 1.3 или старше
 *  - Модуль Mango Reader 0.1.1.1 или старше
 *  - ACL [Опционально]
 *
 * @package   Mango
 * @category  Controller
 * @author    Яковлев Сергей (me@klay.me)
 * @version   0.1.1.1
 * @copyright (c) 2013 Яковлев Сергей
 * @license   GPLv3
 */

class Controller_Admin_Mango_Log extends Controller_Admin {

  /** Метод before() всегда запускается перед любым экшеном */
  public function before()
  {
    // Изначально требуемые привилегии
    if (class_exists('ACL'))
    {
      ACL::Required('view logs');
    }

    // Подгружаем специфичные модулю стили
    Assets::css('user', 'media/css/mango.css', NULL, array('weight' => 0));

    // Выполнить родительский before()
    parent::before();
  }

  /** Список событий */
  public function action_list()
  {
    // Заголовок страницы
    $this->title = 'Системный журнал';

    // Создаём экземпляр Mango_Database
    $db = Mango::instance();

    // Формируем представление
    $view = View::factory('admin/mango/log/list')
                ->bind('pagination',  $pagination)
                ->bind('logs',        $logs);

    // Листалка по страницам
    $pagination = Pagination::factory(
      array
      (
        'current_page'    => array('source'=>'cms', 'key'=>'page'),
        'total_items'     => $db->count('Logs'),
        'items_per_page'  => 50,
        'uri'             => Route::get('admin/log')->uri(),
      )
    );

    // Выбираем все журналы
    $logs = $db->find('Logs')
               ->skip($pagination->offset)
               ->sort(array('time'=> -1))
               ->limit($pagination->items_per_page);

    $this->response->body($view);
  }

  /** Просмотр конкретного cобытия */
  public function action_view()
  {
    // Получаем ID
    $id = $this->request->param('id', 0);

    // Выбираем 1 документ
    $log = Mango::instance()->find_one('Logs', array('_id' => new MongoId($id)));

    // Если не получили документ
    if(is_null($log))
    {
      // Информируем
      Message::alert('События #' . $id . ' в журнале не обнаружено!');

      // Журналируем
      Kohana::$log->add(Log::WARNING, 'Обнаружена попытка получить из  журнала событие с id: `:id`, которого нет!',
        array(
          ':id' => $id
        )
      );

      // Перенаправляем с кодом 404 - не найдено
      if (! $this->_internal)
      {
        $this->request->redirect(Route::get('admin/log')->uri(), 404);
      }
    }

    // Получаем пользователя по ID
    $user = User::lookup((int) $log['user']);

    // Формируем отображаемое имя
    $log['user'] = $user->nick;

    // Заголовок страницы
    $this->title  = 'Просмотр события';

    // Формируем представление
    $view = View::factory('admin/mango/log/view')
                ->set('log', $log);

    $this->response->body($view);
  }

  /** Удаление сообщения журнала */
  public function action_delete()
  {
    // Требуемые привилегии
    if (class_exists('ACL'))
    {
      ACL::Required('delete logs');
    }

    // Получаем ID
    $id = $this->request->param('id', 0);

    // Выбираем 1 документ
    $log = Mango::instance()->find_one('Logs', array('_id' => new MongoId($id)));

    // Если не получили документ
    if(is_null($log))
    {
      // Информируем
      Message::alert('События #' . $id . ' в журнале не обнаружено!');

      // Журналируем
      Kohana::$log->add(Log::WARNING, 'Обнаружена попытка удалить из  журнала событие с id: `:id`, которого нет!',
        array(
          ':id' => $id
        )
      );

      // Перенаправляем с кодом 404 - не найдено
      if (! $this->_internal)
      {
        $this->request->redirect(Route::get('admin/log')->uri(), 404);
      }
    }

    $this->title = 'Удаление записи журнала';

    $view = View::factory('form/confirm')
                ->set('action', Route::url('admin/log', array('action' => 'delete', 'id' => $id)))
                ->set('title', 'Событие #'.$id);

    // Если удаление не подтверждено
    if (isset($_POST['no']) AND $this->valid_post())
    {
      $this->request->redirect(Route::get('admin/log')->uri(), 200);
    }

    // Если удаление подтверждено
    if (isset($_POST['yes']) AND $this->valid_post())
    {
      try
      {
        Mango::instance()->remove(
          'Logs',                           // Имя коллекции
          array('_id' => new MongoId($id)), // ID события
          array("justOne" => TRUE)          // Удалить не более одной записи
        );

        Message::notice('Сообщение из журнала было удалено');

        // Перенаправляем с кодом 200
        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 200);
        }

      }
      catch (Exception $e)
      {
        Message::error('Произошла ошибка при удалении сообщения: '.$e->getMessage());

        // Перенаправляем с кодом 500
        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 500);
        }
      }
    }

    $this->response->body($view);
  }

  /** Очистка всего журнала */
  public function action_clear()
  {
    // Требуемые привилегии
    if (class_exists('ACL'))
    {
      ACL::Required('delete logs');
    }

    // Формируем заголовок
    $this->title = 'Очистка всего журнала';

    $view = View::factory('form/confirm')
                ->set('action', Route::url('admin/log', array('action' => 'clear')))
                ->set('title', 'Все события из журнала');

    // Если удаление не подтверждено
    if (isset($_POST['no']) AND $this->valid_post())
    {
      $this->request->redirect(Route::get('admin/log')->uri(), 200);
    }

    // Если очистка журнала подтверждена
    if (isset($_POST['yes']) AND $this->valid_post())
    {
      try
      {
        $responce = Mango::instance()->drop('Logs');

        Message::notice('Журнал успешно очищен. Сообщение базы данных: '.$responce['msg']);

        // Перенаправляем с кодом 200
        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 200);
        }

      }
      catch (Exception $e)
      {
        Message::error('Произошла ошибка при очистке журнала: '.$e->getMessage());

        // Перенаправляем с кодом 500
        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 500);
        }
      }
    }

    $this->response->body($view);
    
  }

}