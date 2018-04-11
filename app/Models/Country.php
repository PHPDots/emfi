<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['title','country_code', 'country_type'];

       protected $table = TBL_COUNTRY;

    public static function getCountryList()
    {
    	$data = array();
    	$rows = Country::orderBy('title')->get();
    	if($rows)
    	{
    		foreach ($rows as $cn)
    		{
    			$data[$cn->id] = $cn->title; 
    		}
    	}
    	return $data;
    }
}
