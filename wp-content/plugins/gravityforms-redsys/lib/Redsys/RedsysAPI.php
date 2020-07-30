<?php
namespace Redsys;

if(!class_exists('Services_JSON',false)) require_once dirname(__FILE__).'/json.php';
if(!function_exists('php_compat_hash_hmac')) require_once dirname(__FILE__).'/hmac.php';
require_once dirname(__FILE__).'/RedsysHelper.php';
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION); //5.2.7 ->  50207       5.5.28 -> 50528
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

class RedsysAPI {

	/******  Array de DatosEntrada ******/
    var $vars_pay = array();
	
	/******  Set parameter ******/
	function setParameter($key,$value){
		$this->vars_pay[$key]=$value;
	}

	/******  Set parameters ******/
	function setParameters($parameters){
		$this->vars_pay=$parameters;
	}

	/******  Get parameter ******/
	function getParameter($key, $default = null){
		if(isset($this->vars_pay[$key])){
			return $this->vars_pay[$key];
		}
		return $default;
	}

	/******  JSON Encode Functions (PHP4) ******/
	function json_decode4($data){
        $json = new Services_JSON();
        return $json->decode($data);
    }
	function json_encode4($data){
        $json = new Services_JSON();
        return $json->encode($data);
    }

	/******  3DES Function  ******/
	function encrypt_3DES($message, $key) {
	    $l = ceil(strlen($message) / 8) * 8;
	    return substr(openssl_encrypt($message . str_repeat("\0", $l - strlen($message)), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, "\0\0\0\0\0\0\0\0"), 0, $l);
	}

	/******  Base64 Functions  ******/
	function base64_url_encode($input){
		return strtr(base64_encode($input), '+/', '-_');
	}
	function encodeBase64($data){
		$data = base64_encode($data);
		return $data;
	}
	function base64_url_decode($input){
		return base64_decode(strtr($input, '-_', '+/'));
	}
	function decodeBase64($data){
		$data = base64_decode($data);
		return $data;
	}

	/******  MAC Function ******/
	function mac256($ent,$key){
		if (PHP_VERSION_ID < 50102) {
			$res = hash_hmac4('sha256', $ent, $key, true);
		} else {
			$res = hash_hmac('sha256', $ent, $key, true);//(PHP 5 >= 5.1.2)
		}
		return $res;
	}
	
	/******  Obtener Número de pedido ******/
	function getOrder(){
		$numPedido = "";
		if(empty($this->vars_pay['DS_MERCHANT_ORDER'])){
			$numPedido = $this->vars_pay['Ds_Merchant_Order'];
		} else {
			$numPedido = $this->vars_pay['DS_MERCHANT_ORDER'];
		}
		return $numPedido;
	}
	/******  Convertir Array en Objeto JSON ******/
	function arrayToJson(){
		if (PHP_VERSION_ID < 50200) {
			$json = $this->json_encode4($this->vars_pay);
		} else {
			$json = json_encode($this->vars_pay); //(PHP 5 >= 5.2.0)
		}
		return $json;
	}
	function createMerchantParameters(){
		// Se transforma el array de datos en un objeto Json
		$json = $this->arrayToJson();
		// Se codifican los datos Base64
		return $this->encodeBase64($json);
	}
	function createMerchantSignature($key){
		// Se decodifica la clave Base64
		$key = $this->decodeBase64($key);
		// Se genera el parámetro Ds_MerchantParameters
		$ent = $this->createMerchantParameters();
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($this->getOrder(), $key);
		// MAC256 del parámetro Ds_MerchantParameters
		$res = $this->mac256($ent, $key);
		// Se codifican los datos Base64
		return $this->encodeBase64($res);
	}

	/******  Obtener Número de pedido ******/
	function getOrderNotif(){
		$numPedido = "";
		if(empty($this->vars_pay['Ds_Order'])){
			$numPedido = $this->vars_pay['DS_ORDER'];
		} else {
			$numPedido = $this->vars_pay['Ds_Order'];
		}
		return $numPedido;
	}
	/******  Convertir String en Array ******/
	function stringToArray($datosDecod){
		if (PHP_VERSION_ID < 50200) {
			$this->vars_pay = $this->json_decode4($datosDecod);
		} else {
			$this->vars_pay = json_decode($datosDecod, true); //(PHP 5 >= 5.2.0)
		}
		$_REQUEST = array_merge($_REQUEST,$this->vars_pay);
	}
	function decodeMerchantParameters($datos){
		// Se decodifican los datos Base64
		$decodec = $this->base64_url_decode($datos);
		return $decodec;	
	}
	function createMerchantSignatureNotif($key, $datos){
		// Se decodifica la clave Base64
		$key = $this->decodeBase64($key);
		// Se decodifican los datos Base64
		$decodec = $this->base64_url_decode($datos);
		// Los datos decodificados se pasan al array de datos
		$this->stringToArray($decodec);
		// Se diversifica la clave con el Número de Pedido
		$key = $this->encrypt_3DES($this->getOrderNotif(), $key);
		// MAC256 del parámetro Ds_Parameters que envía Redsys
		$res = $this->mac256($datos, $key);
		// Se codifican los datos Base64
		return $this->base64_url_encode($res);	
	}
}
