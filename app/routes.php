<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

//dd(Omnipay::getFactory()->find());


Route::get('/', 'HomeController@showWelcome');
Route::post('get_started', 'AccountController@getStarted');

Route::get('view/{invoice_key}', 'InvoiceController@view');
Route::get('payment/{invoice_key}', 'InvoiceController@show_payment');
Route::get('complete', 'InvoiceController@do_payment');


Route::filter('auth', function()
{
	if (!Auth::check())
    {
        return Redirect::to('/');
    }
});

Route::group(array('before' => array('auth', 'csrf')), function()
{   
	Route::get('account/{section?}', 'AccountController@showSection');
	Route::post('account/{section?}', 'AccountController@doSection');

	Route::resource('clients', 'ClientController');
	Route::get('api/clients', array('as'=>'api.clients', 'uses'=>'ClientController@getDatatable'));
	Route::post('clients/bulk', 'ClientController@bulk');

	Route::resource('invoices', 'InvoiceController');
	Route::get('api/invoices/{client_id?}', array('as'=>'api.invoices', 'uses'=>'InvoiceController@getDatatable'));	
	Route::get('invoices/create/{client_id}', 'InvoiceController@create');
	Route::post('invoices/bulk', 'InvoiceController@bulk');

	Route::get('payments', 'PaymentController@index');
	Route::get('api/payments/{client_id?}', array('as'=>'api.payments', 'uses'=>'PaymentController@getDatatable'));
	Route::post('payments/bulk', 'PaymentController@bulk');

	Route::get('home', function() { return View::make('header'); });
	Route::get('reports', function() { return View::make('header'); });
	Route::get('payments/create', function() { return View::make('header'); });

	Route::get('api/activities/{client_id?}', array('as'=>'api.activities', 'uses'=>'ActivityController@getDatatable'));	
});

// Confide routes
//Route::get( 'user/create',                 'UserController@create');
//Route::post('user',                        'UserController@store');
Route::get('login',                  'UserController@login');
Route::post('login',                  'UserController@do_login');
//Route::get( 'user/confirm/{code}',         'UserController@confirm');
//Route::get( 'user/forgot_password',        'UserController@forgot_password');
//Route::post('user/forgot_password',        'UserController@do_forgot_password');
//Route::get( 'user/reset_password/{token}', 'UserController@reset_password');
//Route::post('user/reset_password',         'UserController@do_reset_password');
Route::get('logout',                 'UserController@logout');




HTML::macro('nav_link', function($url, $text, $url2 = '', $extra = '') {
    $class = ( Request::is($url) || Request::is($url.'/*') || Request::is($url2) ) ? ' class="active"' : '';
    return '<li'.$class.'><a href="'.URL::to($url).'" '.$extra.'>'.$text.'</a></li>';
});

HTML::macro('tab_link', function($url, $text, $active = false) {
    $class = $active ? ' class="active"' : '';
    return '<li'.$class.'><a href="'.URL::to($url).'" data-toggle="tab">'.$text.'</a></li>';
});

HTML::macro('menu_link', function($type) {
	$types = $type.'s';
	$Type = ucfirst($type);
	$Types = ucfirst($types);
	$class = ( Request::is($types) || Request::is($types.'/*')) ? ' active' : '';
    return '<li class="dropdown '.$class.'">
			  <a href="'.URL::to($types).'" class="dropdown-toggle">'.$Types.'</a>
			  <ul class="dropdown-menu" id="menu1">
			    <!-- <li><a href="'.URL::to($types).'">List '.$Types.'</a></li> -->
			    <li><a href="'.URL::to($types.'/create').'">New '.$Type.'</a></li>
			  </ul>
			</li>';
});

HTML::macro('image_data', function($imagePath) {
	return 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imagePath));
});


function pluralize($string, $count) 
{
	$string = str_replace('?', $count, $string);
	return $count == 1 ? $string : $string . 's';
}

function toArray($data)
{
	return json_decode(json_encode((array) $data), true);
}

function toSpaceCase($camelStr)
{
	return preg_replace('/([a-z])([A-Z])/s','$1 $2', $camelStr);
}

function toSqlDate($date)
{
	if (!$date)
	{
		return '';
	}

	return DateTime::createFromFormat('m/d/Y', $date);
}

function fromSqlDate($date)
{
	if (!$date || $date == '0000-00-00')
	{
		return '';
	}

	return DateTime::createFromFormat('Y-m-d', $date)->format('m/d/Y');
}

function processedRequest($url)
{
	Session::put(Input::get('_token'), $url);
	Session::put('_token', md5(microtime()));
}


	
function trackViewed($url, $name)
{
	$viewed = Session::get(RECENTLY_VIEWED);	
	
	if (!$viewed)
	{
		$viewed = [];
	}

	$object = new stdClass;
	$object->url = $url;
	$object->name = $name;
	
	for ($i=0; $i<count($viewed); $i++)
	{
		$item = $viewed[$i];
		
		if ($object->url == $item->url)
		{
			array_splice($viewed, $i, 1);
		}
	}

	array_unshift($viewed, $object);
		
	if (count($viewed) > 5)
	{
		array_pop($viewed);
	}

	Session::put(RECENTLY_VIEWED, $viewed);
}


define("ENV_DEVELOPMENT", "local");
define("ENV_STAGING", "staging");
define("ENV_PRODUCTION", "production");

define("RECENTLY_VIEWED", "RECENTLY_VIEWED");
define("ENTITY_CLIENT", "client");
define("ENTITY_INVOICE", "invoice");
define("ENTITY_PAYMENT", "payment");

define("ACCOUNT_DETAILS", "details");
define("ACCOUNT_SETTINGS", "settings");
define("ACCOUNT_IMPORT", "import");
define("ACCOUNT_MAP", "import_map");
define("ACCOUNT_EXPORT", "export");