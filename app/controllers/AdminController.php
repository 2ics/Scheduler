<?php

class AdminController extends BaseController {

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
        'processes'		=> array('Super Admin'),
        'saveProcesses' => array('Super Admin')
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

	public function processes()
	{
		return View::make('admin.processes');
	}
	public function saveProcesses()
	{
		$data = Input::all();
		foreach(Process::all() as $process){
			$process->order = null;
			$process->save();
		}
		foreach(ProcessEquipment::all() as $equipment){
			$equipment->order = null;
			$equipment->save();
		}
		foreach ($data as $index => $process){
			if (isset($process['id'])){
				$dbProcess = Process::find($process['id']);
			}else{
				$dbProcess = new Process;
			}
			$dbProcess->name = str_replace("_", " ", $index);
			$dbProcess->order = $process['order'];
			$dbProcess->save();
			if (isset($process['equipment'])){
				foreach($process['equipment'] as $equipmentName => $equipment){
					if (isset($equipment['id'])){
						$dbEquipment = ProcessEquipment::find($equipment['id']);
					}else{
						$dbEquipment = new ProcessEquipment;
					}
					$dbEquipment->name = $equipmentName;
					$dbEquipment->process_id = $dbProcess->id;
					$dbEquipment->order = $equipment['order'];
					$dbEquipment->save();
				}
			}
		}

		Process::whereNull('order')->forceDelete();
		ProcessEquipment::whereNull('order')->forceDelete();
	}

}