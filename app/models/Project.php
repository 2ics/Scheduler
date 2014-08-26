<?php

class Project extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'projects';
    
	protected $softDelete = true;
	
    public function tasks()
    {
        return $this->hasMany('Task');
    }
    
    public function customer()
    {
        return $this->belongsTo('Customer');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }
}