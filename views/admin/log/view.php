<div class="help">
    <?php _e('Here shows the complete information about current entry from the System log.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $log['_id'])), '<i class="fa fa-trash-o"></i> '.__('Delete'), array('class' => 'btn btn-danger pull-right', 'title' => __('Delete this entry from log'))) ?>
<div class="clearfix"></div><br>

<table id="log-admin-view" class="table table-striped table-bordered table-highlight">
    <colgroup><col class="oce-first"></colgroup>
    <thead>
    <tr>
        <th><?php _e('Field')?></th>
        <th><?php _e('Value')?></th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php _e('ID'); ?></td>
            <td><?php echo $log['_id']; ?></td>
        </tr>
        <tr>
            <td><?php _e('Type')?></td>
            <td>
                <?php echo HTML::label($log['level'], $log['level']); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e('Date')?></td>
            <td>
                <?php echo Date::formatted_time($log['_id']->getTimestamp(), Config::get('site.date_time_format', 'l, F j, Y - H:i'), Config::get('site.timezone', 'UTC')); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e('Host')?></td>
            <td><?php echo $log['hostname']; ?></td>
        </tr>
        <tr>
            <td><?php _e('User Agent')?></td>
            <td><?php echo isset($log['user_agent']) ? $log['user_agent'] : '&mdash;' ?></td>
        </tr>
        <tr>
            <td><?php _e('File')?></td>
            <td><?php echo isset($log['file']) ? Text::plain($log['file']) : '&mdash;' ?></td>
        </tr>
        <tr>
            <td><?php _e('Line')?></td>
            <td><?php echo isset($log['line']) ? Text::plain($log['line']) : '&mdash;' ?></td>
        </tr>
        <?php if (isset($log['class'])): ?>
            <tr>
                <td><?php _e('Class')?></td>
                <td><?php echo Text::plain($log['class']) ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td><?php _e('Function')?></td>
            <td><?php echo Text::plain($log['function']) ?></td>
        </tr>
        <tr>
            <td><?php _e('URL')?></td>
            <td><?php echo Text::plain($log['url']) ?></td>
        </tr>
        <tr>
            <td><?php _e('Refer')?></td>
            <td><?php echo isset($log['refer']) ? Text::plain($log['refer']) : '&mdash;' ?></td>
        </tr>
        <tr>
            <td><?php _e('Message')?></td>
            <td><?php echo Text::plain($log['body']) ?></td>
        </tr>
    </tbody>
</table>
