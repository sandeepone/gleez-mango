<?php defined("SYSPATH") OR die("No direct script access.");
/**
 * Представление для отображения списка событий
 *
 * @package   Mango
 * @category  View
 * @author    Яковлев Сергей (me@klay.me)
 * @version   0.1.1.1
 * @copyright (c) 2013 Яковлев Сергей
 * @license   GPLv3
 */
?>

<div class="help">
  <p class="lead">
    Модуль Mango Reader отслеживает системные события на вашем сайте и заносит
    их в журнал. Привилегированным пользователям предоставляется возможность
    просмотра журнала. Журнал это простой список из зарегистрированных событий
    содержащих в себе информацию  об использовании ресурсов сайта, данные о
    производительности, ошибки, предупреждения и другую оперативную информацию.
    Очень важно проверять отчёты системных событий на регулярной основе, т.к.
    это единственный способ узнать, что же на самом деле происходит на Вашем сайте.
  </p>
</div>

<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' =>'clear')), '<i class="icon-trash"></i> Очистить всё', array('class' => 'btn btn-danger pull-right', 'title' => 'Очистить все сообщения из журнала')); ?>
<div class="clearfix"></div><br>

<table id="log-admin-list" class="table table-bordered table-striped table-hover">
  <thead>
    <tr>
      <th>Дата</th>
      <th>Тип</th>
      <th>Сообщение</th>
      <th>IP</th>
      <th>URL</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($logs as $log) : ?>
    <tr>
      <td><?php echo Date::date_time($log['time']->sec); ?></td>
      <td>
        <span class="label label-<?php echo strtolower($log['type']); ?>">
          <?php echo $log['type']; ?>
        </span>
      </td>
      <td>
        <?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' => 'view', 'id' => $log['_id'] )), Text::plain(Text::limit_chars($log['body'], 50)), array('title'=> 'Просмотр события')); ?>
      </td>
      <td><?php echo $log['host']; ?></td>
      <td><?php echo Text::limit_chars($log['url'], 25); ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php echo $pagination ?>