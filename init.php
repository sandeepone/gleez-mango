<?php
/**
 * Setting the Routes
 *
 * @package    MangoReader\Routing
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */

/** Routing setup */
if (!Route::cache()) {
    Route::set('admin/log', 'admin/logs(/<action>(/<id>))(/p<page>)', array(
        'id'          => '([A-Fa-f0-9]+)',
        'page'        => '\d+',
        'action'      => 'stat|list|view|delete|clear|bulk',
    ))
    ->defaults(array(
        'directory'   => 'admin',
        'controller'  => 'log',
        'action'      => 'list',
    ));
}

/**
 * Define Module specific Permissions
 *
 * Definition of user privileges by default if the ACL is present in the system.
 * Note: Parameter `restrict access` indicates that these privileges have serious
 * implications for safety.
 *
 * @uses  ACL::cache
 * @uses  ACL::set
 */
if (!ACL::cache()) {
    ACL::set('Mango Reader', array
    (
        'view logs' =>  array (
            'title'           => __('View Logs'),
            'restrict access' => TRUE,
            'description'     => __('View all logs'),
        ),
        'delete logs' =>  array (
            'title'           => __('Deleting Logs'),
            'restrict access' => TRUE,
            'description'     => __('Deleting logs from the System Log'),
        ),
    ));

    /** Cache the module specific permissions in production */
    ACL::cache(FALSE, Kohana::$environment === Kohana::PRODUCTION);
}
