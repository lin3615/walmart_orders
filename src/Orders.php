<?php
/*
 * @Description: 沃尔玛订单下载，上传跟踪号 (美国站，加拿大站)
 * @Author: lin3615
 * @Date: 2021-02-04 10:39:57
 * @LastEditTime: 2021-02-06 10:30:33
 * @LastEditors: lin3615
 * @Reference: 
 */
namespace Lin3615\WalmartOrders;
class Orders 
{
    private $prefixUrlUs = 'https://marketplace.walmartapis.com';
    private $tokenUrlUs = '/v3/token';
    private $allOrdersUrlUs = '/v3/orders';
    private $releasedUrlOrderUs = '/v3/orders/released';
    private $accessTokenList = array();

    private $releasedUrlOrderCa = '/v3/ca/orders/released';
    private $allOrdersUrlCa = '/v3/ca/orders';

    /**
     * 上传跟踪号-加拿大站
     */
    public function shippingUpdatesCa($accessKey,$accessSecret,$accessToken,$purchaseId,$param)
    {
        $url = $this->prefixUrlUs . $this->allOrdersUrlCa . '/'.$purchaseId.'/shipping';
        $requestMethod = 'POST';//Request method type i.e GET, POST
        $timestamp = round(microtime(true) * 1000); //Current system timestamp
        $qos = uniqid();
        $sign = $this->_GetWalmartAuthSignature($url, $requestMethod, $timestamp,$accessKey,$accessToken);

        $header = array(
            "WM_SVC.NAME: marketplace.walmartapis.com",
            "WM_TENANT_ID: WALMART.CA",
            "WM_LOCALE_ID: en_CA",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "WM_SEC.TIMESTAMP: ".$timestamp,
            "WM_SEC.AUTH_SIGNATURE: ".$sign,
            "WM_CONSUMER.CHANNEL.TYPE: " . $accessSecret,
            "WM_CONSUMER.ID: ".$accessKey,
            "Content-Type: application/json",
            "Accept: application/json",
            "Host: marketplace.walmartapis.com"
        );
        $uploadData = $this->getDataCa($param);
        return $this->curlRequest($url,$header,$requestMethod,$uploadData);
    }

    /**
     * 组织标发数据-加拿大站
     */
    private function getDataCa($param) {
        $lineNumber = $param['lineNumber'];
        $trackingNumber = $param['trackingNumber'];
        $trackingURL = null;
        if(isset($param['trackingURL']) && $param['trackingURL']) {
            $trackingURL = $param['trackingURL'];
        }
        $methodCode = 'Standard';
        $methodCodeList = array('Standard','Express','Oneday','Freight');
        if(isset($param['methodCode']) && in_array($param['methodCode'],$methodCodeList)) {
            $methodCode = $param['methodCode'];
        }

        $carrierNameInfo = array('otherCarrier' => null,'carrier' => null);
        if(isset($param['otherCarrier']) && $param['otherCarrier']) {
            $carrierNameInfo['otherCarrier'] = $param['otherCarrier'];
        }
        if(isset($param['carrier']) && $param['carrier']) {
            $carrierNameInfo['carrier'] = $param['carrier'];
        }

        $postDatas = array(
            'orderShipment' => array(
                'orderLines' => array(
                   'orderLine' => array(array(
                       'lineNumber' => $lineNumber,
                       'orderLineStatuses' => array(
                           'orderLineStatus' => array(array(
                               'status' => 'Shipped',
                               'statusQuantity' => array(
                                   'unitOfMeasurement' => 'Each',
                                   'amount' => 1,
                                  ),
                               'trackingInfo' => array(
                                   'shipDateTime' => time() * 1000,
                                   'methodCode' => 'Standard',
                                   'carrierName' => $carrierNameInfo,
                                   'trackingNumber' => $trackingNumber,
                                   'trackingURL' => $trackingURL
                               ),
                           )
                          )
                       )
                   )) 
                )
            )
        );
        return json_encode($postDatas);
    }

