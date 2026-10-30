{{-- AJAX-фрагмент списка заказов: плашки-сводка + таблица с
     пагинацией. Его отдаёт OrderController при fragment=1 (и
     indexAjax), а admin-orders-live.js подменяет им содержимое
     #orders-live без перезагрузки страницы. --}}
@include('admin.order.chips')
@include('admin.order.table')
