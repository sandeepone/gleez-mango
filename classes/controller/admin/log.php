<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/sergeyklay/gleez-mango Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

use Gleez\Mango\Client;

/**
 * Admin Controller Class for control logging
 *
 * System Requirements
 *
 * - Gleez CMS 1.1.5 or higher
 * - MondoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    MangoReader\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.0
 */
class Controller_Admin_Log extends Controller_Admin {

    /**
     * Current logs collection
     * @var \Gleez\Mango\Collection
     */
    private $collection;

    /**
     * Collection name
     * @var string
     */
    private $collection_name;

    /**
     * Default collection name for logging
     * @type string
     */
    const DEFAULT_COLLECTION_NAME = 'logs';

    /**
     * The before() method is called before controller action
     *
     * @uses  ACL::required
     * @uses  Config::get
     * @uses  \Gleez\Mango\Client::instance
     * @uses  \Gleez\Mango\Client::__get
     */
    public function before()
    {
        ACL::required('view logs');

        $this->collection_name = Config::get('mango-reader.collections.logs', static::DEFAULT_COLLECTION_NAME);
        $this->collection = Client::instance()->{$this->collection_name};

        parent::before();
    }

    /**
     * The after() method is called after controller action
     *
     * @uses  Route::get
     * @uses  Route::uri
     */
    public function after()
    {
        // Tabs
        $this->_tabs = array(
            array('link' => Route::get('admin/log')->uri(array('action' =>'list')), 'text' => __('List')),
        );

        if (Client::instance()->isCollectionExists($this->collection_name)) {
            $this->_tabs[] = array('link' => Route::get('admin/log')->uri(array('action' =>'stat')), 'text' => __('Statistics'));
        }

        parent::after();
    }

    /**
     * Show Log statistics
     *
     * @uses  \Gleez\Mango\Collection::getStats
     */
    public function action_stat()
    {
        $this->title = __('System Log Statistics');

        $view = View::factory('admin/log/stat')
            ->set('stats', $this->collection->getStats())
            ->set('mongoVersion', Client::instance()->getMongoVersion());

        $this->response->body($view);
    }

    /**
     * Shows list of events
     *
     * @uses  Config::get
     * @uses  Route::get
     * @uses  \Gleez\Mango\Client::instance
     * @uses  \Gleez\Mango\Collection::count
     * @uses  \Gleez\Mango\Collection::reset
     * @uses  \Gleez\Mango\Collection::skip
     * @uses  \Gleez\Mango\Collection::sortDesc
     * @uses  \Gleez\Mango\Collection::limit
     * @uses  \Gleez\Mango\Collection::toArray
     */
    public function action_list()
    {
        $this->title = __('System Log');

        $view = View::factory('admin/log/list')
            ->set('clear_url',    Route::get('admin/log')->uri(array('action' =>'clear')))
            ->bind('pagination',  $pagination)
            ->bind('logs',        $logs);

        $pagination = Pagination::factory(
            array(
                'current_page'   => array('source'=>'cms', 'key'=>'page'),
                'total_items'    => $this->collection->count(),
                'items_per_page' => Config::get('mango-reader.items_per_page', 30),
                'uri'            => Route::get('admin/log')->uri(),
            )
        );

        $logs = $this->collection
            ->reset()
            ->sortDesc('time')
            ->skip($pagination->offset)
            ->limit($pagination->items_per_page)
            ->toArray();

        $this->response->body($view);
    }

    /**
     * View a particular event
     *
     * @uses  \Gleez\Mango\Collection::findOne
     * @uses  Message::alert
     * @uses  Log::add
     * @uses  Route::get
     * @uses  Route::uri
     * @uses  Request::redirect
     */
    public function action_view()
    {
        $this->title  = __('View Log');
        $id = $this->request->param('id', 0);

        $log = $this->collection->findOne(array('_id' => new MongoId($id)));

        if(is_null($log)) {
            Log::warning('An attempt to get the log event id: `:id`, which is not found!',
                array(':id' => $id)
            );
            Message::alert(__('Message #%id not found!', array('%id' => $id)));

            // Redirect to listing
            $this->request->redirect(Route::get('admin/log')->uri(), 404);
        }

        $view = View::factory('admin/log/view')
            ->set('log', $log);

        $this->response->body($view);
    }

