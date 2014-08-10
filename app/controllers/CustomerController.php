<?php

class CustomerController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function allCustomers()
	{
		$customers = Customer::all();
		$all_customers = array();
		foreach ($customers as $customer){
			$temp_customer['value'] = $customer->id;
			$temp_customer['text'] = $customer->name;
			$all_customers[] = $temp_customer;
		}

		return $all_customers;
	}

    public function allUsers()
    {
        $users = User::all();
        $all_users = array();
        foreach ($users as $user){
            $temp_user['value'] = $user->id;
            $temp_user['text'] = $user->name;
            $all_users[] = $temp_user;
        }

        return $all_users;
    }

}