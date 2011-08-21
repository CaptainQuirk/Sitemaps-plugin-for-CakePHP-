<?php if ( isset ( $urlSet ) ) : ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php
    foreach ( $urlSet as $url ) :
      echo $this->Sitemap->node ( $url ) ;
    endforeach ;
  ?>
</urlset>
<?php else : ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php if ( $staticCount ) : ?>
    <sitemap>
      <loc>
        <?php
          echo $this->Html->url (
            array (
              'plugin' => 'sitemaps',
              'controller' => 'sitemaps' ,
              'action'   => 'static_view' ,
              'prefix'   => null ,
              $controllerSlug ,
              'ext' => 'xml'
              
            ) , true 
          ) ;
        ?>
      </loc>
    </sitemap>
  <?php endif ; ?> 
  <?php for ( $i = 1 ; $i <= $dynamicPageCount ; $i++ ) : ?>
    <sitemap>
      <loc>
        <?php  echo $this->Html->url (
            array (
              'plugin' => 'sitemaps',
              'controller' => 'sitemaps' ,
              'action'   => 'dynamic_view' ,
              'prefix'   => null ,
              'ext'       => 'xml' ,
              $controllerSlug ,
              $i
            ) , true    
          );
        ?>
      </loc>
    </sitemap>
  <?php endfor ; ?>
</sitemapindex>
<?php endif ; ?>