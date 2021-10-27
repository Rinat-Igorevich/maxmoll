<?php
require $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';

$change = false;

if (isset($_GET['order'])) {
    $orderId = $_GET['order'];
    $change = true;
    $order = Orders::getOrders($orderId)[0];
    $orderItems = Orders::getOrderItems($orderId);
    $sum = 0;
    foreach ($orderItems as $item) {
        $sum += $item['cost'];
    }
}

$status = $order['status'] ?? false;
$type = $order['type'] ?? false;

?>
<div class="container">
    <h4><?= $change ? 'Изменить' : 'Создать' ?> заказ</h4>
    <hr>
    <form action="/layout/orders/" method="post">
        <div class="container">
            <button class="btn btn-primary" type="submit" name="action"
                    value="<?= isset($_GET['order']) ? 'changeOrder' : 'createOrder' ?>">сохранить
            </button>
        </div>
        <div class="container" style="text-align: center">
            <input name="customer" value="<?= $order['customer'] ?? '' ?>" required>ФИО
            <input name="phone" value="<?= $order['phone'] ?? '' ?>" required>Телефон
            <hr>
            Номер заказа
            <input name="orderID" readonly value="<?= $orderId ?? '' ?>" style="width: 50px">
            Статус заказа
            <select class="form-select" name="status"
                    style="width: 150px; display: inline" <?= $status ? '' : 'disabled' ?>>
                <option value="active" <?= $status && $status == 'active' ? 'selected' : '' ?>>active</option>
                <option value="completed"<?= $status && $status == 'completed' ? 'selected' : '' ?>>completed
                </option>
                <option value="canceled" <?= $status && $status == 'canceled' ? 'selected' : '' ?>>canceled</option>
            </select>
            Тип
            <select class="form-select" name="type" style="width: 150px; display: inline">
                <option value="offline" <?= $status && $status == 'offline' ? 'selected' : '' ?>>offline</option>
                <option value="online"<?= $status && $status == 'online' ? 'selected' : '' ?>>online</option>
            </select>
            Менеджер
            <select class="form-select" name="user" style="width: 150px; display: inline">
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $order['user_id'] ?? '' == $user['id'] ? 'selected' : '' ?>><?= $user['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr>
        <table class="table" id="createOrder">
            <thead>
                <th></th>
                <th>Товар</th>
                <th>Количество</th>
                <th>Остаток</th>
                <th>Цена</th>
                <th>Скидка</th>
                <th>Сумма</th>
                <th>
                    <button type="button" id="addButton" class="btn  btn-dark"
                            value="<?= isset($orderItems) ? count($orderItems) : 1 ?>"
                            onclick="products.addRow(2)">
                        + строка
                    </button>
                </th>
                <th>
                    <button type="button" id="addButton" class="btn  btn-dark"
                            value="1"
                            onclick="products.deleteRow()">
                        - строка
                    </button>
                </th>
            </thead>
            <tbody>
            <?php if (isset($orderItems)): ?>
                <?php foreach ($orderItems as $key => $orderItem): ?>
                    <tr id="<?= $key + 1 ?>">
                        <th><?= $key + 1 ?></th>
                        <td><select id="product<?= $key + 1 ?>" name="product_<?= $key + 1 ?>" required
                                    onchange="products.setStockAndPrice(event)">
                                <option>Выберите товар</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>" <?= $product['id'] == $orderItem['id'] ? 'selected' : '' ?>><?= $product['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input id="count<?= $key + 1 ?>" name="count_<?= $key + 1 ?>" type="number" min="0"
                                   value="<?= $orderItem['count'] ?>" required oninput="products.changeCost(event)"
                                   style="width: 100px">
                        </td>
                        <td><input id="onStock<?= $key + 1 ?>" value="<?= $orderItem['stock'] ?>" disabled></td>
                        <td><input id="price<?= $key + 1 ?>" value="<?= $orderItem['price'] ?>" disabled></td>
                        <td><input id="discount<?= $key + 1 ?>" name="discount_<?= $key + 1 ?>"
                                   value="<?= $orderItem['discount'] ?>" onchange="products.setStockAndPrice()">
                        </td>
                        <td>
                            <input class="sum" id="sum<?= $key + 1 ?>" name="sum_<?= $key + 1 ?>"
                                   value="<?= $orderItem['cost'] ?>" readonly>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr id="1">
                    <th>1</th>
                    <td><select class="product" id="product1" name="product_1" required
                                onchange="products.setStockAndPrice(event)">
                            <option>Выберите товар</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= $product['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input class="product" id="count1" name="count_1" type="number" min="0" required
                               oninput="products.changeCost(event)"></td>
                    <td><input id="onStock1" disabled></td>
                    <td><input id="price1" disabled></td>
                    <td><input id="discount1" name="discount_1" value="0" onchange="products.changeCost(event)">
                    </td>
                    <td><input class="sum" id="sum1" name="sum_1" readonly></td>
                </tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <td colspan="5" style="text-align: right">итого</td>
                    <td><input name="cost" id="cost" value="<?= $sum ?? '' ?>" readonly></td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';

