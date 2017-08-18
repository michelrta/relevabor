<?php

class Funcoes extends Config{
	
	var $site = '';
	var $url = self::URL;
	var $uri  = self::URI;
	var $upload  = self::UPLOAD;
	var $root = self::ROOT;
	var $con;
	
	//FAZ TRATAMENTO DAS VARIAVEIS DA CONFIGURAÇÃO
	function __construct(){
		//MONTA A VARIVEL GLOBAL SITE
		$this->site = $this->url.$this->uri;
		
		//FORÇA O SSL E O WWW
		if(self::FORCE_WWW && self::FORCE_SSL){
			if(strstr($_SERVER["HTTP_HOST"],'www.')) $this->location("https://www." . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
		}
		//FORÇA O WWW
		if(self::FORCE_WWW){
			if(!strstr($_SERVER["HTTP_HOST"],'www.')) $this->location("http://www." . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
			if(!strstr($this->site,'www.')){
				$this->site = str_replace('http://','http://www.',$this->site);
				$this->site = str_replace('https://','https://www.',$this->site);
			}
		}
		//FORÇA O SSL
		if(self::FORCE_SSL){
			if($_SERVER["HTTPS"] != "on") $this->location("https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
			$this->site = str_replace('http://','https://',$this->site);
		}
		//DEFINE O ROOT CASO ELE NÃO TENHA SIDO ESPECIFICADO NO CONFIG
		if(self::ROOT == ""){ 
			$this->root = $_SERVER['DOCUMENT_ROOT'];
		}
		//CONECTA NO BANCO CASO OS PARAMETROS SEJAM PASSADOS
		if(self::HOST_BD != "" && self::USER_BD != "" && self::PASS_BD != "" && self::NOME_BD != ""){
			$this->con = $this->connect();
		}
		
	}
	
	//CONECTA COM O BANCO DE DADOS
	public function connect(){
		$con = mysqli_connect(self::HOST_BD,self::USER_BD,self::PASS_BD) OR DIE ("Não foi possivel conectar ao banco de dados");
		$bd = mysqli_select_db($con,self::NOME_BD) OR DIE ("Não foi possivel selecionar a base de dados");
		mysqli_set_charset($con,'utf8');
		return $con;		
	}
	
	//REDIRECT VIA HEADER
	public function location($url=NULL){
		 header('Location: '.$url);
	}
	
	//SETA UMA VARIAVEL PARA SER ENVIADA PARA A VIEW
	public function set($valor,$nome){
		$_SESSION['set'][1] = $valor;
		$_SESSION['set'][2] = '$'.$nome;
	}

	//INSERE UM ARRAY NO BANCO
	public function insert($array,$tabela,$validacao = NULL){
		
		//--DELETA CAMPOS QUE NÃO EXISTEM NA TABELA
		$campos_tabela = array();
		$sql = mysqli_query($this->con,"SHOW COLUMNS FROM $tabela") or die('Ocorreu um error '.mysqli_error());
		
		while($res = mysqli_fetch_assoc($sql)){
			$campos_tabela[] = $res['Field'];
			
			}	
		
		foreach($array as $campo => $dados){

			if(!in_array($campo,$campos_tabela) || is_array($array[$campo])){
				unset($array[$campo]);
				}else{
					$array[$campo] = mysqli_real_escape_string($this->con,$array[$campo]);
					}	
			}
		
		//--FAZ UPDATE CASO TENHA ID		
		if(isset($array['id_'.$tabela]) && !empty($array['id_'.$tabela])){
			
		$a = "UPDATE $tabela SET ";
		$qtdDados = count($array);
		$cont = 0;
		foreach($array as $campo => $dados){
			
			if($cont!=($qtdDados-1)){
				$a .= $campo." = '$dados', ";
			}else{
			    $a .= $campo." = '$dados'";
				}
			$cont++;
		}
		$a .=" WHERE id_".$tabela."=".$array['id_'.$tabela];
		
		$query = $a;
		$query = str_replace(',)',')',$query);
		$query = str_replace(",W","W",$query);
	    
		if(mysqli_query($this->con,$query)){
		return true;	
		}
		else {
			
			return false;
			 }
		}
		//--FAZ O INSERT NA TABELA
		else{		
		
		$a = "INSERT INTO $tabela (";
		$b = " VALUES (";
		$qtdDados = count($array);
		$cont = 0;
		foreach($array as $campo => $dados){
			
			if($cont!=($qtdDados)){
				$a .= $campo.",";
				$b .= "'$dados',";
			}else {
				$a .= $campo;
				$b .= "'$dados'";
				}
			$cont++;
		}
		$a .=")";
		$b .=")";
		$query = $a.$b;
		$query = str_replace(',)',')',$query);
	    
		if(mysqli_query($this->con,$query)){
	        return true;
		}else {
			 return false;
			 }}
		
	}
	
	//PEGA O ULTIMO REGISTRO COM OS PARAMETROS PASSADOS
	function find($tabela = NULL,$campo = NULL, $valor = NULL){
	$query = "SELECT * FROM " . $tabela . " WHERE " . $campo . " = '" . $valor ."' ORDER BY id_" . $tabela . " desc";
    if(mysqli_query($this->con,$query)){
	$query = mysqli_query($this->con,$query);
	$retorno = mysqli_fetch_array($query);
	$retorno = $this->removeNumber($retorno);
	return $retorno;
	} else return NULL;
	
	
}

	//CONVERTE DATA PARA FORMATO DO BANCO
	public function dataBd($data,$tipo = NULL){
	        if(!empty($tipo)){
				
				$data = explode(" ",$data);
				$time = $data[1];
				$data = $data[0];
				
				
				}
				
			$data = explode('/',$data);
			$data = $data[2].'-'.$data[1].'-'.$data[0];
			if(!empty($tipo)){
				$data = $data.' '.$time;				
				}
				
			return $data;
	   }
	   
	//CONVERTE DATA PARA FORMATO PADRÃO
	public function data($data,$tipo = NULL){
			if(empty($data)) return false;
			if(!empty($tipo)){
				$data = explode(' ',$data);
				$time = $data[1];
				$data = $data[0];
				
				}
	        $data = explode('-',$data);
			$data = $data[2].'/'.$data[1].'/'.$data[0];
			if(!empty($tipo)){
				$data = $data.' '.$time;				
				}
	   		return $data;
	   }
	   
	//REMOVE POSIÇÃO NUMERICA DE UM ARRAY
	public function removeNumber($array){
		if(!empty($array)){
			foreach($array as $check => $dados){
					      if(is_numeric($check)){
							  
							  unset($array[$check]);						
							  }		 
					 }
					  
				 return $array;
		}
			}
			
	//MONTA CLASSE PARA IDENTIFICAR PAGINAS	
	public function getClass(){
	$uri = $_SERVER['REQUEST_URI'];
	if(strstr($uri,'?')) $uri = strstr($uri,'?',true);	
	$classe= "";
	if('/'.$this->uri == $uri){
		$classe.=" front";
		}else{
			$classe.=" not-front";
			
		}
	return $classe;
	}
	
	//CONVERTE VALOR PARA FORMATO AMERICANO
	public function moedaBd($valor){
		$valor = str_replace('.','',$valor);
		$valor = str_replace(',','.',$valor);
		return $valor;
		}
		
	//CONVERTE VALOR PARA FORMATO BRASILEIRO
	public function moeda($valor){
		if($valor == 0) $valor = "0.00";
		if(empty($valor)) return false;
		$valor = number_format($valor,2,',','.');
		
		return $valor;
		}
		
	//REMOVE CARACTERES ESPECIAIS ACENTOS E ESPAÇOS DE UMA STRING
	public function removeChar($palavra){
		$palavra = trim($palavra);
		$palavra = strtolower($palavra);
		$a = array('á','â','ã','à','ó','ô','õ','ò','é','ê','è','ì','í','ç',' ',',','.',';',':','?','/','\\','[',']','Ã','Á','"','(',')','Í','ú','+','%','Ó','É','Ò','È','&','!','@','#','$','¨','*','Ç','ª',' º','|','–',' ','  ','´');
		$b = array('a','a','a','a','o','o','o','o','e','e','e','i','i','c','-','','','','','','','','','','a','a','','','','i','u','','','o','e','o','e','','','','','','','','c','','','-','-','-','-','');
		$palavra = str_replace($a,$b,strtolower($palavra));
		$palavra = str_replace('---','-',strtolower($palavra));
		$palavra = str_replace('--','-',strtolower($palavra));
		$palavra = str_replace(' ','-',strtolower($palavra));
		return $palavra;
	}
	//REMOVE CARACTERES ESPECIAIS
	public function clear($palavra){
		$palavra = strtolower($palavra);
		$a = array('-','/','.','\\',',',' ',')','(');
		$b = array('','','','','','','','');
		$palavra = str_replace($a,$b,strtolower($palavra));
		return $palavra;
	}
	
	//SETA UM PARAMETRO GLOBAL
	public function setVal($nome, $valor){
		$_SESSION[$nome] = $valor;	
	}
	
	//PEGA UM PARAMETRO GLOBAL
	public function getVal($nome){
		return $_SESSION[$nome];
	}
	
	//FAZ PRINT_R IDENTADO
	public function debug($valor){
		echo "<pre>";
		print_r($valor);
		echo "</pre>";
		}
		
	//SETA UMA VIEW A SER CARREGADA
	public function setPagina($valor){
		$_SESSION['pagina'] = $valor;
		}
	
	//MENIPULA VARIAVEIS GET
	public function getQuery($posicao,$valor){
		$getUrl = $this->filtro($_GET);
		$getUrl[$posicao] = $valor;
		if(empty($valor)) unset($getUrl[$posicao]);
		return "?".http_build_query($getUrl);
		}
		
	//REMOVE CROSS SCRIPT DE UM ARRAY
	public function filtro($array = NULL){
		$retorno = array();
		$arrayA = array('"',"'",'<','>');
		$arrayB = array('&quot;','&apos;','&lt;','&gt;');
		
		foreach($array as $a => $b){
			$retorno[$a] = htmlspecialchars($b);
			
			$retorno[$a] = preg_replace('/(from|select|insert|delete|where|alert|script|drop table|show tables|#|\*||\\\\)/i','',$retorno[$a]);
			$retorno[$a] = str_replace($arrayA,$arrayB,$retorno[$a]);
			$retorno[$a] = trim($retorno[$a]);
			}
		return $retorno;
	}
	//RETORNA O NOME DO MÊS
	public function getMes($a,$ab = NULL){
		if(empty($ab)){
			$mes = array( 
			"1" => "Janeiro",
			"2" => "Fevereiro",
			"3" => "Março",
			"4" => "Abril",
			"5" => "Maio",
			"6" => "Junho",
			"7" => "Julho",
			"8" => "Agosto",
			"9" => "Setembro",
			"10" => "Outubro",
			"11" => "Novembro",
			"12" => "Dezembro");
		}else{
			$mes = array( 
			"1" => "JAN",
			"2" => "FEV",
			"3" => "MAR",
			"4" => "ABR",
			"5" => "MAI",
			"6" => "JUN",
			"7" => "JUL",
			"8" => "AGO",
			"9" => "SET",
			"10" => "OUT",
			"11" => "NOV",
			"12" => "DEZ");
			}
		return $mes[$a];
	}
}
?>