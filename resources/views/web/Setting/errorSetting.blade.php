
@extends('layout')

@section('content')


    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <h4> <i class="fa-solid fa-triangle-exclamation text-danger"></i> Предупреждение </h4>

        <br>
        @isset($message)

            <div class=" alert alert-danger alert-dismissible fade show in text-center "> {{ $message }}
                <p>
                <a class="text-muted" href="/Setting/{{$accountId}}"><i class="fa-solid fa-triangle-exclamation"></i>
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
