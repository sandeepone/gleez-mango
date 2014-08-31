<?php
/**
 * @var Model_Log $model
 */
?>

<div class="help">
    <?php _e('Here shows the complete information about current entry from the System log.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $model->id)), '<i class="fa fa-trash-o"></i> '.__('Delete'), array('class' => 'btn btn-danger pull-right', 'title' => __('Delete this entry from log'))) ?>
<div class="clearfix"></div><br>

<table id="log-admin-view" class="table table-bordered">
    <thead>
	    <tr>
	        <th><?php _e('Field')?></th>
	        <th><?php _e('Value')?></th>
	    </tr>
    </thead>
    <tbody>
        <tr>
            <th><?php _e('ID'); ?></th>
            <td><?php echo $model->id; ?></td>
        </tr>
        <tr>
            <th><?php _e('Type')?></th>
            <td><?php echo HTML::label($model->level, $model->level); ?></td>
        </tr>
        <tr>
            <th><?php _e('Date')?></th>
            <td>
                <?php echo Date::formatted_time($model->id->getTimestamp(), Config::get('site.date_time_format', 'l, F j, Y - H:i'), Config::get('site.timezone', 'UTC')); ?>
            </td>
        </tr>
        <tr>
            <th><?php _e('Host')?></th>
            <td><?php echo $model->hostname; ?></td>
        </tr>
        <tr>
            <th><?php _e('User Agent')?></th>
            <td><?php echo $model->user_agent ?></td>
        </tr>
        <tr>
            <th><?php _e('File')?></th>
            <td><code><?php echo $model->file ?></code></td>
        </tr>
        <tr>
            <th><?php _e('Line')?></th>
            <td><?php echo $model->line ?></td>
        </tr>
            <th><?php _e('Class')?></th>
            <td><?php echo $model->class ?></td>
        <tr>
            <th><?php _e('Function')?></th>
            <td><?php echo $model->function ?></td>
        </tr>
        <tr>
            <th><?php _e('URL')?></th>
            <td><?php echo HTML::anchor($model->url, URL::site($model->url, true)) ?></td>
        </tr>
        <tr>
            <th><?php _e('Referer')?></th>
            <td><?php echo HTML::anchor($model->referer, URL::site($model->referer, true)) ?></td>
        </tr>
        <tr>
            <th><?php _e('Message')?></th>
            <td><pre><?php echo $model->body ?></pre></td>
        </tr>
    </tbody>
</table>
