
@extends('layout')
@section('item', 'link_5')
@section('content')

    <script>
        function CopyPastIdEmployee(id) {
            console.log('id = ' + id)
            var Copy = document.getElementById(id).innerText;

            navigator.clipboard.writeText(Copy)
                .then(() => {
                    console.log('Text copied to clipboard');
                })
                .catch(err => {
                    console.error('Error in copying text: ', err);
                });
        }
    </script>

    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
            <div class="col-10" style="margin-top: 1.2rem"> <span class="text-white" style="font-size: 20px">  Настройки &#8594; Сотрудники </span></div>
            <div class="col-2 text-center">
                <img src="https://smarttis.kz/Config/logo.png" width="40%"  alt="">
                <div class="text-white" style="font-size: 11px; margin-top: 8px"> Топ партнёр сервиса МойСклад </div>
            </div>
        </div>

        <br>
        @isset($message)

            <div class="{{$message['alert']}}"> {{ $message['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset

        <div class=" alert alert-warning alert-dismissible fade show in text-center" style="font-size: 16px">
            Важно, сотрудники, которые есть в UDS их необходимо связать, для этого скопируйте внешний идентификатор из приложения и вставьте его в UDS (Сотрудники &#8594; кассир &#8594; нужный кассир &#8594; детальная страница &#8594; подробная информация о кассире &#8594; Внешний идентификатор (для интеграции) )
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <form action=" " method="post">
        @csrf <!-- {{ csrf_field() }} -->

            <div id="Workers" class="border border-info">
                <div class="mx-1 row mt-2 p-1 text-black ">
                    <div class="col-1 mx-3  rounded">
                        №
                    </div>
                    <div class="col-5  rounded">
                        Имя сотрудника
                    </div>
                    <div class="col-5 mx-4  rounded">
                        Внешний идентификатор
                    </div>
                </div>
                <hr class="border border-primary">
                @foreach($employee as $id=>$item)
                    @if($security[$item->id] != 'cashier')
                        <div class="mx-1 row mt-2">
                            <div class="col-1 mx-3 mt-1">
                                {{$id}}
                                @if ($security[$item->id] == 'admin') <i class="mx-2 fa-solid fa-user-tie text-success "></i>@endif
                                @if($security[$item->id] == 'individual') <i class="mx-2 fa-solid fa-user-gear text-primary"></i>@endif
                            </div>
                            <div class="col-5 mx-1 mt-1">
                                {{$item->fullName}}
                            </div>
                            <div class="col-5">
                                <div  class=" row mb-2 mx-2 bg-myBlue rounded">
                                    <div class="col-1">
                                        <i onclick="CopyPastIdEmployee('{{$item->id}}')" class="text-orange btn fa-solid fa-link p-2"></i>
                                    </div>
                                    <div id="{{$item->id}}" class="mt-1 col-11 myWebXyk2 s-min-16">
                                       {{$item->id}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

        </form>
    </div>



@endsection