    /**
     * 通知平台订单-加拿大站
     */
    public function acknowledgeOrdersCa($accessKey,$accessSecret,$accessToken,$purchaseId) {
        $url = $this->prefixUrlUs . $this->allOrdersUrlCa . '/'.$purchaseId.'/acknowledge'; 
        $requestMethod = 'POST';//Request method type i.e GET, POST
        $timestamp = round(microtime(true) * 1000); //Current system timestamp
        $qos = uniqid();
        $sign = $this->_GetWalmartAuthSignature($url, $requestMethod, $timestamp,$accessKey,$accessToken);
        $header = array(
            "WM_SVC.NAME: marketplace.walmartapis.com",
            "WM_TENANT_ID: WALMART.CA",
            "WM_LOCALE_ID: en_CA",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "WM_SEC.TIMESTAMP: ".$timestamp,
            "WM_SEC.AUTH_SIGNATURE: ".$sign,
            "WM_CONSUMER.CHANNEL.TYPE: " . $accessSecret,
            "WM_CONSUMER.ID: ".$accessKey,
            "Content-Type: application/xml",
            "Accept: application/xml",
            "Host: marketplace.walmartapis.com"
        );
        print_r($header);
        return $this->curlRequest($url,$header,$requestMethod);
    }

    /**
     * 获取要发货的订单-加拿大站
     */
    public function getAllReleasedOrdersCa($accessKey,$accessSecret,$accessToken,$param) {
        $requestStr = '?';
        if(isset($param['nextCursor']) && $param['nextCursor']){
            $requestStr = $param['nextCursor'] . '&';
        }else{
            if(isset($param['createdStartDate']) && $param['createdStartDate']) {
                $requestStr .= 'createdStartDate=' . $param['createdStartDate'] .'&';
            }
            if(isset($param['createdEndDate']) && $param['createdEndDate']) {
                $requestStr .= 'createdEndDate=' . $param['createdEndDate'] .'&';
            }

            if(isset($param['limit']) && $param['limit'] > 0 && $param['limit'] <= 200) {
                $requestStr .= 'limit=' . $param['limit'] .'&';
            }

            if(isset($param['productInfo']) && $param['productInfo']) {
                $requestStr .= 'productInfo=' . $param['productInfo'] .'&';
            }
        }

       

        $url = $this->prefixUrlUs .$this->releasedUrlOrderCa . substr($requestStr,0,-1);
        $requestMethod = 'GET';
        $timestamp = round(microtime(true) * 1000); 
        $qos = uniqid();
        
        $sign = $this->_GetWalmartAuthSignature($url, $requestMethod, $timestamp,$accessKey,$accessToken);
        $header = array(
            "WM_SVC.NAME: marketplace.walmartapis.com",
            "WM_TENANT_ID: WALMART.CA",
            "WM_LOCALE_ID: en_CA",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "WM_SEC.TIMESTAMP: ".$timestamp,
            "WM_SEC.AUTH_SIGNATURE: ".$sign,
            "WM_CONSUMER.CHANNEL.TYPE: " . $accessSecret,
            "WM_CONSUMER.ID: ".$accessKey,
            "Content-Type: multipart/form-data",
            "Accept: application/json",
            "Host: marketplace.walmartapis.com"
        );
        return $this->curlRequest($url,$header);
    }
    /**
     * 获取订单-加拿大站
     */
    public function getAllOrdersCa($accessKey,$accessSecret,$accessToken,$param) {
        $requestStr = '?';
        if(isset($param['nextCursor']) && $param['nextCursor']){
            $requestStr = $param['nextCursor'] . '&';
        }else{
            if(isset($param['createdStartDate']) && $param['createdStartDate']) {
                $requestStr .= 'createdStartDate=' . $param['createdStartDate'] .'&';
            }
            if(isset($param['createdEndDate']) && $param['createdEndDate']) {
                $requestStr .= 'createdEndDate=' . $param['createdEndDate'] .'&';
            }

            if(isset($param['limit']) && $param['limit'] > 0 && $param['limit'] <= 200) {
                $requestStr .= 'limit=' . $param['limit'] .'&';
            }

            if(isset($param['productInfo']) && $param['productInfo']) {
                $requestStr .= 'productInfo=' . $param['productInfo'] .'&';
            }

            if(isset($param['shipNodeType']) && $param['shipNodeType']) {
                $requestStr .= 'shipNodeType=' . $param['shipNodeType'] .'&';
            }

            if(isset($param['sku']) && $param['sku']) {
                $requestStr .= 'sku=' . $param['sku'] .'&';
            }

            if(isset($param['customerOrderId']) && $param['customerOrderId']) {
                $requestStr .= 'customerOrderId=' . $param['customerOrderId'] .'&';
            }

            if(isset($param['purchaseOrderId']) && $param['purchaseOrderId']) {
                $requestStr .= 'purchaseOrderId=' . $param['purchaseOrderId'] .'&';
            }

            if(isset($param['fromExpectedShipDate']) && $param['fromExpectedShipDate']) {
                $requestStr .= 'fromExpectedShipDate=' . $param['fromExpectedShipDate'] .'&';
            }

            if(isset($param['toExpectedShipDate']) && $param['toExpectedShipDate']) {
                $requestStr .= 'toExpectedShipDate=' . $param['toExpectedShipDate'] .'&';
            }

            if(isset($param['shippingProgramType']) && $param['shippingProgramType']) {
                $requestStr .= 'shippingProgramType=' . $param['shippingProgramType'] .'&';
            }
        }

        $url = $this->prefixUrlUs .$this->allOrdersUrlCa . substr($requestStr,0,-1);
        $requestMethod = 'GET';
        $timestamp = round(microtime(true) * 1000); 
        $qos = uniqid();
        
        $sign = $this->_GetWalmartAuthSignature($url, $requestMethod, $timestamp,$accessKey,$accessToken);
        $header = array(
            "WM_SVC.NAME: marketplace.walmartapis.com",
            "WM_TENANT_ID: WALMART.CA",
            "WM_LOCALE_ID: en_CA",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "WM_SEC.TIMESTAMP: ".$timestamp,
            "WM_SEC.AUTH_SIGNATURE: ".$sign,
            "WM_CONSUMER.CHANNEL.TYPE: " . $accessSecret,
            "WM_CONSUMER.ID: ".$accessKey,
            "Content-Type: multipart/form-data",
            "Accept: application/json",
            "Host: marketplace.walmartapis.com"
        );

        return $this->curlRequest($url,$header);
    }
    
