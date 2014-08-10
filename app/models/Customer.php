<?php

class Customer extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'customers';
	
	protected $softDelete = true;

	public static $unguarded = true;

}