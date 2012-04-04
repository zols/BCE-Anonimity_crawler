<?php
$smarty = new Smarty();
$smarty->debugging 		= false;
$smarty->force_compile 	= true;
$smarty->caching 		= false;
$smarty->compile_check 	= true;
$smarty->cache_lifetime = -1;
$smarty->template_dir 	= 'application/templates';
$smarty->compile_dir 	= 'work/template_c';
$smarty->cache_dir 	= 'work/cache';
?>