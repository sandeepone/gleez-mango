<?php
/**
 * @var string $actionClear
 * @var string $actionBulk
 * @var Pagination $pagination
 * @var \Gleez\Mango\Collection $logs
 * @var \MangoReader\LogHelper $logHelper
 */
?>
<div class="row">
    <div class="col-md-12">
        <div class="help">
            <?php _e('The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information. It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.'); ?>
        </div>
        <?php include Kohana::find_file('views', 'errors/partial'); ?>
        <div class="content">
            <?php echo Form::open($actionBulk, array('id'=>'admin-logs-form', 'class'=>'no-form')); ?>
            <fieldset class="bulk-actions form-actions rounded">
                <div class="row">
                    <div class="form-group col-xs-7 col-sm-3 col-md-3">
                        <div class="control-group <?php echo isset($errors['operation']) ? 'has-error': ''; ?>">
                            <?php echo Form::select('operation', $logHelper->getBulkActions(true), '', array('class' => 'form-control col-md-5')); ?>
                        </div>
                    </div>
                    <div class="form-group col-xs-5 col-sm-2 col-md-2">
                        <?php echo Form::submit('log-bulk-actions', __('Apply'), array('class'=>'btn btn-primary col-md-5')); ?>
                    </div>
                    <div class="form-group col-xs-6 col-sm-7 col-md-7 form-actions-right">
                        <?php echo HTML::anchor($actionClear, '<i class="fa fa-trash-o"></i> '.__('Clear all'), array('class' => 'btn btn-danger pull-right', 'title' => __('Clear all'))); ?>
                    </div>
                </div>
            </fieldset>
            <table id="log-admin-list" class="table table-striped table-bordered table-highlight">
                <thead>
                <tr>
                    <th> # </th>
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
                        <td><?php echo Form::checkbox('logs['.$log['_id'].']', $log['_id'], isset($_POST['logs'][$log['_id']])) ?></td>
                        <td><?php echo Date::date_time($log['_id']->getTimestamp()); ?></td>
                        <td><?php echo HTML::label($log['level'], $log['level']); ?></td>
                        <td><?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' => 'view', 'id' => $log['_id'])), Text::plain(Text::limit_chars($log['body'], 50)), array('title'=> __('View Log'))); ?></td>
                        <td><?php echo $log['hostname']; ?></td>
                        <td><?php echo Text::limit_chars($log['url'], 30); ?></td>
                        <td><?php echo HTML::icon(Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $log['_id'])), 'fa-trash-o', array('class'=>'action-delete', 'title'=> __('Delete Log'))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php echo Form::close(); ?>
        </div>
    </div>
    <div class="span-12> text-center">
        <?php echo $pagination ?>
    </div>
</div>
