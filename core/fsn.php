<?php

include('extra.php');
class mrPHP extends Extra{
	
	
	
	//FUNÇÃO QUE CARREGA OS CONTROLES
	function controle(){
		$arquivo 	= "";
		$funcao 	= "";
		$parametro  = "";
		$classe 	= "";
		$uri = $_SERVER['REQUEST_URI'];
		$uri = str_replace($this->uri,'',$uri);
		if(strstr($uri,'?')) $uri = strstr($uri,'?',true);
		//SE NÃO TEM PARAMETRO CARREGA CONTROLE DA HOME
		if($uri == "/" || empty($uri)){
			$arquivo = "home_controle.php";
			$funcao = "index";	
			$classe = "home";
		}else{
			$uri = explode('/',$uri);
			$uri =array_filter($uri);
			$uri = array_slice($uri,0);
			if($uri[0] != ""){
				$arquivo = $uri[0]."_controle.php";
				$funcao = "index";	
				$classe = $uri[0];	
			}
			if($uri[1] != ""){
				$funcao = $uri[1];				
			}
			
			if(count($uri) > 2 && self::URL_CONTROL!=""){
				//$uri[2] .="/";
				for($a = 3;$a<count($uri);$a++){
					$uri[2] .=$uri[$a]."/";
					}
				}
			
		}
		
		//VERIFICA SE O CONTROLE EXISTE
		if(!is_file('controle/'.$arquivo)){
			$arquivo = "home_controle.php";
			$classe = "home";
			$funcao = "index";	
			$parametro = $uri[0].'/'.$uri[1].$uri[2];
			if(self::URL_CONTROL!=""){ 
				$arquivo = self::URL_CONTROL."_controle.php";
				$classe = self::URL_CONTROL;
				$parametro = "";
				
				for($a = 0;$a<count($uri);$a++){
					$parametro .=$uri[$a]."/";
					}
				$uri[2] = $parametro;
				$uri[1] = ""; 
			}
			
		}
		if(!is_file('controle/'.$arquivo)){
			$arquivo = "home_controle.php";
		}
		//CHAMA O CONTROLE E VERIFICA SE A CLASSE E A FUNÇÃO EXISTEM
			require_once('controle/'.$arquivo);
			if(class_exists($classe)){
				eval('$controle = new '.str_replace('-','_',$classe).';');
				
				if(!method_exists($controle,str_replace('-','_',$uri[1])) && $uri[1] != ""){
					$funcao = "index";
					$parametro = $uri[1].$uri[2];
					
					
				}else if($uri[2] != ""){
					$parametro = $uri[2];
					
					}
				$funcao = str_replace('-','_',$funcao);
				if(method_exists($controle,$funcao)){
					eval('$controle->'.$funcao.'("'.$parametro.'");');
				}else{
					echo "FUNÇÃO ".$funcao." NÃO ENCONTRADA";
				}
			
				
			}else{
					echo "CLASSE ".$classe." NÃO ENCONTRADA";
				}
		
			
		// SETA O TEMPLATE A SER CARREGADO		
		$template = 'index.php';
		if(!empty($controle->template) && isset($controle->template)) $template = $controle->template; 
		include('template/'.$template);
		
	}
	
	//FUNÇÃO QUE CARREGA AS PAGINAS
	public function getPagina(){
		$set = 0;
		if(isset($_SESSION['set'])){
			$set = 1;
			if(is_array($_SESSION['set'][1])){
				eval($_SESSION['set'][2].' = $_SESSION["set"][1];');
				}else{
				eval($_SESSION['set'][2].' = "'.$_SESSION['set'][1].'";');
				}
				unset($_SESSION['set']);
		}
		
		$uri = $_SERVER['REQUEST_URI'];
		$uri = str_replace($this->uri,'',$uri);
		if(strstr($uri,'?')) $uri = strstr($uri,'?',true);
		if(!empty($_SESSION['pagina'])){
			$uri = $_SESSION['pagina'];
			unset($_SESSION['pagina']);
		}
	
		if($uri == "/" || empty($uri)){
			$pasta = "home";
			$arquivo = "index.php";				
		}else{
			$uri = explode('/',$uri);
			$uri =array_filter($uri);
			$uri = array_slice($uri,0);
			if($uri[0] != ""){
				$pasta = $uri[0];
				$arquivo = "index.php";			
			}
			if($uri[1] != ""){
				$arquivo = $uri[1].'.php';			
			}
		}
			
		if(count($uri) > 3 && self::URL_CONTROL==""){
				$pasta = "404";
				$uri[1] = "index";
		}
	
		if(is_file('paginas/'.$pasta.'/'.$arquivo)){
			require_once('paginas/'.$pasta.'/'.$arquivo);
		}else{
			http_response_code (404);
			require_once('paginas/404/index.php');
		}
		
		
		 
		
}
	
	
}



?>