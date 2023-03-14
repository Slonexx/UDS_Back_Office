@extends('layout')
@section('item', 'link_1')
@section('content')
    <script>
        const url = 'https://office.uds.app/register/trial?ref=3942050';
        function clickref(){
            window.open(url);
        }
    </script>
    {{--<form action="  {{ route( 'CheckSave' , ['accountId' => $accountId] ) }} " method="post">
    @csrf <!-- {{ csrf_field() }} -->
        <button class="btn btn-outline-dark textHover"> check </button>

    </form>--}}
    <div class="content p-4 mt-2 bg-white text-Black rounded">

        @include('div.TopServicePartner')
        <script> NAME_HEADER_TOP_SERVICE("Возможности интеграции") </script>

        <div class="row mt-3">
            <div class="col-6">
                <div class="row">
                    <div> <strong>ПОЛУЧЕНИЕ ЗАКАЗОВ ИЗ UDS</strong></div>
                    <div class="">
                        Заказы поступают в МойСклад автоматически, вы получаете уведомление. Если поменяется Статус заказа в UDS, то вы это сразу увидите в МоемСкладе. Вы можете настроить Статусы самостоятельно.
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="mt"> <strong>ЗАГРУЗКА ТОВАРОВ ИЗ/В UDS</strong></div>
                <div class="">
                    Товары могут быть выгружены из МоегоСклада в UDS или наоборот.
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-6">
                <div class="row">
                    <div class=""> <strong>ПОЛУЧЕНИЕ ДАННЫХ О КЛИЕНТЕ</strong></div>
                    <div class="">
                        Клиенты из UDS проверяются в МоемСкладе по номеру телефона. В случае отсутствия клиента в базе, он создастся автоматически как Физическое лицо.
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class=""> <strong>АВТОМАТИЧЕСКОЕ СОЗДАНИЕ ДОКУМЕНТОВ</strong></div>
                <div class="">
                    Вы можете автоматизировать продажи и настроить автоматическое создание Платежных документов, Отгрузок, Счетов-фактур.
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-6">
                <div class=""> <strong>7 ДНЕЙ БЕСПЛАТНО</strong></div>
                <div class="">
                    Мы на 1000% уверены в своем приложении и поэтому готовы предоставить 7 дней, чтобы Вы могли оценить его возможности и уникальность.
                </div>
            </div>
            <div class="col-6">
                <div class=""> <strong>НОВЫЕ ВОЗМОЖНОСТИ</strong></div>
                <div class="">
                    Мы не стоим на месте, поэтому совсем скоро Вы сможете оценить новые фишки в нашем приложении. Ну и будем признатальны за обратную связь.
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-3"></div>
            <div class="col-6 text-center">
                <button onclick="clickref()" id="click" class="btn uds-gradient text-white">Зарегистрируйтесь в системе лояльности UDS бесплатно!
                </button>
            </div>
            <div class="col-3"></div>
        </div>
    </div>

@endsection

