@include('admin_dashboard.header')

<div class="content">

    <h2 class="text-2xl font-bold mb-4"> السائقين </h2>

    <div class="my-4">
        {{ 'مجموع السائقين: '.$sum}}
    </div>

    <div class="relative rounded-tl-md  rounded-tr-md overflow-auto">
        <div class="overflow-x-auto relative">
            
            <table class="table">
                <thead class="table_head">
                    <tr>
                        <th scope="col" class="py-3 px-6">#</th>
                        <th scope="col" class="py-3 px-6"> الاسم </th>                        
                        <th scope="col" class="py-3 px-6"> رقم الهاتف </th>
                        <th scope="col" class="py-3 px-6"> الايميل </th>
                        <th scope="col" class="py-3 px-6"> التفعيل </th>
                        <th scope="col" class="py-3 px-6"> تاريخ التسجيل </th>
                        <th scope="col" class="py-3 px-6"> تاريخ اخر ظهور </th>
                    </tr>
                </thead>
                <tbody class="table_body">
                    @foreach($users as $user)
                        <tr data-href="{{ route('driver.details' , $user->id ) }}" class="clickable-row cursor-pointer hover:bg-gray-200">
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400"> {{$user->id}} </td>                                                                                    
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ $user->first_name.' '.$user->last_name }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right" dir="ltr"> {{ $user->phone_numeber }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right" dir="ltr"> {{ $user->email }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ $user->isApproved == 1 ? 'مفعل' : 'غير مفعل'  }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ @$user->created_at }} </td>                            
                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 text-right"> {{ $user->updated_at }} </td>                            
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>        
    </div>

    <div class="text-left mt-10" dir="rtl">
        {{ $users->onEachSide(5)->links('pagination::tailwind') }}
    </div>

</div>


<script>
    $(document).ready(function($) {
        $(".clickable-row").click(function() {
            window.location = $(this).data("href");
            console.log($(this).data("href"));
        });
    });
</script>
@include('admin_dashboard.footer')