    private function _GetWalmartAuthSignature($url, $requestMethod, $timestamp,$accessKey,$accessToken) 
	{
		$authData = $accessKey."\n";
		$authData .= $url."\n";
		$authData .= $requestMethod."\n";
		$authData .= $timestamp."\n";
		$pem = $this->_ConvertPkcs8ToPem(base64_decode($accessToken));
		$privateKey = openssl_pkey_get_private($pem);
		$signature = '';
		$hash = defined("OPENSSL_ALGO_SHA256") ? OPENSSL_ALGO_SHA256 : "sha256";
		if (!openssl_sign($authData, $signature, $privateKey, $hash))
		{ 
			return base64_encode($signature);
		}
		return base64_encode($signature);
    
    }
    
    private function _ConvertPkcs8ToPem($der)
	{
		static $BEGIN_MARKER = "-----BEGIN PRIVATE KEY-----";
		static $END_MARKER = "-----END PRIVATE KEY-----";
		$key = base64_encode($der);
		$pem = $BEGIN_MARKER . "\n";
		$pem .= chunk_split($key, 64, "\n");
		$pem .= $END_MARKER . "\n";
		return $pem;
	}

    



    
    /**
     * ack订单-美国站
     */
    public function acknowledgeOrdersUs($clientId,$clientSecret,$purchaseOrderId) {
        $url = $this->prefixUrlUs . $this->allOrdersUrlUs . '/' . $purchaseOrderId . '/acknowledge'; 
        $authorization = base64_encode($clientId.":".$clientSecret);
        $qos = uniqid();
        $accessTokenInfo = $this->getAccessToken($clientId,$clientSecret);
        if(isset($accessTokenInfo['access_token']) && $accessTokenInfo['access_token'])
        {
            $accessToken = $accessTokenInfo['access_token'];
        }else 
        {
            return "access获取失败:".json_encode($access_token_info);
        }
        $header = array(
            "WM_SVC.NAME: Walmart Marketplace",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "Authorization: Basic " . $authorization,
            "WM_SEC.ACCESS_TOKEN: " .$accessToken,
            "Content-Type: application/json",
            "Accept: application/json",
            "Host: marketplace.walmartapis.com"
        
        );

        $response = $this->curlRequest($url,$header,'POST');
        $responseArr = json_decode($response,true);
        return $responseArr;
    }
    /**
     * 订单上传跟踪号-美国站
     */
    public function shipOrderLinesUs($clientId,$clientSecret,$purchaseOrderId,$param) {
        $url = $this->prefixUrlUs . $this->allOrdersUrlUs . '/' . $purchaseOrderId . '/shipping'; 
        $accessTokenInfo = $this->getAccessToken($clientId,$clientSecret);
        if(isset($accessTokenInfo['access_token']) && $accessTokenInfo['access_token'])
        {
            $accessToken = $accessTokenInfo['access_token'];
        }else 
        {
            return "access获取失败:".json_encode($access_token_info);
        }
        $authorization = base64_encode($clientId.":".$clientSecret);
        $qos = uniqid();
        $tracknumber = '';
        $carrier = '';
        $linenumber = 1;
        $trackingURL = '';
        $otherCarrier = 'null';

        if(isset($param['tracknumber']) && $param['tracknumber']) {
            $tracknumber = $param['tracknumber'];
        }

        if(isset($param['carrier']) && $param['carrier']) {
            $carrier = $param['carrier'];
        }

        if(isset($param['linenumber']) && $param['linenumber']) {
            $linenumber = $param['linenumber'];
        }

        if(isset($param['trackingURL']) && $param['trackingURL']) {
            $trackingURL = $param['trackingURL'];
        }

        if(isset($param['otherCarrier']) && $param['otherCarrier']) {
            $otherCarrier = $param['otherCarrier'];
        } 
        
        $postData = $this->getXmlDataUs($tracknumber,$carrier,$linenumber,$trackingURL,$otherCarrier);
        $header = array(
            "WM_SVC.NAME: Walmart Marketplace",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "Authorization: Basic " . $authorization,
            "WM_SEC.ACCESS_TOKEN: " .$accessToken,
            "Content-Type: application/xml",
            "Accept: application/xml",
            "Host: marketplace.walmartapis.com"
        );
        $response = $this->curlRequest($url,$header,'POST',$postData);
		return $response;
    }

