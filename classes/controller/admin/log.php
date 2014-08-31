<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/sergeyklay/gleez-mango Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

use Gleez\Mango\Client;
use Gleez\Mango\Document;
use Gleez\Mango\Exception;
use MangoReader\LogHelper;

/**
 * Admin Controller Class for control logging
 *
 * @package MangoReader\Controller\Admin
 * @author  Gleez Team
 * @version 1.0.0
 */
class Controller_Admin_Log extends Controller_Admin
{
    /**
     * LogHelper instance
     * @var \MangoReader\LogHelper
     */
    private $logHelper;

    /**
     * Current logs collection
     * @var \Gleez\Mango\Collection
     */
    private $collection;

    /**
     * Collection name
     * @var string
     */
    private $collectionName;

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

        $this->collectionName = Config::get('mango-reader.collections.logs', static::DEFAULT_COLLECTION_NAME);
        $this->collection = Client::instance()->{$this->collectionName};
        $this->logHelper = new LogHelper;

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

        if (Client::instance()->isCollectionExists($this->collectionName)) {
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
            ->set('mongoVersion', $this->collection->getClientInstance()->getMongoVersion());

        $this->response->body($view);
    }

    /**
     * Shows list of log entries
     */
    public function action_list()
    {
        $this->title = __('System Log');
        $formAction = Route::get('admin/log')->uri(array('action' => 'bulk'));

        $view = View::factory('admin/log/list')
            ->set('actionClear',  Route::get('admin/log')->uri(array('action' =>'clear')))
            ->set('actionBulk',   $formAction)
            ->set('logHelper',    $this->logHelper)
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
            ->limit($pagination->items_per_page);

        $this->response->body($view);
    }

    /**
     * View a particular log entry
     */
    public function action_view()
    {
        $this->title  = __('View Log');
        $id = $this->request->param('id');
        $model = Document::factory('Log', $id);

        if (!$model->isLoaded()) {
            Log::warning('An attempt to get the log entry id: [:id], which is not found!', array(':id' => $id));
            Message::error(__('Log entry #:id not found!', array(':id' => $id)));

            // Redirect to listing
            $this->request->redirect(Route::get('admin/log')->uri(), 404);
        }

        $this->response->body(View::factory('admin/log/view', array('model' => $model)));
    }

    /**
     * Delete the message from log
     *
     * @throws \Gleez\Mango\Exception
     */
    public function action_delete()
    {
        // Required privilege
        ACL::required('delete logs');

        $id = $this->request->param('id');
        $model = Document::factory('Log', $id);

        if (!$model->isLoaded()) {
            Log::warning('An attempt to get the log entry id: [:id], which is not found!', array(':id' => $id));
            Message::error(__('Log entry #:id not found!', array(':id' => $id)));

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
                $model->delete();

                Log::info('Log entry #:id successfully deleted.', array(':id' => $id));
                Message::success(__('Log entry #:id successfully deleted.'));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), 200);
            } catch (Exception $e) {
                Message::error(__('An error occurred when deleting the log entry: %msg',array('%msg' => $e->getMessage())));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), $e->getCode());
            }
        }

        $this->response->body($view);
    }

    /**
     * Clean up system log
     *
     * @throws \Gleez\Mango\Exception
     */
    public function action_clear()
    {
        // Required privilege
        ACL::required('delete logs');

        $this->title = __('Clean up system log');

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
                $this->collection->safeRemove();

                Log::info('System log has successfully cleaned up.');
                Message::success(__('System log has successfully cleaned up.'));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), 200);
            } catch (Exception $e) {
                Log::error('An error occurred when cleaning the system log: :msg', array(':msg' => $e->getMessage()));
                Message::error(__('An error occurred when cleaning the system log: %msg', array('%msg' => $e->getMessage())));

                // Redirect to listing
                $this->request->redirect(Route::get('admin/log')->uri(), $e->getCode());
            }
        }

        $this->response->body($view);
    }

    /**
     * Bulk action (temporary deleting only)
     */
    public function action_bulk()
    {
        $redirect = Route::get('admin/log')->uri(array('action' => 'list'));

        $this->title = __('Bulk Actions');
        $post = $this->request->post();

        // If deletion is not desired, redirect to list
        if (isset($post['no']) && $this->valid_post())
            $this->request->redirect($redirect);

        // If deletion is confirmed
        if (isset($post['yes']) && $this->valid_post()) {
            $this->bulkDelete(array_filter($post['items']));

            Message::success(__('The delete has been performed!'));
            $this->request->redirect($redirect);
        }

        if ($this->valid_post('log-bulk-actions')) {
            if (isset($post['operation']) && empty($post['operation'])) {
                Message::error(__('No bulk operation selected.'));
                $this->request->redirect($redirect);
            }

            if (!isset($post['logs']) || (!is_array($post['logs']) || !count(array_filter($post['logs'])))) {
                Message::error(__('No logs selected.'));
                $this->request->redirect($redirect);
            }

            try {
                if ($post['operation'] == 'delete') {
                    $logs = $this->logHelper->getBulkIds($post['logs']);
                    $this->title = __('Delete Logs');

                    $criteria = array('_id' => array('$in' => $logs));
                    $items = $this->collection->find($criteria);

                    $view = View::factory('form/confirm_multi')
                        ->set('action', '')
                        ->set('items', $items);

                    $this->response->body($view);
                    return;
                }

                Message::success(__('The update has been performed!'));
                $this->request->redirect($redirect);
            } catch( Exception $e) {
                Message::error(__('The update has not been performed!'));
            }
        }
    }

    /**
     * Bulk delete log entries
     * @param array $logs
     */
    private function bulkDelete(array $logs)
    {
        $writeConcern = $this->collection->getClientInstance()->getWriteConcern();
        $w = $writeConcern['w'] == 0 ? 1 : $writeConcern['w'];
        $wtimeout = $writeConcern['wtimeout'];

        $options = array('w' => $w, 'wtimeout' => $wtimeout, 'justOne' => false);
        $criteria = array('_id' => array('$in' => $this->logHelper->getBulkIds($logs)));

        $this->collection->safeRemove($criteria, $options);
    }
}
