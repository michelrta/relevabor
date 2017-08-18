<?php
/*
Versão 3.0
Desenvolvido por: Michel Almeida
OBS: Sempre coloque / (barra) no final das URLs e Diretórios
*/
class Config {
	const URL 		= "http://www.exemplo.com.br/"; //URL DO SITE
	const URI 		= ""; // DIRETORIO DO FRAMEWORK
	const UPLOAD 	= "http://www.exemplo.com/upload/"; //URL DO DIRETORIO DE UPLOAD
	const ROOT 		= ""; //DIRETORIO RAIZ. Se não informado será usado o DOCUMENT_ROOT
	
	//CONEXÃO COM BANCO DE DADOS
	const HOST_BD 	= ""; //Endereço do Banco
	const USER_BD 	= ""; // Usuário do Banco
	const PASS_BD 	= ""; // Senha do banco 
	const NOME_BD 	= ""; // Nome do Banco de Dados
		
	const FORCE_WWW = false; //FORÇA WWW
	const FORCE_SSL = false; // Força o HTTPS
	
	const URL_CONTROL = ""; //NOME DO CONTROLE PARA TRATAMENTO DE URLs AMIGAVEIS DE PRIMEIRO NÍVEL
	
}
require_once('../core/mrPHP.php');
?>