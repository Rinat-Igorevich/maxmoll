<?php
require 'config.php';

function getPDO()
{
    $dsn = 'mysql:host=' . DB_CONNECTION_HOST . ';
                dbname=' . DB_CONNECTION_DB_NAME;

    return new PDO(
        $dsn,
        DB_CONNECTION_USER_NAME,
        DB_CONNECTION_PASSWORD
    );
}

function getNewOrderItems()
{
    $orderItemsToCreate = [];
    foreach ($_POST as $key=>$value) {

        if (stristr($key, '_', true) == 'product') {
            $id = explode('_', $key)[1];
            $orderItemsToCreate[$id]['id'] = $value;
        }
        if (stristr($key, '_', true) == 'count') {
            $id = explode('_', $key)[1];
            $orderItemsToCreate[$id]['count'] = $value;
        }
        if (stristr($key, '_', true) == 'discount') {
            $id = explode('_', $key)[1];
            $orderItemsToCreate[$id]['discount'] = $value;
        }
        if (stristr($key, '_', true) == 'sum') {
            $id = explode('_', $key)[1];
            $orderItemsToCreate[$id]['sum'] = $value;
        }
    }
    return $orderItemsToCreate;
}