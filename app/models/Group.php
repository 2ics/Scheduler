<?php

class Group extends Eloquent {
    // use \Venturecraft\Revisionable\RevisionableTrait;

	// protected $revisionEnabled = true;

    protected $table = 'groups';

    public function user()
    {
        return $this->belongsTo('User');
    }
}