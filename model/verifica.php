<?php
class verifica{
	function leesesion(){
		
		if(empty($_SESSION)){
		    session_start();
		}
		
		
		if(isset($_SESSION['name']) && isset($_SESSION['last_activity'])){
			$timeout = 2 * 60 * 60; 
			$elapsed = time() - $_SESSION['last_activity'];
			
			if($elapsed > $timeout){
				
				$this->destruyesesion();
				return "";
			}
			
			
			$_SESSION['last_activity'] = time();
		}
		
	  	if(isset($_SESSION['name'])){
			$s = $_SESSION['name'];
		}	  
		else{
		    $s = "";
		}
		return $s;
	}
	function destruyesesion(){
		session_start();
		session_destroy();
		header("Location: . ");
	}
}
?>