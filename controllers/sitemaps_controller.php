<?php
/**
 * Class SitemapsController
 * 
 * 
 *
 *
 *
 *
 */
class SitemapsController extends SitemapsAppController {
  
  var $uses = null ;
  
  var $components = array ( 'RequestHandler', 'SitemapConfig' ) ;
  
  var $helpers = array ( 'Sitemaps.Sitemap' , 'Cache' ) ;
  //var $helpers = array ( 'Sitemaps.Sitemap' ) ;
  
  
  /**
   * Function beforeFilter
   * 
   * Definition of the cake built-in beforeFilter Callback. Prevents non XML requests to be handled by sending a 404 response before anything else happens
   *
   */
  
  function beforeFilter ( ) {
    
    if ( !$this->RequestHandler->isXml ( )) {
      
      $this->cakeError ( 'error404' ) ;
      
    }
    
    
  }
  
  /**
   *  Function main
   *
   *  First action called by a bot according to the url given in robots.txt. It uses the SitemapConfig component to retrieve
   *  a list of affected controllers set by the users in app/config/bootstrap.php and passes it to the corresponding view
   *
   */
  
  function main ( ) {
      
      $controllersList = $this->SitemapConfig->getControllersList ( ) ;
      
      $this->set ( 'controllersList' , array_unique ( $controllersList ) ) ;
      
  }
  
