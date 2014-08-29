<?php

use Authority\Repo\Group\GroupInterface;
use Authority\Service\Form\Group\GroupForm;

class GroupController extends BaseController {

    /**
     * Member Vars
     */
    protected $group;
    protected $groupForm;
    protected $access = array(
        'index'   => array('Super Admin', 'Admin'),
        'create'  => array('Super Admin', 'Admin'),
        'store'   => array('Super Admin', 'Admin'),
        'edit'    => array('Super Admin', 'Admin'),
        'show'    => array('Super Admin', 'Admin'),
        'update'  => array('Super Admin', 'Admin'),
        'destroy' => array('Super Admin', 'Admin')
    );

    /**
     * Constructor
     */
    public function __construct(GroupInterface $group, GroupForm $groupForm)
    {
        $this->group = $group;
        $this->groupForm = $groupForm;

        // Establish Filters
        $this->beforeFilter('auth');
        parent::checkPermissions($this->access);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $groups = $this->group->all();

        return View::make('groups.index')->with('groups', $groups);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $allGroups = Group::all();
        foreach ($allGroups as $groupName)
        {
            $groupList[] = $groupName->name;
        }

        return View::make('groups.create')->with('groupList', $groupList);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        // Form Processing
        $result = $this->groupForm->save(Input::all());

        if ($result['success'])
        {
            // Success!
            Session::flash('success', $result['message']);

            return Redirect::action('GroupController@index');

        } else
        {
            Session::flash('error', $result['message']);

            return Redirect::action('GroupController@create')
                ->withInput()
                ->withErrors($this->groupForm->errors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show($id)
    {
        //Show a group and its permissions.
        $group = $this->group->byId($id);

        return View::make('groups.show')->with('group', $group);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        $group = $this->group->byId($id);
        $permissions = $group->getPermissions();
        $allGroups = $group->all();
        foreach ($allGroups as $groupName)
        {
            $groupList[] = $groupName->name;
        }

        return View::make('groups.edit')->with('group', $group)->with('groupList', $groupList)->with('allGroups', $allGroups)->with('permissions', $permissions);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update($id)
    {
        // Form Processing
        $result = $this->groupForm->update(Input::all());

        if ($result['success'])
        {
            // Success!
            Session::flash('success', $result['message']);

            return Redirect::action('GroupController@index');

        } else
        {
            Session::flash('error', $result['message']);

            return Redirect::action('GroupController@create')
                ->withInput()
                ->withErrors($this->groupForm->errors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        if ($this->group->destroy($id))
        {
            Session::flash('success', 'Group Deleted');

            return Redirect::action('GroupController@index');
        } else
        {
            Session::flash('error', 'Unable to Delete Group');

            return Redirect::action('GroupController@index');
        }
    }

}