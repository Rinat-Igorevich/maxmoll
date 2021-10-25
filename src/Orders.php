<?php
require $_SERVER['DOCUMENT_ROOT'] . '/helpers.php';

class Orders
{
    public static function getOrders($id = null)
    {
        $pdo = getPDO();
        $query = 'SELECT orders.id,
                        orders.customer,
                        orders.phone,
                        orders.created_at,
                        orders.completed_at,
                        orders.type,
                        u.name AS user,
                        u.id AS user_id,
                        orders.status,
                        product_id,
                        p.name AS product_name,
                        p.price,
                        p.stock,
                        oi.count,
                        oi.discount,
                        oi.cost
                FROM orders
                    LEFT JOIN users u ON u.id = orders.user_id
                    LEFT JOIN order_items oi ON oi.id = orders.id
                    LEFT JOIN products p ON p.id = oi.product_id';

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

    public static function createOrder()
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
        $pdo=null;
        $error.= Products::decreaseStock($_POST['product'], $_POST['count']);
        $error.= self::createOrderItems();

        return $error;
    }

    public static function createOrderItems()
    {
        $pdo = getPDO();

        $statement = $pdo->prepare(
                    'INSERT INTO order_items(product_id, count, discount, cost) 
                            VALUES(?, ?, ?, ?) 
                            ');
        $statement->execute([
                        intval($_POST['product']),
                        intval($_POST['count']),
                        intval($_POST['discount']),
                        intval($_POST['cost'])
        ]);
        $pdo = null;
        return 'productID ' . $_POST['product'];
    }

    public static function changeOrder()
    {
        $pdo = getPDO();
        $currentStatus = self::getCurrentOrderStatus($_POST['orderID']);
        $date = null;

        if ($currentStatus == 'canceled' && $_POST['status'] != 'canceled') {
            Products::decreaseStock($_POST['product'], $_POST['count']);

        } elseif ($currentStatus != 'canceled' && $_POST['status'] == 'canceled') {
            Products::increaseStock($_POST['product'], $_POST['count']);

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

        $error = $statement->errorCode();
        $error .= self::changeOrderItems($_POST['orderID']);
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

    public static function changeOrderItems($id)
    {
        $pdo = getPDO();
        $statement = $pdo->prepare(
            'UPDATE order_items SET
                            product_id = ?,
                            count = ?,
                            discount = ?,
                            cost = ? 
                   WHERE id = ?
                            ');
        $statement->execute([
            $_POST['product'],
            $_POST['count'],
            $_POST['discount'],
            $_POST['cost'],
            $id
        ]);
        $pdo = null;
        return $statement->errorCode();
    }

    public static function getReport()
    {
        $pdo = getPDO();

        $statement = $pdo->prepare("SELECT DATE(completed_at) AS date, sum(oi.cost) AS sum, count(*) AS count
                                FROM orders
                                    LEFT JOIN order_items AS oi ON oi.id=orders.id
                                WHERE status = 'completed'
                                GROUP BY DATE(completed_at)
                                ORDER BY date DESC
                                ");
        $statement->execute();
        $report = $statement->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;

        return $report;
    }
}