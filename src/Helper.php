<?php

require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

class Helper
{
    public static function getPDO()
    {
        $dsn = 'mysql:host=' . DB_CONNECTION_HOST . ';
                dbname=' . DB_CONNECTION_DB_NAME;

        return new PDO(
            $dsn,
            DB_CONNECTION_USER_NAME,
            DB_CONNECTION_PASSWORD
        );
    }

    public static function checkStatus($currentStatus, $newStatus, $orderItemsToChange)
    {
        $date = null;
        if ($currentStatus == 'canceled' && $newStatus != 'canceled') {
            Products::decreaseStock($orderItemsToChange);
        } elseif ($currentStatus != 'canceled' && $newStatus == 'canceled') {
            Products::increaseStock($orderItemsToChange);
        } elseif ($currentStatus != 'completed' && $newStatus == 'completed') {
            $date = date('Y-m-d H:i:s');
        } else {
            $date = null;
        }
        return $date;
    }

    public static function getNewOrderItems()
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
}