@extends('layout')
@section('item', 'link_5')
@section('name_head', 'Настройки → Сотрудники')
@section('content')

    <script>
        function CopyPastIdEmployee(id) {
            navigator.clipboard.writeText(document.getElementById(id).innerText).then(() => { }).catch(err => { });
        }
    </script>

    @include('div.TopServicePartner')
    @include('div.notification')
    <div class="box">
        <div class="mt-1 alert alert-warning alert-dismissible fade show in text-center" style="font-size: 16px">
            Важно, сотрудники, которые есть в UDS их необходимо связать, для этого скопируйте внешний идентификатор из
            приложения и вставьте его в UDS (Сотрудники &#8594; кассир &#8594; нужный кассир &#8594; детальная страница
            &#8594; подробная информация о кассире &#8594; Внешний идентификатор (для интеграции) )
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <form action=" " method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div id="Workers" class="content-container border border-info">
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
                                @if ($security[$item->id] == 'admin')
                                    <i class="mx-2 fa-solid fa-user-tie text-success "></i>
                                @endif
                                @if($security[$item->id] == 'individual')
                                    <i class="mx-2 fa-solid fa-user-gear text-primary"></i>
                                @endif
                            </div>
                            <div class="col-5 mx-1 mt-1">
                                {{$item->fullName}}
                            </div>
                            <div class="col-5">
                                <div class=" row mb-2 mx-2 bg-myBlue rounded">
                                    <div class="col-1">
                                        <i onclick="CopyPastIdEmployee('{{$item->id}}')"
                                           class="text-orange btn fa-solid fa-link p-2"></i>
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
            <div class="buttons-container">
            </div>
        </form>
    </div>

@endsection
