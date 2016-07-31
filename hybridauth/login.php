<?php
session_start();
/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */

// ------------------------------------------------------------------------
//	HybridAuth End Point
// ------------------------------------------------------------------------

require_once( "Hybrid/Auth.php" );
//require_once( "Hybrid/Endpoint.php" );

$config   = dirname(__FILE__) . '/config.php';
$hybridauth = new Hybrid_Auth( $config );
$kakao = $hybridauth->authenticate( "Kakao" );

$data = $kakao -> getUserProfile();
//Hybrid_Endpoint::process();
?>