    /**
     * 发货传递数据-美国站
     * @param string  $tracknumber
     * @param string  $carrier
     * @param int     $linenumber
     * @param string  $trackingURL
     * @param string  $otherCarrier
     */
    private function getXmlDataUs($tracknumber,$carrier,$linenumber = 1,$trackingURL = null,$otherCarrier = null)
	{
		$datetime = date('Y-m-d\TH:i:s.0000\Z', time());
    	$message = "<orderLines>
		<orderLine>
		  <lineNumber>{$linenumber}</lineNumber>	
		  <orderLineStatuses>
			<orderLineStatus>
			  <status>Shipped</status>
			  <statusQuantity>
				<unitOfMeasurement>EACH</unitOfMeasurement>
				<amount>1</amount>
			  </statusQuantity>
			  <trackingInfo>
				<shipDateTime>{$datetime}</shipDateTime>
				<carrierName>
				    <otherCarrier>{$otherCarrier}</otherCarrier>
				    <carrier>{$carrier}</carrier>
				</carrierName>
				<methodCode>Standard</methodCode>
				<trackingNumber>{$tracknumber}</trackingNumber>
				<trackingURL>{$trackingURL}</trackingURL>
			  </trackingInfo>
			</orderLineStatus>
		  </orderLineStatuses>
		</orderLine>
	  </orderLines>";

        $shipInfo = <<<EOD
        <?xml version="1.0" encoding="UTF-8"?>
        <orderShipment xmlns="http://walmart.com/mp/v3/orders" xmlns:ns3="http://walmart.com/">
        {$message}
        </orderShipment>
        EOD;
        return $shipInfo;
	}

