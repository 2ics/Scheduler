<?php

class UploadController extends BaseController {

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

    protected $access = array(
        'customers'         => array('Super Admin'),
        'uploadCustomers'   => array('Super Admin'),
        'insertCustomers'   => array('Super Admin')
    );
    /**
     * Constructor
     */
    public function __construct()
    {
        // Establish Filters
        $this->beforeFilter('auth');
        parent::checkPermissions($this->access);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function customers()
    {
        return View::make('upload.customers');
    }

    public function uploadCustomers()
    {
        ini_set('max_execution_time', 120);
        $file = Input::file('file'); // your file upload input field in the form should be named 'file'

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Customer::truncate();

        $folder = 'uploads/';
        $random = str_random(8);
        $destinationPath = $folder . $random;
        $filename = $file->getClientOriginalName();
        //$extension =$file->getClientOriginalExtension(); //if you need extension of the file
        $uploadSuccess = Input::file('file')->move($destinationPath, $filename);
        if ($uploadSuccess)
        {
            $file = "public/" . $destinationPath . "/" . $filename;
            $this->insertCustomers($random, $filename);

            return Response::json(array('success' => 200, 'folder' => $random, 'filename' => $filename)); // or do a redirect with some message that file was uploaded
        } else
        {
            return Response::json('error', 400);
        }
    }

    public function insertCustomers($folder, $file)
    {
        ini_set('max_execution_time', 120);
        Excel::load("public/uploads/" . $folder . "/" . $file, function ($reader)
        {
            $reader->each(function ($row)
            {
                $row = $row->toArray();
                $company = new Customer($row);
                $company->save();
            });
        });

    }

}