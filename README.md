# php-walmart-orders
沃尔玛订单下载，跟踪号上传
示例应用，可以参考Demo.php文件，传递参数结果API说明文档

引入文件方式：

第一种方式：
先下载这个包，然后修改 项目下的composer.json
添加以下内容
    "require": {
	........
        "lin3615/walmart_orders": "@dev"
    },

	"repositories": [
		{
			"type":"path",
			"url":"/绝对路径/walmart_orders"
		}	
	],


第二种方式：
直接在根目录执行以下命令

composer require  lin3615/walmart_orders:@dev