    /**
     * 根据状态下载订单-美国站
     */
    public function allOrdersUs($clientId,$clientSecret,$param) {
        $requestStr = '?';
        // 下一页
        if(isset($param['nextCursor']) && $param['nextCursor']){
            $requestStr = $param['nextCursor'] . '&';
        }else{
            if(isset($param['sku']) && $param['sku']) {
                $requestStr .= 'sku=' . $param['sku'] . '&';
            }

            if(isset($param['customerOrderId']) && $param['customerOrderId']) {
                $requestStr .= 'customerOrderId=' . $param['customerOrderId'] . '&';
            }

            if(isset($param['purchaseOrderId']) && $param['purchaseOrderId']) {
                $requestStr .= 'purchaseOrderId=' . $param['purchaseOrderId'] . '&';
            }

            if(isset($param['status']) && $param['status']) {
                $requestStr .= 'status=' . $param['status'] . '&';
            }

            if(isset($param['createdStartDate']) && $param['createdStartDate']) {
                $requestStr .= 'createdStartDate=' . $param['createdStartDate'] . '&';
            }

            if(isset($param['createdEndDate']) && $param['createdEndDate']) {
                $requestStr .= 'createdEndDate=' . $param['createdEndDate'] . '&';
            }

            if(isset($param['fromExpectedShipDate']) && $param['fromExpectedShipDate']) {
                $requestStr .= 'fromExpectedShipDate=' . $param['fromExpectedShipDate'] . '&';
            }

            if(isset($param['toExpectedShipDate']) && $param['toExpectedShipDate']) {
                $requestStr .= 'toExpectedShipDate=' . $param['toExpectedShipDate'] . '&';
            }

            if(isset($param['lastModifiedStartDate']) && $param['lastModifiedStartDate']) {
                $requestStr .= 'lastModifiedStartDate=' . $param['lastModifiedStartDate'] . '&';
            }

            if(isset($param['lastModifiedEndDate']) && $param['lastModifiedEndDate']) {
                $requestStr .= 'lastModifiedEndDate=' . $param['lastModifiedEndDate'] . '&';
            }

            if(isset($param['limit']) && $param['limit'] > 0 && $param['limit'] < 200) {
                $requestStr .= 'limit=' . $param['limit'] . '&';
            }

            if(isset($param['productInfo']) && $param['productInfo']) {
                $requestStr .= 'productInfo=' . $param['productInfo'] . '&';
            }

            if(isset($param['shipNodeType']) && $param['shipNodeType']) {
                $requestStr .= 'shipNodeType=' . $param['shipNodeType'] . '&';
            }

            if(isset($param['shippingProgramType']) && $param['shippingProgramType']) {
                $requestStr .= 'shippingProgramType=' . $param['shippingProgramType'] . '&';
            }

        }

        $accessTokenInfo = $this->getAccessToken($clientId,$clientSecret);
        if(isset($accessTokenInfo['access_token']) && $accessTokenInfo['access_token'])
        {
            $accessToken = $accessTokenInfo['access_token'];
        }else 
        {
            return "access获取失败:".json_encode($access_token_info);
        }

        $authorization = base64_encode($clientId.":".$clientSecret);
        $url = $this->prefixUrlUs . $this->allOrdersUrlUs . substr($requestStr,0,-1);
        $qos = uniqid();
        $header = array(
            "WM_SVC.NAME: Walmart Marketplace",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "Authorization: Basic " . $authorization,
            "WM_SEC.ACCESS_TOKEN: " .$accessToken,
            "Content-Type: application/json; charset=UTF-8",
            "Accept: application/json; charset=UTF-8",
            "Host: marketplace.walmartapis.com"
        
        );
        $response = $this->curlRequest($url,$header);
        $responseArray = json_decode($response,true);
        return $responseArray;
    }