  /** Function dispatch
   *
   * This function has two purposes : to provide a sitemap for a given controller of the main
   * application or to redirect to the index function to generate a sitemapindex for the controller
   * if the model returns more than 50000 rows.
   *
   * @param string controllerSlug slugified version of the controller name
   *
   */
  
  
  function dispatch (  $controllerSlug = null ) {
    
  // loading the appropriate controller  
    $controllerObject = $this->__getController ( $controllerSlug ) ;    
    
  // setting empty variables
    $staticCount  = $dynamicCount  = 0;
    $staticUrlSet = $dynamicUrlSet = array ( ) ;
    
  // setting the cache duration to the value set in the SitemapConfig component
    $this->cacheAction = $this->SitemapConfig->getControllerCacheDuration ( $controllerObject->name ) ;

  // testing the existence of the staticSitemap method in the controller 
    if ( method_exists ( $controllerObject, 'staticSitemap' ) ) {
    // Retrieving the set of urls
      $staticUrlSet =  $controllerObject->staticSitemap ( ) ;
    // Counting the set
      $staticCount  = count ( $staticUrlSet ) ;
    }
    
  // Getting the name of the model that should correspond to the given controller
    $modelClass = $controllerObject->modelClass;
    
  // Testing for the existence of the model 
    if ( $model = $this->__getModel ( $modelClass ) ) {
    // Testing for the existence of the dynamicSitemap method in the model     
      if ( method_exists ( $model , 'dynamicSitemap' ) ) {
      // If the required dynamicSitemapCount method is missing, we trigger an error
        if ( !method_exists ( $model , 'dynamicSitemapCount') ) {
          trigger_error (
            __d('sitemaps',
              sprintf ( 'You must create the method dynamicSitemapCount() in Model %s' , $modelClass )
            ) ,
            E_USER_ERROR
          ) ;
        }
      
      // Getting the number of urls affected for the given model  
        $dynamicCount = $model->dynamicSitemapCount ( ) ;
          
      }
    }  
    
  // Getting the limit number through the component for the given component
    if ( $limit = $this->SitemapConfig->getControllerLimit ( $controllerObject->name ) ) {
      
    // Testing if the sum of static urls and dynamic ones
      if ( $staticCount + $dynamicCount <= $limit ) {
        
      // If dynamic urls exist
        if ( $dynamicCount > 0 ) {
          
        // Let's get them
          $dynamicUrlSet = $model->dynamicSitemap ( ) ;
        }
        
      // Passing the url set to the view
        $this->set ( 'urlSet' , array_merge ( $staticUrlSet , $dynamicUrlSet ) ) ;
        
      } else {
        
        // Passing back the slug passed in the url to the view
          $this->set ( 'controllerSlug' , $controllerSlug ) ;
        
        // Passing the number of static urls
          $this->set ( compact ( 'staticCount' ) ) ;
        
        // Passing the number of dynamic urls to the view to built paginated urls
          $this->set ( 'dynamicPageCount', ceil ( $dynamicCount / $limit ) ) ;
      }
    }
  }
  
/**
 * List of static urls
 *
 * @param string controllerSlug 
 * 
 */
  function static_view ( $controllerSlug = '' ) {
    
  // loading the appropriate controller   
    $controllerObject = $this->__getController ( $controllerSlug ) ;
  
  // setting the cache duration to the value set in the SitemapConfig component    
    $this->cacheAction = $this->SitemapConfig->getControllerCacheDuration ( $controllerObject->name ) ;
  
  // Checking for the existence of the sitemap method and triggering an error if missing  
    if ( !method_exists ( $controllerObject , 'staticSitemap' ) ) {
      trigger_error (
        __d ( 'sitemaps',
          sprintf ( 'You must create the method staticSitemap() in %s' , $controllerClass )
        ) ,
        E_USER_ERROR
      ) ;
    }
  
  // Passing the set of url to the view  
    $this->set ( 'urlSet', $controllerObject->staticSitemap ( ) ) ;
    
  // use of the sitemap view
    $this->render ( 'sitemap' ) ;
  }
  
/**
 * Liste paginée des urls dynamiques
 */
  function dynamic_view($controllerSlug, $page) {
  
  // loading the appropriate controller  
    $controllerObject = $this->__getController ( $controllerSlug ) ;
  
  // setting the cache duration to the value set in the SitemapConfig component  
    $this->cacheAction = $this->SitemapConfig->getControllerCacheDuration ( $controllerObject->name ) ;
  
  // Getting the name of the model that should correspond to the given controller  
    $modelClass = $controllerObject->modelClass;
    
    $model = $this->__getModel ( $modelClass , true ) ;
    
    //debug ( get_class_methods ( $model ) ) ;
    
    if ( !method_exists( $model , 'dynamicSitemap') ) {
        trigger_error (
          __d('sitemaps',
            sprintf ( 'You must create the method dynamicSitemap() in Model %s' , $modelClass )
          ) ,
          E_USER_ERROR
        ) ;
    }
    
    $limit = $this->SitemapConfig->getControllerLimit($controllerObject->name);
    $offset = ($page - 1) * $limit;
    
    $this->set('urlSet', $model->dynamicSitemap($offset, $limit));
    
    $this->render ( 'sitemap' ) ;
  }
  
  
  function __getController ( $controllerSlug ) {
    
    $controllerName = Inflector::pluralize ( Inflector::classify ( $controllerSlug ) );
    
    if ( !in_array ( $controllerName , $this->SitemapConfig->getControllersList ( ) ) ) {
      trigger_error (
          __d('sitemaps',
            sprintf ( 'The controller %s is not in the Sitemaps.Settings set in app/config/bootstrap.php' , $controllerName )
          ) ,
          E_USER_ERROR
        ) ;
    }
    
    if ( !App::import('Controller', $controllerName) ) {
      
      trigger_error (
          __d('sitemaps',
            sprintf ( 'The controller %s could not be found' , $controllerName )
          ) ,
          E_USER_ERROR
        ) ;
      
    }
    
    $controllerClass = $controllerName."Controller";
    $controllerObject = new $controllerClass;
    
    
    return $controllerObject ;
    
  }
  
  function __getModel ( $modelClass , $strict = false ) {
    
    $loaded = App::import ( 'Model' , $modelClass ) ;
    
    if ( $loaded ) {
      
      return new $modelClass ;
      
    } else {
      
        if ( $strict ) {
              
            trigger_error (
              __d('sitemaps', sprintf ( 'Model %s not found' , $modelClass ) ) ,
              E_USER_ERROR
            ) ;
          
        } else {
          
            return false ;
          
        }   
    } 
  }
  
}