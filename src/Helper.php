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
    /*
     * ф-я сравнивает текущий и новый статус заказа
     * возвращает текущую дату, если новый статус completed и null, если нет
     * после изменения заказа вызывается ф-я изменения записей в таблице заказ-товары
     * которая принимает массив новых товаров и id измененного заказа
     */
    public static function checkStatus($currentStatus, $newStatus, $orderItemsToChange)
    {
        $date = null;

            if ($currentStatus == 'canceled' && $newStatus != 'canceled') {
                Products::decreaseStock($orderItemsToChange);
            } elseif ($currentStatus != 'canceled' && $newStatus == 'canceled') {
                Products::increaseStock($orderItemsToChange);
            } elseif ($newStatus == 'completed') {
                $date = date('Y-m-d H:i:s');
            } else {
                $date = null;
            }

        return $date;
    }
    /*
     * ф-я формирует новый список товаров заказа из массива POST
     * возвращает массив с товарами
     */
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