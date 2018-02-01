<?php

return [
	'oauth2' => [
		'pgc_id' 	 => env('PASSWORD_GRANT_CLIENT_ID'),
		'pgc_secret' => env('PASSWORD_GRANT_CLIENT_SECRET')
	],

	'heroku' => [
		'api_key'  => env('HEROKU_API_KEY'),
		'app_name' => 'cc-tutor'
	],
];
