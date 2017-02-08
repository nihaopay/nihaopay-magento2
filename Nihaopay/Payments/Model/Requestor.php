<?php
namespace Nihaopay\Payments\Model;

class Requestor
{
	private $debug = false;
	
	public function __construct(){
	
	}

	public function request($token, $payment,$amount){
		$order = $payment->getOrder();
	
		$httpClient = CurlClient::instance();
		$url = "";
		if($this->debug)
			$url = "https://apitest.nihaopay.com/v1.2/transactions/expresspay";
		else
			$url = "https://api.nihaopay.com/v1.2/transactions/expresspay";
		$headers = array("Authorization: Bearer " . $token);
		
		if($this->debug){
			//test account
			$params = array("amount"=>$order->getGrandTotal()*100
					,"card_type"=>"unionpay"
					,"currency"=>$order->getOrderCurrencyCode()
					,"card_number"=>'6221558812340000'
					,"card_exp_month"=>'11'
					,"card_exp_year"=>'2017'
					,"card_cvv"=>'123'
					,"description"=>sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail())
					);
		}else{
			$params = array("amount"=>$order->getGrandTotal()*100
					,"card_type"=>"unionpay"
					,"currency"=>$order->getOrderCurrencyCode()
					,"card_number"=>$payment->getCcNumber()
					,"card_exp_month"=>sprintf('%02d',$payment->getCcExpMonth())
					,"card_exp_year"=>$payment->getCcExpYear()
					,"card_cvv"=>$payment->getCcCid()
					,"description"=>sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail())
					);
		}
		list($rbody, $rcode, $rheaders) = $httpClient->request("post",$url,$headers,$params,false);
		$resp = $this->_interpretResponse($rbody, $rcode, $rheaders,$params);
		
		return $resp;
		
	}
	
	public function refund($token,$payment,$amount){
	
	    $transactionId = $payment->getParentTransactionId();
		$order = $payment->getOrder();

		$httpClient = CurlClient::instance();
		$url = "";
		if($this->debug)
			$url = "https://apitest.nihaopay.com/v1.2/transactions/" . $transactionId . "/refund";
		else
			$url = "https://api.nihaopay.com/v1.2/transactions/" . $transactionId . "/refund";
		$headers = array("Authorization: Bearer " . $token);

		$params = array("amount"=>$amount*100
				,"currency"=>$order->getOrderCurrencyCode()
				,"reason"=>''
				);
					
		list($rbody, $rcode, $rheaders) = $httpClient->request("post",$url,$headers,$params,false);
		$resp = $this->_interpretResponse($rbody, $rcode, $rheaders,$params);
		
		return $resp;
					
					
	}
	private function _interpretResponse($rbody, $rcode, $rheaders,$params)
    {
        try {
            $resp = json_decode($rbody, true);
        } catch (Exception $e) {
            $msg = "Invalid response body from API: $rbody "
              . "(HTTP response code was $rcode)";
            throw new Error_Api($msg, $rcode, $rbody);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleApiError($rbody, $rcode, $rheaders, $resp,$params);
        }
        return $resp;
    }
    public function handleApiError($rbody, $rcode, $rheaders, $resp,$param)
    {
        if (!is_array($resp) || !isset($resp['error'])) {
            $msg = "Invalid response object from API77777777: $rbody "
              . "(HTTP response code was $rcode)";
        }

        $error = isset($resp['error']) ? $resp['error']:$rcode ;
        $msg = isset($resp['message']) ? $resp['message'] : null;
        $code = isset($error['code']) ? $error['code'] : null;

        throw new Error_Api($msg,$param, $rcode, $rbody, $resp, $rheaders);

    }
    
    public function setDebug($debug){
    	$this->debug = $debug;
    }
    public function getDebug(){
    	return $this->debug ;
    }
	protected function log($msg)
    {
        Mage::log("Requestor - ".$msg);
    }    
    
    public function getSecureForm($token, $params){
    
		$httpClient = CurlClient::instance();
		$url = "";
		if($this->debug)
			$url = "https://apitest.nihaopay.com/v1.2/transactions/securepay";
		else
			$url = "https://api.nihaopay.com/v1.2/transactions/securepay";
		$headers = array("Authorization: Bearer " . $token);
		
		$this->log('send params to '.$url .' with head' . print_r($headers,true));
		$this->log('params:'. print_r($params,true));
		
		list($rbody, $rcode, $rheaders) = $httpClient->request("post",$url,$headers,$params,false);
		$this->log($rbody);

		$resp = $this->_interpretResponse($rbody, $rcode, $rheaders,$params);
		
		return $rbody;
    }

    function getReferenceCode($order_id){

    	$tmstemp = time();
        return $order_id . 'at' . $tmstemp;
    }
	
    
}
