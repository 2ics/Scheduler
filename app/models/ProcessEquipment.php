<?php

class ProcessEquipment extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'equipment';
	
	protected $softDelete = true;

    public function process()
    {
        return $this->hasOne('Process');
    }
}