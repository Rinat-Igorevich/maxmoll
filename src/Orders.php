<?php
require $_SERVER['DOCUMENT_ROOT'] . '/helpers.php';

class Orders
{
    public static function getOrders($id = null)
    {
        $pdo = getPDO();
        $query = 'SELECT * FROM orders';

        if ($id != null) {
            $query .= ' WHERE orders.id =' . $id;
        }

        $query .= ' ORDER BY created_at DESC';

        $statement = $pdo->prepare($query);
        $statement->execute();
        $orders = $statement->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $orders;
    }

    public static function createOrder($orderItemsToCreate)
    {
        $pdo = getPDO();
        $query = 'INSERT INTO orders(
                               customer, 
                               phone,  
                               user_id, 
                               created_at,
                               type,
                               status
                             )
                        values (?, ?, ?, ?, ?, ?) ';
        $statement = $pdo->prepare($query);
        $statement->execute([
                     $_POST['customer'],
                     $_POST['phone'],
                     $_POST['user'],
                     date('Y-m-d H:i:s'),
                     $_POST['type'],
                     'active',
                    ]);

        $error = $statement->errorCode();

        $error.= self::createOrderItems($orderItemsToCreate, $pdo->lastInsertId());
        $pdo=null;

        return $error;
    }

    public static function createOrderItems($orderItemsToCreate, $order_id)
    {
        $pdo = getPDO();
        Products::decreaseStock($orderItemsToCreate);
        foreach ($orderItemsToCreate as $key => $value) {
            $statement = $pdo->prepare('
                                        INSERT INTO order_items(order_id, product_id, count, discount, cost) 
                                            VALUES(?, ?, ?, ?, ?) 
                                      ');
            $statement->execute([
                                    $order_id,
                                    intval($value['id']),
                                    intval($value['count']),
                                    intval($value['discount']),
                                    floatval($value['sum'])
                                ]);
        }
        $pdo = null;
        return '';
    }

    public static function changeOrder($orderItemsToChange)
    {
        $pdo = getPDO();
        $currentStatus = self::getCurrentOrderStatus($_POST['orderID']);
        $date = null;

        if ($currentStatus == 'canceled' && $_POST['status'] != 'canceled') {
            Products::decreaseStock($orderItemsToChange);

        } elseif ($currentStatus != 'canceled' && $_POST['status'] == 'canceled') {
            Products::increaseStock($orderItemsToChange);

        } elseif ($currentStatus != 'completed' && $_POST['status'] == 'completed') {
            $date = date('Y-m-d H:i:s');
        } else {
            $date = null;
        }

        $query = 'UPDATE orders SET 
                               customer = ?, 
                               phone = ?,  
                               user_id = ?, 
                               completed_at = ?,
                               type = ?,
                               status = ?
                        WHERE id = ?';
        $statement = $pdo->prepare($query);
        $statement->execute([
                             $_POST['customer'],
                             $_POST['phone'],
                             $_POST['user'],
                             $date,
                             $_POST['type'],
                             $_POST['status'],
                             $_POST['orderID']
                             ]);

        $error = self::changeOrderItems($orderItemsToChange, $_POST['orderID']);
        $pdo = null;
        return $error;
    }

    public static function getCurrentOrderStatus($id)
    {
        $pdo = getPDO();
        $statement = $pdo->prepare('SELECT status FROM orders WHERE id = ?');
        $statement->execute([$id]);
        $status = $statement->fetchColumn();
        $pdo = null;

        return $status;
    }

    public static function getOrderItems($id)
    {
        $pdo = getPDO();
        $statement = $pdo->prepare('SELECT order_id, product_id AS id, count, discount, cost, p.stock, p.price FROM order_items
                                             LEFT JOIN products p on p.id = order_items.product_id
                                          WHERE order_id=?');
        $statement->execute([$id]);
        $orderItems = $statement->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $orderItems;
    }

    public static function changeOrderItems($orderItemsToChange, $orderId)
    {
        self::deleteOrderItems($orderId);
        self::createOrderItems($orderItemsToChange, $orderId);

        return '';
    }

    public static function deleteOrderItems($orderID)
    {
        $pdo = getPDO();
        $orderItems = self::getOrderItems($orderID);
        Products::increaseStock($orderItems);
        $statement = $pdo->prepare('DELETE FROM order_items WHERE order_id=?');
        $statement->execute([$orderID]);
        $pdo = null;
    }

    public static function getReport()
    {
        $pdo = getPDO();

        $statement = $pdo->prepare("SELECT COUNT(DISTINCT order_id) AS count, DATE(completed_at) as date, sum(cost) as sum FROM orders
                                                LEFT JOIN order_items oi on orders.id = oi.order_id
                                            WHERE status = 'completed'
                                            GROUP BY DAY(completed_at)
                                  ");
        $statement->execute();
        $report = $statement->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;

        return $report;
    }
}