    /**
     * 获取要通知要发货的订单 - 美国站
     * @param string $clientId
     * @param string $clientSecret
     * @param array $param
     * @return array | mix
     */
    public function allReleasedOrdersUs($clientId,$clientSecret,$param)
    {
        $requestStr = '?';
        // 下一页
        if(isset($param['nextCursor']) && $param['nextCursor']){
            $requestStr = $param['nextCursor'] . '&';
        }else{
            if(isset($param['createdStartDate']) && $param['createdStartDate']) {
                $requestStr .= 'createdStartDate=' . $param['createdStartDate'] . '&';
            }

            if(isset($param['createdEndDate']) && $param['createdEndDate']) {
                $requestStr .= 'createdEndDate=' . $param['createdEndDate'] . '&';
            }

            if(isset($param['productInfo']) && $param['productInfo']) {
                $requestStr .= 'productInfo=' . $param['productInfo'] . '&';
            }

            if(isset($param['shipNodeType']) && $param['shipNodeType']) {
                $requestStr .= 'shipNodeType=' . $param['shipNodeType'] . '&';
            }

            if(isset($param['sku']) && $param['sku']) {
                $requestStr .= 'sku=' . $param['sku'] . '&';
            }

            if(isset($param['customerOrderId']) && $param['customerOrderId']) {
                $requestStr .= 'customerOrderId=' . $param['customerOrderId'] . '&';
            }

            if(isset($param['purchaseOrderId']) && $param['purchaseOrderId']) {
                $requestStr .= 'purchaseOrderId=' . $param['purchaseOrderId'] . '&';
            }

            if(isset($param['fromExpectedShipDate']) && $param['fromExpectedShipDate']) {
                $requestStr .= 'fromExpectedShipDate=' . $param['fromExpectedShipDate'] . '&';
            }

            if(isset($param['toExpectedShipDate']) && $param['toExpectedShipDate']) {
                $requestStr .= 'toExpectedShipDate=' . $param['toExpectedShipDate'] . '&';
            }

            if(isset($param['shippingProgramType']) && $param['shippingProgramType']) {
                $requestStr .= 'shippingProgramType=' . $param['shippingProgramType'] . '&';
            }
            if(isset($param['limit']) && $param['limit'] > 0 && $param['limit'] <= 200) {
                $requestStr .= 'limit=' . $param['limit'] . '&';
            }
        }

        $accessTokenInfo = $this->getAccessToken($clientId,$clientSecret);
        if(isset($accessTokenInfo['access_token']) && $accessTokenInfo['access_token'])
        {
            $accessToken = $accessTokenInfo['access_token'];
        }else 
        {
            return "access获取失败:".json_encode($access_token_info);
        }

        $authorization = base64_encode($clientId.":".$clientSecret);
        $url = $this->prefixUrlUs . $this->releasedUrlOrderUs . substr($requestStr,0,-1);
        $qos = uniqid();
        $header = array(
            "WM_SVC.NAME: Walmart Marketplace",
            "WM_QOS.CORRELATION_ID: " . $qos,
            "Authorization: Basic " . $authorization,
            "WM_SEC.ACCESS_TOKEN: " .$accessToken,
            "Content-Type: application/json; charset=UTF-8",
            "Accept: application/json; charset=UTF-8",
            "Host: marketplace.walmartapis.com"
        
        );
        $response = $this->curlRequest($url,$header);
        $responseArray = json_decode($response,true);
        return $responseArray;
    }

    /**
     * 获取美国站的token
     * @param string $clientId
     * @param string $clientSecret
     * @return array | mix
     */
    private function getAccessToken($clientId,$clientSecret)
    {
        $authorization = base64_encode($clientId.":".$clientSecret);
        $nowTime = time();
        $accessKeyMd5 = md5($authorization);
        $accessKey = substr($accessKeyMd5,0,18);
        if(isset($this->accessTokenList[$accessKey])) {
            $sepTime = $this->accessTokenList[$accessKey]['dateline'] - $nowTime;
            if($sepTime > 30) {
               return  $this->accessTokenList[$accessKey];  
            }
        }
        $qos = uniqid();
        
        $ch = curl_init();
        
        $options = array(
            CURLOPT_URL => $this->prefixUrlUs . $this->tokenUrlUs,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HEADER => false,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic ".$authorization,
                "Content-Type: application/x-www-form-urlencoded",
                "Accept: application/json",
                "WM_SVC.NAME: Walmart Marketplace",
                "WM_QOS.CORRELATION_ID: ".$qos,
                "WM_SVC.VERSION: 1.0.0"
            ),
        );
        curl_setopt_array($ch, $options);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);

        $response = curl_exec($ch);
        if($response === FALSE ){
            echo "CURL Error:".curl_error($ch).PHP_EOL;
            return curl_error($ch);
        }
        $arr = json_decode($response, true);
        $this->accessTokenList[$accessKey]['dateline'] = $nowTime + $arr['expires_in'];  
        $this->accessTokenList[$accessKey]['access_token'] = $arr['access_token'];
        return $arr;
    }

    /**
     * 发起请求
     */
    private function curlRequest($url,$headers=array(), $postMethod='GET', $postData = '', $req=''){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if(strtoupper($postMethod) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if(is_array($postData)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }
        if(!empty($headers)) {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER , $headers);
        }
        if(!empty($req)){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $req);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $data;
    }

}