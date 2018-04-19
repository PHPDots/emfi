<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Country extends Model
{
    use Sluggable;
    use \Dimsav\Translatable\Translatable;

    protected $fillable = ['title','country_code', 'country_type','slug'];
    protected $table = TBL_COUNTRY;

    public $translatedAttributes = ['country_name'];

    public function sluggable()
    {
        return 
        [
            'slug' => 
            [
                'source' => 'title',
                'on_update' => true
            ]
        ];
    }      

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
