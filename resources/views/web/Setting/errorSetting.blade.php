
@extends('layout')
@section('item', 'link_10')
@section('content')


    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
            <div class="col-10" style="margin-top: 1.2rem"> <span class="text-white" style="font-size: 20px">  Предупреждение </span></div>
            <div class="col-2 text-center">
                <img src="https://smarttis.kz/Config/logo.png" width="40%"  alt="">
                <div class="text-white" style="font-size: 11px; margin-top: 8px"> Топ партнёр сервиса МойСклад </div>
            </div>
        </div>
        <br>
        @isset($message)

            <div class=" alert alert-danger alert-dismissible fade show in text-center "> {{ $message }}
                <p>
                <a class="text-muted" href="/Setting/{{$accountId}}/{{$isAdmin}}"><i class="fa-solid fa-triangle-exclamation"></i>
                    Перейти на основные настройки  <i class="fa-solid fa-triangle-exclamation"></i></a>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset


    </div>





@endsection



<style>
    .selected {
        margin-right: 0px !important;
        background-color: rgba(17, 17, 17, 0.14) !important;
        border-radius: 3px !important;
    }
    .dropdown-item:active {
        background-color: rgba(123, 123, 123, 0.14) !important;
    }

    .block {
        display: none;
        margin: 10px;
        padding: 10px;
        border: 2px solid orange;
    }

</style>
