<?php
if( URL::has('insert') ) {

	require get_dashboard_path('view/customize/context-insert.php');
}


elseif( URL::has('update') ) {
	
	require get_dashboard_path('view/customize/context-update.php');
} 


elseif( URL::has('section') ) {
    
    require get_dashboard_path('view/customize/context-select-section.php');
} 


else {

	require get_dashboard_path('view/customize/context-select.php');
}