    /**
     * Delete the message from log
     *
     * @uses  ACL::required
     * @uses  \Gleez\Mango\Collection::findOne
     * @uses  \Gleez\Mango\Collection::safeRemove
     * @uses  Message::success
     * @uses  Message::alert
     * @uses  Message::error
     * @uses  Log::add
     * @uses  Request::redirect
     * @uses  Route::get
     * @uses  Route::uri
     * @uses  Route::url
     * @uses  Template::valid_post
     */
    public function action_delete()
    {
        // Required privilege
        ACL::required('delete logs');

        $id  = $this->request->param('id', 0);
        $log = $this->collection->findOne(array('_id' => new MongoId($id)));

        if (is_null($log)) {
            Log::warning('An attempt to delete the log event id: `:id`, which is not found!',
                array(':id' => $id)
            );
            Message::alert(__('Message #%id not found!', array(':id' => $id)));

            // Redirect to listing
            $this->request->redirect(Route::get('admin/log')->uri(), 404);
        }

        $this->title = __('Delete :id', array(':id' => $id));
        $view = View::factory('form/confirm')
            ->set('action', Route::url('admin/log', array('action' => 'delete', 'id' => $id)))
            ->set('title', __('Log #:id', array(':id' => $id)));

        // If deletion is not desired, redirect to list
        if (isset($_POST['no']) AND $this->valid_post()) {
            // Redirect to listing
            $this->request->redirect(Route::get('admin/log')->uri(), 200);
        }

        // If deletion is confirmed
        if (isset($_POST['yes']) AND $this->valid_post()) {
            try {
                $this->collection->safeRemove(
                    array('_id'     => new MongoId($id)), // Event ID
                    array('justOne' => TRUE)              // Remove at most one record
                );

                Log::info('System log successfully cleared.');
                Message::success(__('Entry from the system log has been removed'));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), 200);
            } catch (MongoException $e) {
                Message::error(__('An error occurred when deleting the message: %msg',
                    array(':msg' => $e->getMessage())
                ));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), 500);
            }
        }

        $this->response->body($view);
    }

    /**
     * Drop collection
     *
     * @uses  ACL::required
     * @uses  Route::url
     * @uses  Route::get
     * @uses  Route::uri
     * @uses  Template::valid_post
     * @uses  Request::redirect
     * @uses  Message::success
     * @uses  Message::error
     * @uses  \Gleez\Mango\Collection::safeDrop
     */
    public function action_clear()
    {
        // Required privilege
        ACL::required('delete logs');

        $this->title = __('Drop system log');

        $view = View::factory('form/confirm')
            ->set('action', Route::url('admin/log', array('action' => 'clear')))
            ->set('title', __('All logs'));

        // If deletion is not desired, redirect to list
        if (isset($_POST['no']) AND $this->valid_post()) {
            $this->request->redirect(Route::get('admin/log')->uri(), 200);
        }

        // If deletion is confirmed
        if (isset($_POST['yes']) AND $this->valid_post()) {
            try {
                $response = $this->collection->safeRemove();

                Log::info('System log successfully cleared.');
                Message::success(__('System log successfully cleared.'));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), 200);
            } catch (MongoException $e) {
                Log::error('An error occurred when dropping the system log: :msg',
                    array(':msg' => $e->getMessage())
                );
                Message::error(__('An error occurred when dropping the system log: %msg',
                    array('%msg' => $e->getMessage())
                ));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), 500);
            }
        }

        $this->response->body($view);
    }
}
