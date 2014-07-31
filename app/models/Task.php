<?php

class Task extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'tasks';
	
	protected $softDelete = true;
}