<?php
class SitemapsController extends SitemapsAppController {
  
  var $uses = null ;
  
  var $components = array ( 'RequestHandler', 'SitemapConfig' ) ;
  
  var $helpers = array ( 'Sitemaps.Sitemap' , 'Cache' ) ;
  //var $helpers = array ( 'Sitemaps.Sitemap' ) ;
  
  function beforeFilter ( ) {
    
    if ( !$this->RequestHandler->isXml ( )) {
      
      $this->cakeError ( 'error404' ) ;
      
    }
    
    
  }
  
  function main ( ) {
      
      $controllersList = $this->SitemapConfig->getControllersList();
      
      $this->set ( 'controllersList' , array_unique ( $controllersList ) ) ;
      
  }
  
  /* Function dispatch
   *
   * This function has two purposes : to provide a sitemap for a given controller of the main
   * application or to redirect to the index function to generate a sitemapindex for the controller
   * if the model returns more than 50000 rows.
   *
   * @param $controllerName
   *
   */
  
  
  function dispatch (  $controllerSlug = null ) {
    
    $controllerObject = $this->__getController ( $controllerSlug ) ;    
    
    $staticCount  = $dynamicCount  = 0;
    $staticUrlSet = $dynamicUrlSet = array ( ) ;
    
    $this->cacheAction = $this->SitemapConfig->getControllerCacheDuration ( $controllerObject->name ) ;

    
    if (method_exists($controllerObject, 'staticSitemap')) {
      $staticUrlSet =  $controllerObject->staticSitemap() ;
      $staticCount  = count ( $staticUrlSet ) ;
    }
    
    $modelClass = $controllerObject->modelClass;
    
    if ( $model = $this->__getModel ( $modelClass ) ) {
      
      
      
      if (method_exists( $model , 'dynamicSitemap') ) {
        if ( !method_exists ( $model , 'dynamicSitemapCount') ) {
          trigger_error (
            __d('sitemaps',
              sprintf ( 'You must create the method dynamicSitemapCount() in Model %s' , $modelClass )
            ) ,
            E_USER_ERROR
          ) ;
        }
        
        $dynamicCount = $model->dynamicSitemapCount ( ) ;
      
        
      
      }
    }
    
    
    
    if ($limit = $this->SitemapConfig->getControllerLimit($controllerObject->name)) {
      if ($staticCount + $dynamicCount <= $limit) {
        if ( $dynamicCount > 0 ) {
          $dynamicUrlSet = $model->dynamicSitemap();
        }
        // On envoie à la vue la liste de toutes les urls
        $this->set('urlSet', array_merge($staticUrlSet, $dynamicUrlSet));
      } else {
        // On donne à la vue le nom du controller pour qu'elle crée les balises loc qui pointent vers :
        // 1. la sitemap des urls statiques
        // 2. les sitemaps des urls dynamiques paginées
        $this->set('controllerSlug' , $controllerSlug );
        
        $this->set ( compact ( 'staticCount' ) ) ;
        
        // On donne à la vue le nb de pages pour les urls de sitemap dynamiques
        $this->set('dynamicPageCount', ceil ($dynamicCount / $limit));
      }
    }
  }
  
/**
 * Liste des urls statiques
 */
  function static_view($controllerSlug = '') {
    
    
    $controllerObject = $this->__getController ( $controllerSlug ) ;
    
    $this->cacheAction = $this->SitemapConfig->getControllerCacheDuration ( $controllerObject->name ) ;
    
    if (!method_exists($controllerObject, 'staticSitemap')) {
      trigger_error (
        __d('sitemaps',
          sprintf ( 'You must create the method staticSitemap() in %s' , $controllerClass )
        ) ,
        E_USER_ERROR
      ) ;
    }
    
    $this->set('urlSet', $controllerObject->staticSitemap() ) ;
    
    $this->render ( 'sitemap' ) ;
  }
  
/**
 * Liste paginée des urls dynamiques
 */
  function dynamic_view($controllerSlug, $page) {
  
    $controllerObject = $this->__getController ( $controllerSlug ) ;
    
    $this->cacheAction = $this->SitemapConfig->getControllerCacheDuration ( $controllerObject->name ) ;
    
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