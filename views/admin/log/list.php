<div class="help">
	<?php _e('The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information. It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.'); ?>
</div>

<?php echo HTML::anchor($clear_url, '<i class="icon-trash"></i> '.__('Clear all'), array('class' => 'btn btn-danger pull-right', 'title' => __('Clear all'))); ?>
<div class="clearfix"></div><br>

<table id="log-admin-list" class="table table-striped table-bordered table-highlight">
	<thead>
	<tr>
		<th><?php _e('Date') ?></th>
		<th><?php _e('Type') ?></th>
		<th><?php _e('Message') ?></th>
		<th><?php _e('Host') ?></th>
		<th><?php _e('URL') ?></th>
		<th><?php _e('Actions') ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($logs as $log) : ?>
		<tr>
			<td><?php echo Date::date_time($log['_id']->getTimestamp()); ?></td>
			<td><?php echo HTML::label($log['level'], $log['level']); ?></td>
			<td><?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' => 'view', 'id' => $log['_id'])), Text::plain(Text::limit_chars($log['body'], 50)), array('title'=> __('View Log'))); ?></td>
			<td><?php echo $log['hostname']; ?></td>
			<td><?php echo Text::limit_chars($log['url'], 30); ?></td>
			<td><?php echo HTML::icon(Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $log['_id'])), 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete Log'))) ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php echo $pagination ?>
