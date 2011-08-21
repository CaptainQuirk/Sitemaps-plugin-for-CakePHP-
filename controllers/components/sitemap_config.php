<?php
class SitemapConfigComponent extends Object {
  var $default = array(
    'limit' => 50000,
    'cacheDuration' => '+1 day',
  );
  
  var $config = array( );
  
  function initialize ( &$Controller ) {
  //  debug($Controller->sitemapSettings);
    
    if ( !$sitemapSettings = Configure::read ( 'Sitemap.Settings' ) ) {
      
      trigger_error (
          __d('sitemaps',
            'The Sitemap.Settings declaraction array wasn\'t found in your app/config/bootstrap.php'  
          ) ,
          E_USER_ERROR
        ) ;
      
    }
    
    foreach ($sitemapSettings['controllers'] as $controllerName => $controllerSettings) {
      
      if ( !is_array ( $controllerSettings ) ) {
      
        $controllerName = $controllerSettings ;
        $controllerSettings = array ( ) ;
        
      }  
      
       $this->config[$controllerName] = array_merge ($this->default, $controllerSettings);
    }
    
  
  }
  
  function getControllersList ( ) {
    return array_keys ($this->config);
  }
  
  function getControllerLimit($controllerName) {
    if (!isset($this->config[$controllerName])) {
      return false;
    }
    
    return $this->config[$controllerName]['limit'];
  }
  
  function getControllerCacheDuration ( $controllerName ) {
    
    if (!isset($this->config[$controllerName])) {
      return false;
    }
    
    return $this->config[$controllerName]['cacheDuration'] ;
    
  }
}