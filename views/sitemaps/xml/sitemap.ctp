<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php
    foreach ( $urlSet as $url ) :
      echo $this->Sitemap->node ( $url ) ;
    endforeach ;
  ?>
</urlset>