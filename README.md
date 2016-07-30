# Restful Client 
RestfulClient是最轻量的Restful PHP 客户端库

# 如何导入

```
include '../src/Restful/Client.php';
include '../src/Restful/Exception.php';

class_alias('\Restful\Client', '\RestfulClient');

```


# 配置HostAlias

```
$hostalias = [
    'order' => 'https://orderapi.xxx.com/v1',
    'product' => 'https://productapi.xxx.com/v1',
];

//设置HostAlias
\RestfulClient::hostalias($hostalias);

//可以随时切换到不同的api
//访问order
\RestfulClient::host('order')->...
//访问商品
\RestfulClient::host('proudct')->...


```

# 如何设置Http Basic Authorization

```
\RestfulClient::auth('username', 'token');

```


# Restful动作
支持 GET， POST， PUT， DELETE，PATCH 五个http mothod


# Restful请求路径

比如 GET /orders/{order_id}/weixinpay

```
\RestfulClient::host('order')->orders('{order_id}')->weixinpay()->get(
 [
   'order_id' => 123456,
   'limit' => 0
 
 ]
);

```
每个//之间资源都可以作为方法名称调用，比如/orders/ 可以调用方法orders， 方法参数也作为访问的路径自动添加。

# 手动设置Restful请求路径

```
\RestfulClient::host('order')->path('/orders/{order_id}/weixinpay')->get(
 [
   'order_id' => 123456,
   'limit' => 0
 
 ]
);

```
调用path方法手动设置请求路径。

# 设置POST、PUT请求数据格式
设置x-www-form-urlencoded和 json格式调用format方法，默认是json格式。

```
\RestfulClient::host('order')->orders()->format->('urlencode')->post(
 [
   'product_id' => 123456,
   'order_count' => 2
 
 ]
);
```



