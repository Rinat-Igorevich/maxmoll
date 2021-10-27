<?php
require '../header.php';
$orders = Orders::getOrders();

?>
<h3>Заказы</h3>
<hr>
<div class="container">
    <form action="create">
        <button class="btn btn-primary">Создать заказ</button>
    </form>
    <hr>
    <form action="change" method="get">
        <table class="table table-bordered table-striped table-sm ">
            <thead style="border: darkgray">
                <tr style="text-align: center">
                    <th rowspan="2" scope="rowgroup">№</th>
                    <th scope="rowgroup">Покупатель</th>
                    <th scope="col">Дата создания</th>
                    <th rowspan="2" scope="col" style="text-align: center">Менеджер</th>
                    <th scope="col">Тип заказа</th>
                    <th rowspan="2" scope="col">Редактировать</th>
                </tr>
                <tr style="text-align: center">
                    <th scope="rowgroup">телефон</th>
                    <th scope="col">Дата закрытия</th>
                    <th scope="col">Статус</th>
                </tr>
            </thead>
            <tbody class="page-products__list">
            <?php foreach ($orders as $order): ?>
                <tr id="firstRowOrder<?= $order['id'] ?>" style="text-align: center">
                    <th rowspan="2" scope="row"><?= $order['id'] ?></th>
                    <td><?= $order['customer'] ?></td>
                    <td><?= $order['created_at'] ?></td>
                    <td rowspan="2" class="orderItem"
                        style="vertical-align: middle;"><?= Users::getUsers($order['user_id'])[0]['name'] ?></td>
                    <td><?= $order['type'] ?></td>
                    <td rowspan="2" style="vertical-align: middle">
                        <button class="btn btn-dark" name="order" value="<?= $order['id'] ?>">
                            🖉
                        </button>
                    </td>
                </tr>
                <tr id="secondRowOrder<?= $order['id'] ?>" style="text-align: center">
                    <td class="orderItem"><?= $order['phone'] ?></td>
                    <td class="orderItem"><?= $order['completed_at'] ?></td>
                    <td class="orderItem"><?= $order['status'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>
<?php
require '../footer.php';

