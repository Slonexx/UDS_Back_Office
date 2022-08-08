
@extends('widgets.index')

@section('counterparty')
<div class="content p-1 mt-2 bg-white text-Black rounded">
    @php

    dd(app('request'));

    @endphp
    <br>
    <br>
    <p><b title="Используя objectId, переданный в сообщении Open, можем получить через JSON API открытую пользователем сущность/документ">
            Открыт объект
            <span class="hint">(?)</span>:</b> <span id="object"></span></p>
</div>
@endsection
