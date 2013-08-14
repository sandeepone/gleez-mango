<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Controller Class for control logging
 *
 * ### System Requirements
 *
 * - Gleez CMS 0.10.4 or higher
 * - MondoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    Mango\Controller\Admin
 * @author     Gleez Team
 * @version    0.2.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */

class Controller_Admin_Log extends Controller_Admin {

	/**
	 * Current logs collection
	 * @var \Mango_Collection
	 */
	private $collection;

	/**
	 * Collection name
	 * @var string
	 */
	private $collection_name;

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 * @uses  Config::get
	 * @uses  Mango::instance
	 * @uses  Mango::__get
	 */
	public function before()
	{
		ACL::required('view logs');

		$this->collection_name = Config::get('mango-reader.collections.logs', 'logs');
		$this->collection = Mango::instance()->{$this->collection_name};

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
		$exists = Mango::instance()->exists($this->collection_name);

		// Tabs
		$this->_tabs =  array(
			array('link' => Route::get('admin/log')->uri(array('action' =>'list')), 'text' => __('List')),
			$exists ? array('link' => Route::get('admin/log')->uri(array('action' =>'stat')), 'text' => __('Statistics')) : NULL
		);

		parent::after();
	}

	/**
	 * Show Log statistics
	 *
	 * @uses  Mango_Collection::getStats
	 */
	public function action_stat()
	{
		$this->title = __('System Log Statistics');

		$view = View::factory('admin/log/stat')
			->set('stats', $this->collection->getStats());

		$this->response->body($view);
	}

	/**
	 * Shows list of events
	 *
	 * @uses  Config::get
	 * @uses  Route::get
	 * @uses  Mango::instance
	 * @uses  Mango_Collection::count
	 * @uses  Mango_Collection::reset
	 * @uses  Mango_Collection::skip
	 * @uses  Mango_Collection::sortDesc
	 * @uses  Mango_Collection::limit
	 * @uses  Mango_Collection::as_array
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
	 * @uses  Mango_Collection::findOne
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

		if(is_null($log))
		{
			Log::warning('An attempt to get the log event id: `:id`, which is not found!',
				array(':id' => $id)
			);
			Message::alert(__('Message #%id not found!', array('%id' => $id)));

			// Redirect to listing
			$this->request->redirect(Route::get('admin/log')->uri(), 404);
		}

		$view = View::factory('admin/log/view')
			->set('log',        $log);

		$this->response->body($view);
	}

	/**
	 * Delete the message from log
	 *
	 * @uses  ACL::required
	 * @uses  Mango_Collection::findOne
	 * @uses  Mango_Collection::safeRemove
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

		if (is_null($log))
		{
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
		if (isset($_POST['no']) AND $this->valid_post())
		{
			// Redirect to listing
			$this->request->redirect(Route::get('admin/log')->uri(), 200);
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$this->collection->safeRemove(
					array('_id'     => new MongoId($id)), // Event ID
					array('justOne' => TRUE)              // Remove at most one record
				);

				Log::info('System log successfully cleared.');
				Message::set(Message::SUCCESS,__('Entry from the system log has been removed'));

				// Redirect to listing
				$this->request->redirect(Route::get('admin/log')->uri(), 200);
			}
			catch (MongoException $e)
			{
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
	 * @uses  Mango_Collection::safeDrop
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
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/log')->uri(), 200);
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$response = $this->collection->safeRemove();

				Log::info('System log successfully cleared.');
				Message::success(__('System log successfully cleared. Database message: %msg',
					array('%msg' => $response['msg'])
				));

				// Redirect to listing
				$this->request->redirect(Route::get('admin/log')->uri(), 200);
			}
			catch (MongoException $e)
			{
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
