@include('admin_dashboard.header')

<div class="content">

    <h2 class="text-2xl font-bold mb-4"> الطلبات اتجاه واحد </h2>

    <div class="my-4">
        {{ 'مجموع الطلبات: '.$sum}}
    </div>

    <div class="relative rounded-tl-md  rounded-tr-md overflow-auto">
        <div class="overflow-x-auto relative">
            
            <table class="table">
                <thead class="table_head">
                    <tr>
                        <th scope="col" class="py-3 px-6">#</th>
                        <th scope="col" class="py-3 px-6"> اسم العميل </th>                        
                        <th scope="col" class="py-3 px-6"> رقم الهاتف </th>
                        <th scope="col" class="py-3 px-6"> سعر الطلب </th>
                        <th scope="col" class="py-3 px-6"> عدد الركاب </th>
                        <th scope="col" class="py-3 px-6"> تاريخ الانشاء </th>
                        <th scope="col" class="py-3 px-6"> حالة الطلب </th>
                    </tr>
                </thead>
                <tbody class="table_body">
                    @foreach($orders as $order)
                        <tr data-href="" class="clickable-row cursor-pointer hover:bg-gray-200">
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400"> {{$order->id}} </td>                                                                                    
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ $order->client->name }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right" dir="ltr"> {{ $order->client->phone }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right" dir="ltr"> {{ $order->price }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ $order->passengers  }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ @$order->created_at }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ $order->status }} </td>                            
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>        
    </div>

    <div class="text-left mt-10" dir="rtl">
        {{ $orders->onEachSide(5)->links('pagination::tailwind') }}
    </div>

</div>


<script>
    $(document).ready(function($) {
        $(".clickable-row").click(function() {
            window.location = $(this).data("href");
        });
    });
</script>
@include('admin_dashboard.footer')