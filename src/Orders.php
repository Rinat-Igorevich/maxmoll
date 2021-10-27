<?php
require $_SERVER['DOCUMENT_ROOT'] . '/helpers.php';

class Orders
{
    /*
     * ф-я для получения всех заказов из БД (если не передан id)
     * если передан id заказа - возвращает только этот заказ
     * сортирует по дате закрытия заказа (по убыванию)
     * возвращает заказы (заказ) в том виде в котором они хранятся в таблице БД
     */
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
    /*
     * ф-я создает новый заказ в БД
     * принимает массив товаров
     * после создания заказа создает запись в таблице 'заказ-товары'
     * в функцию создания записи таблицы 'заказ-товары' передается
     * полученный на вход массив и id записи полученной при создании заказа
     * возвращает коды ошибок которые возникли при выполнении запросов (одной строкой)
     */
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

        $pdo = null;

        return $error;
    }
    /*
     * функция добавляет запись(записи) в таблицу заказ-товары
     * принимает массив товаров и id заказа
     * вызывает ф-ю, которая уменьшает остатки по товарам (принимает массив товаров полученный на входе)
     * возвращает код ошибки(ошибок) одной строкой
     */
    public static function createOrderItems($orderItemsToCreate, $order_id)
    {
        $pdo = getPDO();
        Products::decreaseStock($orderItemsToCreate);
        $error = '';
        foreach ($orderItemsToCreate as $key => $value) {
            $statement = $pdo
                ->prepare('
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
            $error .= $statement->errorCode();
        }
        $pdo = null;

        return $error ;
    }
    /*
     * ф-я изменения заказа, принимает на вход массив товаров
     * вызывает, ф-ю которая сравнивает текущий и
     * новый статус (если стал отменен - увеличивает остатки по товарам,
     * если был отменен, а стал нет - уменьшает остатки).
     * ф-я возвращает текущую дату, если новый статус completed и null, если нет
     * после изменения заказа вызывается ф-я изменения записей в таблице заказ-товары
     * которая принимает массив новых товаров и id измененного заказа
     * возвращает коды ошибок (одной строкой)
     */
    public static function changeOrder($orderItemsToChange)
    {
        $newStatus = $_POST['status'];

        $currentStatus = self::getCurrentOrderStatus($_POST['orderID']);

        $date = Helper::checkStatus($currentStatus, $newStatus, $orderItemsToChange);

        $pdo = getPDO();
        $error = '';
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
        $error .= $statement->errorCode();
        $error .= self::changeOrderItems($orderItemsToChange, $_POST['orderID']);
        $pdo = null;
        return $error;
    }
    /*
     * ф-я возвращает текущий статус заказа
     * принимает id заказа
     */
    public static function getCurrentOrderStatus($id)
    {
        $pdo = getPDO();
        $statement = $pdo->prepare('SELECT status FROM orders WHERE id = ?');
        $statement->execute([$id]);
        $status = $statement->fetchColumn();
        $pdo = null;

        return $status;
    }
    /*
     * ф-я получения товаров заказа (+остаток +цена
     * принимает id заказа
     * !id товара именуется как просто id
     * возвращает массив товаров
     */
    public static function getOrderItems($id)
    {
        $pdo = getPDO();
        $statement = $pdo
            ->prepare('SELECT order_id, 
                                    product_id AS id, 
                                    count, 
                                    discount, 
                                    cost, 
                                    p.stock, 
                                    p.price 
                             FROM order_items
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