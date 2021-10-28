<?php

namespace SIMPELBOEK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
Plugin Name: simpelboek
Plugin URI:  https://github.com/pranamas/wp/hello-world
Description: raamwerk voor wordpress plugin
Version:     1.1
Author:      Theo van der Greft
Author URI:  http://www.pranamas.nl
*/
require_once dirname( __FILE__ ) . '/bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->init();
?>