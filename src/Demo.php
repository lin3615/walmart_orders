<?php
/*
 * @Description: 沃尔玛订单下载，上传跟踪号示例
 * @Author: lin3615
 * @Date: 2021-02-05 14:06:46
 * @LastEditTime: 2021-02-06 10:29:56
 * @LastEditors: lin3615
 * @Reference: 
 */

namespace Lin3615\demo;
use Lin3615\WalmartOrders\Orders;

include_once 'Orders.php';

class Demo {
	/**
	 * 上传跟踪号-美国站
	 */
	function shipOrderLinesUs($clientId,$clientSecret,$purchaseOrderId,$param) {
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->shipOrderLinesUs($clientId,$clientSecret,$purchaseOrderId,$param);
		print_r($orderInfoList);
	}

	/**
	 * 修改订单状态-美国站
	 */
	function acknowledgeOrdersUs($clientId,$clientSecret,$purchaseOrderId){
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->acknowledgeOrdersUs($clientId,$clientSecret,$purchaseOrderId);
		print_r($orderInfoList);
	}

	/**
	 * 获取要发货的订单-美国站
	 */
	function allReleasedOrdersUs($clientId,$clientSecret,$param) {
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->allReleasedOrdersUs($clientId,$clientSecret,$param);
		print_r($orderInfoList);
	}
	

	/**
	 * 获取所有订单-美国站
	 */
	function allOrdersUs($clientId,$clientSecret,$param) {
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->allOrdersUs($clientId,$clientSecret,$param);
		print_r($orderInfoList);
	}

	/**
	 * 上传跟踪号-加拿大站
	 */
	function shippingUpdatesCa($clientId,$clientSecret,$token,$purchaseId,$param) {
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->shippingUpdatesCa($clientId,$clientSecret,$token,$purchaseId,$param);
		print_r($orderInfoList);
	}
	/**
	 * 获取所有订单-加拿大站
	 */
	function getAllOrdersCa($clientId,$clientSecret,$token,$param) {
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->getAllOrdersCa($clientId,$clientSecret,$token,$param);
		print_r($orderInfoList);
	}
	/**
	 * 获取要发货的订单-加拿大站
	 */
	function getAllReleasedOrdersCa($clientId,$clientSecret,$token,$param) {
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->getAllReleasedOrdersCa($clientId,$clientSecret,$token,$param);
		print_r($orderInfoList);
	}

	/**
	 * 通知修改订单状态-加拿大站
	 */
	function acknowledgeOrdersCa($clientId,$clientSecret,$token,$purchaseId) {
		$ordersObject = new Orders();
		$orderInfoList = $ordersObject->acknowledgeOrdersCa($clientId,$clientSecret,$token,$purchaseId);
		print_r($orderInfoList);
	}

	
}

// 订单上传跟踪号-美国站

$clientId = '';
$clientSecret = '';
$purchaseId = '';
$tracknumber = '';
$param = array('tracknumber' => $tracknumber,
		'carrier' => 'USPS',
		'linenumber' => 6,
		'trackingURL' => 'https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=' . $tracknumber
);

$demo = new Demo();
$demo->shipOrderLinesUs($clientId,$clientSecret,$purchaseId,$param);




// 修改订单状态-美国站
/*
$clientId = '';
$clientSecret = '';
$purchaseId = '';

$demo = new Demo();
$demo->acknowledgeOrdersUs($clientId,$clientSecret,$purchaseId);
*/

// 获取要发货的订单-美国站
/*
$clientId = '';
$clientSecret = '';
$param = array('createdStartDate' => '2020-02-04','limit' => 2);

$demo = new Demo();
$demo->allReleasedOrdersUs($clientId,$clientSecret,$param);
*/


// 获取订单-美国站
/*
$clientId = '';
$clientSecret = '';
$param = array();

$demo = new Demo();
$demo->allOrdersUs($clientId,$clientSecret,$param);
*/



// 上传跟踪号-加拿大站
/*
$clientId = '';
$clientSecret = '';
$purchaseId = '';
$token = '';
$param = array('lineNumber' => '1',
				'trackingNumber' => ' ',
				'carrier' => 'CPC'
			);

$demo = new Demo();			
$demo->shippingUpdatesCa($clientId,$clientSecret,$token,$purchaseId,$param);
*/

// 通知修改订单状态-加拿大站
/*
$clientId = '';
$clientSecret = '';
$purchaseId = '';
$token = '';
$demo = new Demo();
$demo->acknowledgeOrdersCa($clientId,$clientSecret,$token,$purchaseId);
*/


// 获取要发货的订单-加拿大站
/*
$clientId = '';
$clientSecret = '';
$param = array('createdStartDate' => Optional,
				'createdEndDate' => Optional,
				'limit' => Optional
				'productInfo' => Optional,
				'nextCursor' => Optional
			);
$token = '';
$demo = new Demo();
$orderInfoList = $demo->getAllReleasedOrdersCa($clientId,$clientSecret,$token,$param);
print_r($orderInfoList);
*/

// 获取所有订单-加拿大站
/*
$clientId = '';
$clientSecret = '';
$param = array('sku' => optional,
			   'customerOrderId' => optional,
				'purchaseOrderId' => optional,
				'status' => optional,
				'createdStartDate' => required,
				'createdEndDate' => optional,
				'fromExpectedShipDate' => optional,
				'toExpectedShipDate' => optional,
				'limit' => optional,
				'productInfo' => optional,
				'nextCursor' => optional,
			);
$token = '';
$demo = new Demo();
$demo->getAllOrdersCa($clientId,$clientSecret,$token,$param);
*/

