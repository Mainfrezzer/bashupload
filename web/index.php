<?php



# Configure
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);



# Route
$docs_handler = __DIR__ . '/../../bashupload-docs/index.php';
$action = 'default';

if ( in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT']) ) {
  
  $action = 'upload';
}
else {
  
  # load documentation, if we have the docs repo cloned
  if ( is_file($docs_handler) ) {
    $has_docs = true;
    $doc = include $docs_handler;
    
    if ( $doc ) {
      $action = 'docs';
    }
  }
  
  # check for custom pages that might be available
  $uri_trim = rtrim($uri, '/');
  $uri_handler = __DIR__ . "/../actions/{$uri_trim}.php";
  if ( is_file($uri_handler) ) {
    $action = $uri_trim;
  }else{
  # everything else is a possible file to download
  if ( !$doc && ($uri != '/') ) {
    $action = 'file';
  }
  }
}



# Execute routed handler
$accept = explode(',', $_SERVER['HTTP_ACCEPT']);
if ( $_POST['json'] == 'true' ) $renderer = 'json';
else if ( in_array('text/html', $accept) ) $renderer = 'html';
else $renderer = 'txt';

$action_handler = __DIR__ . "/../actions/{$action}.php";
if ( is_file($action_handler) ) {
  include $action_handler;
}



# Render
include __DIR__ . "/../render/{$renderer}.php";