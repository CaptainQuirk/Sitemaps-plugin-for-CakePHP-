<?php
class SitemapHelper extends AppHelper {
  
  
  function node ( $data ) {
    
    $nodeData = '<url>%s</url>' ;
    $content = '' ;
    
    if ( !isset ( $data['loc'] ) ) {
      
      trigger_error (
        __d ( 'sitemaps' , 'No "loc" key found in view data' ) ,
        E_USER_ERROR   
        
      ) ;
      
    }
    
    $loc = '<loc>%s</loc>' ;
    $content .= sprintf ( $loc , $data['loc'] ) ;
    
    $optionalNodes = array ( 'lastmod' , 'changefreq' , 'priority' ) ;
    
    foreach ( $optionalNodes as $optionalNode ) {
      
      if ( empty ( $data[$optionalNode] ) ) continue ;
      
      $node = "<$optionalNode>%s</$optionalNode>" ;
      
      $content .= sprintf ( $node , $data [$optionalNode]) ;
          
    }
    
    return sprintf ( $nodeData , $content ) ;
    
    
   
    
  }
  
  
  
}