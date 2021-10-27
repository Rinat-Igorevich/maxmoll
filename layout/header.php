<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require $_SERVER['DOCUMENT_ROOT'] . '/src/Orders.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/Products.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/Users.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/Helper.php';

$products = Products::getProducts();
$users = Users::getUsers();

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'getProduct':
            $product = Products::getProducts($_POST['id']);
            $result = ['stock' => $product];
            die(json_encode($result));

        case 'getOrders':
            $orders = Orders::getOrders($_POST['id']);
            $result = ['orders' => $orders];
            die(json_encode($result));

        case 'createOrder':
            $orderItemsToCreate = Helper::getNewOrderItems();
            $orders = Orders::createOrder($orderItemsToCreate);
            $result = ['orders' => $orders, 'text' => ' создан'];
            break;

        case 'changeOrder':
            $orderItemsToCreate = Helper::getNewOrderItems();
            $orders = Orders::changeOrder($orderItemsToCreate);
            $result = ['orders' => $orders, 'text' => ' изменен'];
            break;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="/scripts.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <style>
        TH {
            vertical-align: middle;
        }
        TD INPUT {
            width: 110px;
        }
    </style>

    <title>СУЗ</title>
</head>
<header class="d-flex justify-content-center py-3">
    <ul class="nav nav-pills">
        <? foreach ($menu as $name => $ref): ?>
        <li class="nav-item"><a href="<?= $ref?>" class="nav-link"><?= $name?></a></li>
        <? endforeach;?>
    </ul>
</header>
<body>
