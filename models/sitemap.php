<?php
/*
 *     This model rewrites the paginator methods 
 **    It searches for the existence of specific methods in the application model.When present, it uses
 **    its parameters to make the appropriate find::count and find::all according the user's needs. When missing
 **    it gathers all the model's data and generates default cake urls : /controller/view/id or /controller/index
 ***
 ****
 ****
 ****/


class Sitemap extends SitemapsAppModel {
  
  var $useTable = false ;
  
  
  
}


