<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}


    protected function checkPermissions($access, $userId = [])
    {
        $action = explode('@', Route::currentRouteAction())[1];
        
        if(Route::currentRouteAction() == "UserController@create"){
            return;
        }
        if (Sentry::check())
        {
            if (isset($this->access[$action]) && $this->access[$action] != null)
            {
                foreach ($this->access[$action] as $group)
                {
                    if ($group == "Me" && (Route::input('users') == Session::get('userId') || Route::input('id') == Session::get('userId')))
                    {
                        return;
                    }
                    if (Sentry::getUser()->hasAccess($group))
                    {
                        return;
                    }
                }
                Session::flash('error', "You are not allowed to do this.");
                $this->beforeFilter('redirect:home');
            }
        } else
        {
            $this->beforeFilter('redirect:login');
        }
    }
}