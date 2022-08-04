@extends('layout')

@section('content')
    <div class="headfull">
    <div class="content p-4 mt-2 bg-white text-black rounded">
        <h2 align="center">Логирование
            <i class="fas fa-user-secret"></i>
        </h2>
    </div>
    <div class="content p-4 mt-2 bg-white text-black rounded">
        <div class="row">
            <div class="col-sm-8"> Сообщение
            @foreach($array_log as $item)
                <div class="mt-3">
                    <p>{{ $item }}</p>
                </div>
            @endforeach
            </div>

            <div class="col-sm-4"> Время
                @foreach($array_log_created_at as $item)
                    <div class="mt-3">
                        <p>{{ $item }}</p>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    </div>
@endsection

<style>
    .headfull {
        height: 720px;
    }
</style>
