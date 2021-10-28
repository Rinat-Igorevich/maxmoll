
let products = {
    /*
     функция срабатывает когда пользователь вводит кол-во товара
     проверяет чтоб в заказ не ушло больше чем есть на остатке
     проверяет чтоб скидка не превышала стоимость заказа
     обновляет сумму по строке и сумму итого по заказу
     */

    changeCost: function (e) {
        let id = e.target.parentElement.parentElement.id

        if (Number($('#count'+id).val()) <= 0) {
            alert('нельзя заказать 0 товаров')
            $('#count'+id).val(1)
        } else if (Number($('#count'+id).val()) > $('#onStock'+id).val()) {
            alert('Вы пытаетесь заказать больше чем есть на складе')
            $('#count'+id).val($('#onStock'+id).val())
        } else if ($('#discount'+id).val() > Number($('#sum'+id).val())) {
            alert('Скидка превышает стоимость заказа!')
            $('#discount'+id).val(0)
        }

        $('#sum'+id).val((Number($('#count'+id).val()) * Number($('#price'+id).val()))-Number($('#discount'+id).val()))
        let sum=0
        $('.sum').each(function(e,n) {
            sum += Number(n.value)
        })

        $('#cost').val(Number(sum))
        return false;

    },
    /*
       ф-я удаляет строку в заказе,
       ограничение - должна остаться хотя бы одна строка
     */
    deleteRow: function () {
        let countRows = $('#createOrder tbody tr').size()
        console.log(countRows)
        if (countRows > 1) {
            $('#createOrder tbody>tr:last').remove()
            countRows --
        }
    },
    /*
        ф-я добавляет строку в заказе,
        проставляет всем input новые id и name
     */
    addRow: function (id) {
        let newId = Number($('#addButton').val())+1

        $('#createOrder tbody>tr:last').clone(true).insertAfter('#createOrder tbody>tr:last');
        $('#createOrder tbody>tr>th:last').html(newId)
        $('#createOrder tbody>tr:last').attr('id', newId)
        $('#createOrder tbody>tr:last>td>input').each(function(e,n) {
            this.value = ''
        })
        $('#createOrder tbody>tr>td>select:last').attr('id', 'product'+newId)
        $('#createOrder tbody>tr>td>select:last').val('')
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

        $('#addButton').val(newId)
    },
    /*
        ф-я срабатывает при изменении товара в строке заказа
        с помощью ajax запроса получает из БД остаток и стоимость выбранного товара
        подставляет в соответствующие поля
     */
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
                $('#onStock'+row).val(respond.stock[0]['stock'])
                $('#price'+row).val(respond.stock[0]['price'])
            }
        });
    }
}