<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Main Route
$route['default_controller'] = 'auth/login';
$route['404_override'] = 'error_404';
$route['translate_uri_dashes'] = FALSE;
