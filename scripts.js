
let products = {

    /*функция срабатывает когда
     пользователь вводит кол-во товара
     */
    changeCost: function (e) {
        let id = e.target.parentElement.parentElement.id

        if (Number($('#count'+id).val()) < 0) {
            $('#count'+id).val(0)
        } else if (Number($('#count'+id).val()) > $('#onStock'+id).val()) {
            alert('Вы пытаетесь заказать больше чем есть на складе')
            $('#count'+id).val($('#onStock'+id).val())
        } else if ($('#discount'+id).val() > Number($('#sum'+id).val())) {
            alert('Скидка превышает стоимость заказа!')
            $('#discount'+id).val(0)
        }
        console.log($('#cost').val())
        $('#sum'+id).val((Number($('#count'+id).val()) * Number($('#price'+id).val()))-Number($('#discount'+id).val()))
        let sum=0
        $('.sum').each(function(e,n) {
            sum += Number(n.value)
        })
        console.log(sum)
        $('#cost').val(Number(sum))
        return false;

    },
    deleteRow: function () {
        let countRows = $('#createOrder tbody tr').size()
        console.log(countRows)
        if (countRows > 1) {
            $('#createOrder tbody>tr:last').remove()
            countRows --
        }
    },

    addRow: function (id) {
        let newId = Number($('#addButton').val())+1
        let prevId = newId-1
        // $('#createOrder').append('<tr><th>'+ newId +'</th> <td><input></td> <td><input></td> </tr>')
        $('#createOrder tbody>tr:last').clone(true).insertAfter('#createOrder tbody>tr:last');
        $('#createOrder tbody>tr>th:last').html(newId)
        $('#createOrder tbody>tr:last').attr('id', newId)
        $('#createOrder tbody>tr>td>select:last').attr('id', 'product'+newId)
        $('#createOrder tbody>tr>td>select:last').attr('name', 'product_'+newId)
        $('#createOrder tbody>tr:last>td>input:first').attr('id', 'count'+newId)
        $('#createOrder tbody>tr:last>td>input:first').attr('name', 'count_'+newId)
        $('#createOrder tbody>tr:last>td:first').next().next().children().attr('id', 'onStock'+newId)
        $('#createOrder tbody>tr:last>td:first').next().next().children().attr('name', 'onStock'+newId)
        $('#createOrder tbody>tr:last>td:first').next().next().next().children().attr('id', 'price'+newId)
        $('#createOrder tbody>tr:last>td:first').next().next().next().children().attr('name', 'price'+newId)
        $('#createOrder tbody>tr:last>td:last').prev().children().attr('id', 'discount'+newId)
        $('#createOrder tbody>tr:last>td:last').prev().children().attr('name', 'discount_'+newId)
        $('#createOrder tbody>tr:last>td:last>input').attr('id', 'sum'+newId)
        $('#createOrder tbody>tr:last>td:last>input').attr('name', 'sum_'+newId)
        console.log($('#createOrder tbody>tr:last>td>next>input:first'))
        $('#addButton').val(newId)

    },

    reset: function () {
        $('#createOrder')[0].reset()
        $('#status').prop('disabled', true)
        $('#submit').val('createOrder')
    },

    setStockAndPrice: function (e) {
        let id = e.target.value
        let row = e.target.parentElement.parentElement.id
        $('#count').val(0)

        let formData = new FormData();
        formData.append('id', id)
        formData.append('action', 'getProduct')

        $.ajax({
            url: '/layout/orders/',
            method: 'post',
            data: formData,
            dataType: 'json',
            cache: false,
            processData: false,
            contentType: false,

            success: function (respond) {
                console.log(respond)
                $('#onStock'+row).val(respond.stock[0]['stock'])
                $('#price'+row).val(respond.stock[0]['price'])
            }
        });
    }
}

let orders = {

    setOrderFields: function (orderID) {

        let formData = new FormData();
        formData.append('action', 'getOrders')
        formData.append('id', orderID)
        console.log(orderID)
        $.ajax({
            url: '/',
            method: 'post',
            data: formData,
            dataType: 'json',
            cache: false,
            processData: false,
            contentType: false,
            success: function (respond) {
                let order = respond.orders[0]
                console.log(order)

                $('#orderID').val(order['id'])
                $('#customer').val(order['customer'])
                $('#phone').val(order['phone'])
                $('#product').val(order['product_id'])
                $('#count').val(order['count'])
                $('#price').val(order['price'])
                $('#sum').val(order['price'] * order['count'])
                $('#discount').val(order['discount'])
                $('#onStock').val(order['stock'])
                $('#cost').val(order['cost'])
                $('#status').prop('disabled', false)
                $('#status').val(order['status'])
                $('#user').val(order['user_id'])
                $('#type').val(order['type'])
            }
        });
    },

    createOrChangeOrder: function (orderID = null, action = 'createOrder') {
        // event.preventDefault()
        let formData = new FormData();
        console.log(this.checkForm())
        if (this.checkForm()) {
            formData.append('action', $('#submit').val())
            formData.append('orderID', $('#orderID').val())
            formData.append('customer', $('#customer').val())
            formData.append('phone', $('#phone').val())
            formData.append('product', $('#product').val())
            formData.append('count', $('#count').val())
            formData.append('discount', $('#discount').val())
            formData.append('cost', $('#cost').val())
            formData.append('user', $('#user').val())
            formData.append('type', $('#type').val())
            formData.append('status', $('#status').val())

            $.ajax({
                url: '/',
                method: 'post',
                data: formData,
                dataType: 'json',
                cache: false,
                processData: false,
                contentType: false,
                success: function (respond) {
                    alert('Заказ' + respond.text)
                    console.log(respond)
                    products.reset()
                    $('#exampleModal').modal('hide')
                },
                error: function (jqXHR, status, errorThrown) {
                    console.log('ОШИБКА AJAX запроса: ' + status, jqXHR);
                }
            });
        }

    },

    changeOrder: function (e) {
        $('#exampleModal').modal('show')
        let orderId = e.target.value
        this.setOrderFields(orderId)
        $('#submit').val('changeOrder')
    },

    checkForm: function () {

        return ($('#customer').val().replace(/ /g, "").length > 0) &&
            ($('#phone').val().replace(/ /g, "").length > 0) &&
            ($('#product').val() != null) &&
            ($('#count').val() > 0)
    }
}
