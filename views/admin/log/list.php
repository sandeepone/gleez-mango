<?php defined('SYSPATH') OR die('No direct script access allowed.'); ?>

<div class="help">
	<?php _e('The log component monitors your website, capturing system events in a log to be reviewed by an authorized individual at a later time. The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information. It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.'); ?>
</div>

<?php echo HTML::anchor($clear_url, '<i class="icon-trash"></i> '.__('Clear all'), array('class' => 'btn btn-danger pull-right', 'title' => __('Clear all messages from the log'))); ?>
<div class="clearfix"></div><br>

<table id="log-admin-list" class="table table-striped table-bordered table-highlight">
	<thead>
	<tr>
		<th><?php _e('Date') ?></th>
		<th><?php _e('Type') ?></th>
		<th><?php _e('Message') ?></th>
		<th><?php _e('Host') ?></th>
		<th><?php _e('URL') ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($logs as $log) : ?>
		<tr>
			<td><?php echo Date::formatted_time($log['time']->sec, Config::get('site.date_time_format', 'l, F j, Y - H:i'), Config::get('site.timezone', 'UTC')); ?></td>
			<td><?php echo HTML::label($log['level'], $log['level']); ?></td>
			<td>
				<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' => 'view', 'id' => $log['_id'] )), Text::plain(Text::limit_chars($log['body'], 50)), array('title'=> __('View log'))); ?>
			</td>
			<td><?php echo $log['hostname']; ?></td>
			<td><?php echo Text::limit_chars($log['url'], 30); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php echo $pagination ?>
