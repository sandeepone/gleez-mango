<?php defined("SYSPATH") OR die("No direct script access.");
/**
 * Представление для просмотра одного события
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
    Здесь отображается полная информация по данному зарегистрированному событию
    из журнала. Запись будет храниться до тех пор, пока Вы не удалите её
    самостоятельно, либо пока администратор базы данных не очистит её, либо,
    если это предусмотрено настройками, событие не удалится по истечению срока
    давности.
  </p>
</div>

<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $log['_id'])), '<i class="icon-trash"></i> Удалить', array('class' => 'btn btn-danger pull-right', 'title' => 'Удалить из журнала событие')) ?>
<div class="clearfix"></div><br>

<table id="log-admin-view" class="table table-bordered table-striped">
  <colgroup><col class="oce-first"></colgroup>
  <thead>
    <tr>
      <th>Поле</th>
      <th>Значение</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Сообщение</td>
      <td><?php echo $log['_id']; ?></td>
    </tr>
    <tr>
      <td>Тип</td>
      <td>
        <span class="label label-<?php echo strtolower($log['type']); ?>">
          <?php echo $log['type']; ?>
        </span>
      </td>
    </tr>
    <tr>
      <td>Время</td>
      <td><?php echo Date::date_time($log['time']->sec); ?></td>
    </tr>
    <tr>
      <td>IP</td>
      <td><?php echo $log['host']; ?></td>
    </tr>
    <tr>
      <td>Клиент</td>
      <td><?php echo Text::plain($log['agent']) ?></td>
    </tr>
    <tr>
      <td>Пользователь</td>
      <td><?php echo Text::plain($log['user']) ?></td>
    </tr>
    <tr>
      <td>URL</td>
      <td><?php echo Text::plain($log['url']) ?></td>
    </tr>
    <tr>
      <td>Сообщение</td>
      <td><?php echo Text::plain($log['body']) ?></td>
    </tr>
  </tbody>
</table>