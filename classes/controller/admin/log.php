<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Controller Class for control logging
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - Gleez CMS 0.9.26 or higher
 * - MondoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    Mango\Controller\Admin
 * @author     Sergey Yakovlev - Gleez
 * @version    0.1.3
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */

class Controller_Admin_Log extends Controller_Admin {

	/**
	 * Current logs collection
	 * @var Mango_Collection
	 */
	private $collection;

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 * @uses  Config::get
	 */
	public function before()
	{
		ACL::required('view logs');

		$this->collection = Config::get('mango-reader.collections.logs', 'Logs');
		$this->collection = Mango::instance()->{$this->collection};

		parent::before();
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
		$this->title = __('System log');

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
			->as_array();

		$this->response->body($view);
	}

	/**
	 * View a particular event
	 *
	 * @uses  Mango::instance
	 * @uses  Message::alert
	 * @uses  Log::add
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Request::redirect
	 */
	public function action_view()
	{
		$this->title  = __('View log');
		$id = $this->request->param('id', 0);

		$log = $this->collection->findOne(array('_id' => new MongoId($id)));

		if(is_null($log))
		{
			Message::alert(__('Message #%id not found!', array('%id' => $id)));

			Kohana::$log->add(Log::WARNING, 'An attempt to get the log event id: `:id`, which is not found!',
				array(':id' => $id)
			);

			if ( ! $this->_internal)
			{
				$this->request->redirect(Route::get('admin/log')->uri(), 404);
			}
		}

		$view = View::factory('admin/log/view')
			->set('delete_url', Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $id)))
			->set('log',        $log);

		$this->response->body($view);
	}

	/**
	 * Delete the message from log
	 *
	 * @uses  ACL::required
	 * @uses  Mango::instance
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
		$id = $this->request->param('id', 0);
		// Required privilege
		ACL::required('delete logs');

		$log = $this->collection->findOne(array('_id' => new MongoId($id)));

		if(is_null($log))
		{
			Message::alert(__('Message #%id not found!', array(':id' => $id)));

			Kohana::$log->add(Log::WARNING, 'An attempt to delete the log event id: `:id`, which is not found!',
				array(':id' => $id)
			);

			if ( ! $this->_internal)
			{
				$this->request->redirect(Route::get('admin/log')->uri(), 404);
			}
		}

		$this->title = __('Delete :id', array(':id' => $id));
		$view = View::factory('form/confirm')
			->set('action', Route::url('admin/log', array('action' => 'delete', 'id' => $id)))
			->set('title', __('Log #:id', array(':id' => $id)));

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
				$this->collection->remove(
					array('_id' => new MongoId($id)), // Event ID
					array("justOne" => TRUE)          // Remove at most one record
				);

				Message::success(__('Entry from the system log has been removed'));

				Kohana::$log->add(Log::INFO, 'System log successfully cleared');
				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/log')->uri(), 200);
				}

			}
			catch (Exception $e)
			{
				Message::error(__('An error occurred when deleting the message: %msg',
					array(':msg' => $e->getMessage())
				));

				if (! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/log')->uri(), 500);
				}
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
				$response = $this->collection->drop();

				Message::success(__('System log successfully cleared. Database message: %msg',
					array('%msg' => $response['msg'])
				));

				Kohana::$log->add(Log::INFO, 'System log successfully cleared');

				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/log')->uri(), 200);
				}

			}
			catch (Exception $e)
			{
				Message::error(__('An error occurred when clearing the system log: %msg',
					array(':msg' => $e->getMessage())
				));

				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/log')->uri(), 500);
				}
			}
		}

		$this->response->body($view);
	}

}
