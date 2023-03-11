@include('admin_dashboard.header')

<div class="content">

    <h2 class="text-2xl font-bold mb-4"> تفاصيل السائق </h2>

    @if(Session::has('errors'))
    <div class="my-3 w-full p-4 bg-orange-500 text-white rounded-md">
        {!! session('errors')->first('error') !!}
    </div>
    @endif

    @if(Session::has('success'))
    <div class="my-3 w-full p-4 bg-green-700 text-white rounded-md">
        {!! session('success') !!}
    </div>
    @endif

    <div class="p-16">
        <div class="p-8 bg-white shadow mt-24">
            <div class="grid grid-cols-1 md:grid-cols-3">
                <div class="grid grid-cols-2 text-center order-last md:order-first mt-20 md:mt-0">
                    <div>
                        <p class="font-bold text-gray-700 text-sm">{{ $driver->created_at }}</p>
                        <p class="text-gray-400">تاريخ التسجيل</p>
                    </div>                    
                </div>
                <div class="relative">
                    <div class="w-48 h-48 bg-indigo-100 mx-auto rounded-full shadow-2xl absolute inset-x-0 top-0 -mt-24 flex items-center justify-center text-indigo-500">
                        <img src="{{ route('view_img' , $driver->avatar) }}" alt="" srcset="">
                    </div>
                </div>

                <div class=" flex justify-between mt-32 md:mt-0 md:justify-center">
                    <a href="tel:{{ $driver->phone_numeber }}" class="text-white mx-1 py-2 px-4 uppercase text-sm rounded bg-blue-400 hover:bg-blue-500 shadow hover:shadow-lg font-medium transition transform hover:-translate-y-0.5">
                        اتصل
                    </a>
                    <a href="{{ route('drivers.update.status' , [$driver->id , 1]) }}" class="text-white mx-1 py-2 px-2 uppercase text-sm rounded bg-green-400 hover:bg-green-500 shadow hover:shadow-lg font-medium transition transform hover:-translate-y-0.5">
                        قبول السائق
                    </a>
                    <a href="{{ route('drivers.update.status' , [$driver->id , 0]) }}" class="text-white mx-1 py-2 px-2 uppercase text-sm rounded bg-red-400 hover:bg-red-500 shadow hover:shadow-lg font-medium transition transform hover:-translate-y-0.5">
                        رفض السائق
                    </a>
                </div>
            </div>

            <div class="mt-16 text-center">
                <p class="text-2xl font-bold"> {!! $driver->isApproved ? "<span class='text-green-500'>مقبول</span>" : "<span class='text-red-500'>مرفوض</span>" !!} </p>
            </div>

            <div class="mt-16 text-center border-b pb-12">
                <h1 class="text-4xl font-medium text-gray-700">{{ $driver->first_name.' '.$driver->last_name }}</h1>
                <p class="font-light text-gray-600 mt-3">هاتف: {{ $driver->phone_numeber }}</p>
                <p class="font-light text-gray-600 mt-3">إيميل: {{ $driver->email }}</p>
                <p class="font-light text-gray-600 mt-3">تاريخ ميلاد: {{ $driver->birth_date }}</p>                
            </div>            

        </div>

        <h2 class="my-4 text-2xl font-bold text-slate-500">معلومات المركبة</h2>

        <div class="grid grid-cols-2 gap-2 my-8">
            
            <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 ">نوع المركبة</h5>
                <p class="font-normal text-gray-700 dark:text-gray-400">{{ $driver->vehicle_type }}</p>
            </div>

            <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 ">موديل المركبة</h5>
                <p class="font-normal text-gray-700 dark:text-gray-400">{{ $driver->vehicle_model }}</p>
            </div>

            <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 ">سنة صنع المركبة</h5>
                <p class="font-normal text-gray-700 dark:text-gray-400">{{ $driver->vehicle_made_year }}</p>
            </div>

            <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 ">عدد ركاب المركبة</h5>
                <p class="font-normal text-gray-700 dark:text-gray-400">{{ $driver->vehicle_passengers }}</p>
            </div>

            <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 ">لون المركبة</h5>
                <p class="font-normal text-gray-700 dark:text-gray-400">{{ $driver->vehicle_color }}</p>
            </div>

        </div>

        <h2 class="my-4 text-2xl font-bold text-slate-500">بيانات شخصية</h2>

        <div class="grid grid-cols-2 gap-4 my-8">
            
            <a href="{{ route('view_img' , $driver->id_photo) }}" target="_blank" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-r-lg" src="{{ route('view_img' , $driver->id_photo) }}" alt="">
                <div class="flex flex-col justify-between p-4 leading-normal">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">صورة الهوية</h5>
                    <!-- <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.</p> -->
                </div>
            </a>

            <a href="{{ route('view_img' , $driver->driver_license_front) }}" target="_blank" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-r-lg" src="{{ route('view_img' , $driver->id_photo) }}" alt="">
                <div class="flex flex-col justify-between p-4 leading-normal">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">صورة الرخصة من الامام</h5>
                    <!-- <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.</p> -->
                </div>
            </a>

            <a href="{{ route('view_img' , $driver->driver_license_back) }}" target="_blank" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-r-lg" src="{{ route('view_img' , $driver->driver_license_back) }}" alt="">
                <div class="flex flex-col justify-between p-4 leading-normal">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">صورة الرخصة من خلف</h5>
                    <!-- <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.</p> -->
                </div>
            </a>

            <a href="{{ route('view_img' , $driver->vehicle_license_front) }}" target="_blank" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-r-lg" src="{{ route('view_img' , $driver->vehicle_license_front) }}" alt="">
                <div class="flex flex-col justify-between p-4 leading-normal">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">صورة الاستمارة من امام</h5>
                    <!-- <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.</p> -->
                </div>
            </a>

            <a href="{{ route('view_img' , $driver->vehicle_license_back) }}" target="_blank" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-r-lg" src="{{ route('view_img' , $driver->vehicle_license_back) }}" alt="">
                <div class="flex flex-col justify-between p-4 leading-normal">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">صورة الاستمارة من خلف</h5>
                    <!-- <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.</p> -->
                </div>
            </a>

        </div>
    </div>



</div>

@include('admin_dashboard.footer')