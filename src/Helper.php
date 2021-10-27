<?php

class Helper
{
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
}