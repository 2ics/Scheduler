<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Assets::add('planner'); 

// Session Routes
Route::get('login',  array('as' => 'login', 'uses' => 'SessionController@create'));
Route::get('logout', array('as' => 'logout', 'uses' => 'SessionController@destroy'));
Route::resource('sessions', 'SessionController', array('only' => array('create', 'store', 'destroy')));

// User Routes
Route::get('register', 'UserController@create');
Route::get('users/{id}/activate/{code}', 'UserController@activate')->where('id', '[0-9]+');
Route::get('resend', array('as' => 'resendActivationForm', function()
{
	return View::make('users.resend');
}));
Route::post('resend', 'UserController@resend');
Route::get('forgot', array('as' => 'forgotPasswordForm', function()
{
	return View::make('users.forgot');
}));
Route::post('forgot', 'UserController@forgot');
Route::post('users/{id}/change', 'UserController@change');
Route::get('users/{id}/reset/{code}', 'UserController@reset')->where('id', '[0-9]+');
Route::get('users/{id}/suspend', array('as' => 'suspendUserForm', function($id)
{
	return View::make('users.suspend')->with('id', $id);
}));
Route::post('users/{id}/suspend', 'UserController@suspend')->where('id', '[0-9]+');
Route::get('users/{id}/unsuspend', 'UserController@unsuspend')->where('id', '[0-9]+');
Route::get('users/{id}/ban', 'UserController@ban')->where('id', '[0-9]+');
Route::get('users/{id}/unban', 'UserController@unban')->where('id', '[0-9]+');
Route::resource('users', 'UserController');

// Group Routes
Route::resource('groups', 'GroupController');

Route::get('/', array('as' => 'home', function()
{
	return View::make('home');
}));

Route::get('admin/upload/customers', 'UploadController@customers');
Route::post('admin/upload/customers/submit', 'UploadController@uploadCustomers');

Route::get('planner', 'HomeController@planner');
Route::get('process/{name}', 'HomeController@scheduleProcess');
Route::get('api/tasks/get', 'TaskController@getTasks');
Route::post('api/tasks/add', 'TaskController@addTask');
Route::post('api/tasks/save/event', 'TaskController@saveEvent');
Route::post('api/tasks/editcolumn', 'TaskController@editColumn');
Route::post('api/tasks/{process_id}/bydate', 'TaskController@processByDate');
Route::get('api/tasks/getbydate/{start}/{end}', 'TaskController@getByDate');
Route::get('api/tasks/unscheduledtasks', 'TaskController@getUnscheduledTasks');
Route::get('api/tasks/process/{id}/equipment', 'TaskController@getProcessEquipment');
Route::get('api/tasks/process/equipment/order/{id}', 'TaskController@getEquipmentOrderId');
Route::get('api/tasks/process/all', 'TaskController@allProcesses');


Route::get('project/create', 'ProjectController@create');
Route::get('project/editor', 'ProjectController@editor');
Route::get('scheduler', 'ProjectController@scheduler');
Route::get('project/getEquipment', 'ProjectController@getEquipment');
Route::post('project/save', 'ProjectController@save');
Route::get('project/delete/{project_id}', 'ProjectController@delete');
Route::get('project/getAll', 'ProjectController@getAll');
Route::get('project/edit/{project_id}', 'ProjectController@edit');
Route::get('project/schedule/{project_id}', 'ProjectController@schedule');
Route::get('task/reschedule/{task_id}', 'TaskController@reschedule');

Route::get('api/customers/all', 'CustomerController@allCustomers');
Route::get('api/select/processes/all', 'TaskController@allProcessesSelect');
Route::get('api/select/{process_id}/equipments', 'TaskController@allEquipmentByProcessSelect');
Route::get('api/select/process/equipment', 'TaskController@allEquipmentProcessSelect');
Route::get('api/users/all', 'UserController@allUsers');
// App::missing(function($exception)
// {
//     App::abort(404, 'Page not found');
//     //return Response::view('errors.missing', array(), 404);
// });





