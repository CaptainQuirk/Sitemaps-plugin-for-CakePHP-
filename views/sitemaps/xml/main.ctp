<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php foreach ( $controllersList as $controllerName ) : ?>
    <sitemap>
      <loc>
        <?php
          echo $this->Html->url (
            array (
              'plugin' => 'sitemaps' ,
              'prefix' => null ,
              'controller' => 'sitemaps' ,
              'action'  => 'dispatch',
              'ext' => 'xml' ,
              Inflector::underscore ( $controllerName ) 
            ) ,
            true 
          ) ;
        ?>
      </loc>
    </sitemap>
  <?php endforeach ; ?>
</sitemapindex>  