<?php

class Process extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'processes';
	
	protected $softDelete = true;

    public function equipment()
    {
        return $this->hasMany('ProcessEquipment');
    }

}