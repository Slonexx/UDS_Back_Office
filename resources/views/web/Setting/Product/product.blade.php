@extends('layout')
@section('item', 'link_7')
@section('content')

    <div class="content p-4 mt-2 bg-white text-Black rounded main-container content-container">

        @include('div.TopServicePartner')
        <script> NAME_HEADER_TOP_SERVICE("Настройки → товары") </script>

        @if($message['status'] == true)
            <div class="{{$message['alert']}} mt-1"> {{ $message['message'] }} </div>
        @endif
        <form action="  {{ route( 'setProductIndex' , [ 'accountId' => $accountId,  'isAdmin' => $isAdmin ] ) }} " method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="row mt-1 p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">Товары</div>
                </div>
                <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end "
                     style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off_2" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on_2" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>
            <div id="update_uds_data">
                <div class="mt-2 row">
                    <div class="col-6"><label class="mx-3 mt-1 from-label">Сделать первичную выгрузку товаров и категорий </label> </div>
                    <div class="col-2">
                        <select id="ProductFolder" name="ProductFolder" class="form-select text-black" onchange="FU_sendingGoods(this.value)">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>
               {{--     <div class="col-2 text-center">
                        <label class="mx-3 mt-1 from-label">Статус: </label>
                    </div>
                    <div class="col-2">
                        <label class="mx-3 mt-1 from-label">XXX%</label>
                    </div>--}}
                </div>
                <div id="T1View" style="display: block">
                    {{--выгрузка--}}
                    <div class="mt-2 row">
                        <div class="col-6">
                            <label class="row mx-1">
                                <div class="col-9"> Выгрузка в </div>
                                <button type="button"
                                        class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover1 "
                                        data-toggle="popover" data-placement="right" data-trigger="focus"
                                        data-content="Выберите куда будут выгружаться товары">
                                </button>
                            </label>
                            <div class="col-10">
                                <select onchange="ViewBlockHide(this.value)" name="unloading" class="form-select text-black ">
                                    <option value="0">МойСклад</option>
                                    <option value="1">UDS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    {{--если в UDS то Скрывать--}}
                    <div id="T2View" style="display: block">
                        {{--КАТЕГОРИИ И ТОВАРЫ--}}
                        <div class="mt-2 row">
                            <div class="col-6">
                                <label class="row mx-1">
                                    <div class="col-9"> Выберите основные категории </div>
                                    <button type="button"
                                            class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover2 "
                                            data-toggle="popover" data-placement="right" data-trigger="focus"
                                            data-content="При выборе основных категорий будет выгружаться категории и товары, учитывая созданное дерево в МС (см инструкция)">
                                    </button>
                                </label>
                            </div>
                            <div class="input-group">

                                <div class="input-group-append">
                                    <button class="btn btn-secondary dropdown-toggle text-black bg-white" type="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Выберите
                                        основные категории
                                    </button>
                                    <div id="childrenProduct" class="dropdown-menu">
                                        @foreach ($Body_productFolder as $item)
                                            <a id="{{$item->id}}" onclick="productItem( '{{$item->id}}','{{$item->name}}' )"
                                               class="dropdown-item"> {{$item->name}} </a>
                                        @endforeach
                                    </div>
                                </div>

                                <div id="sendingGoodsArr" class="row form-control bg-white text-black"
                                     style="font-size: 12px"></div>
                                >
                                <i onclick="clearSendingGoodsArr()" type="button" class="btn btn-outline-danger">X</i>


                            </div>

                        </div>
                        {{--Цена продаж--}}
                        <div class="mt-2 row">
                            <div class="col-6">
                                <label class="row mx-1">
                                    <div class="col-9"> Укажите раздел цены продаж </div>
                                </label>
                                <div class="col-10">
                                    <select onchange="SalesPriceHidden(this.value, 'salesPrices')" name="salesPrices" class="form-select text-black ">

                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="row mx-1">
                                    <div class="col-9"> Укажите аукционную цену продаж </div>
                                </label>
                                <div class="col-10">
                                    <select onchange="SalesPriceHidden(this.value, 'promotionalPrice')" name="promotionalPrice" class="form-select text-black ">

                                    </select>
                                </div>
                            </div>
                        </div>
                        {{--Склад--}}
                        <div class="mt-2 row">
                            <div class="col-6">
                                <label class="row mx-1">
                                    <div class="col-9"> Выберите основной склад </div>
                                    <button type="button"
                                            class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover3 "
                                            data-toggle="popover" data-placement="right" data-trigger="focus"
                                            data-content="Выберете основной склад остатков товаров в МС">
                                    </button>
                                </label>
                                <div class="col-10">
                                    <select name="Store" class="form-select text-black ">

                                    </select>
                                </div>
                            </div>
                        </div>
                        {{--Выгрузка товаров со склада--}}
                        <div class="mt-2 row">
                            <div class="col-6">
                                <label class="row mx-1">
                                    <div class="col-9"> Выгрузка товаров с зависимостью склада  </div>
                                    <button type="button"
                                            class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover4 "
                                            data-toggle="popover" data-placement="right" data-trigger="focus"
                                            data-content="При выборе данного функционала товары у которых отсутствую остатки в складе не будут выгружаться">
                                    </button>
                                </label>
                                <div class="col-10">
                                    <select name="StoreRecord" class="form-select text-black ">
                                        <option value="0"> Выгружать все товары </option>
                                        <option value="1"> Не выгружать товары с 0 остатком </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--Скрытие--}}
                    <div class="mt-2 row">
                        <div class="col-6">
                            <label class="row mx-1">
                                <div class="col-9"> Товары с 0 остатком в UDS</div>
                            </label>
                            <div class="col-10">
                                <select id="productHidden" name="productHidden" class="form-select text-black ">
                                    <option value="0"> Скрывать</option>
                                    <option value="1"> Не скрывать</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="href_padding">
            <button class="btn btn-outline-dark gradient_focus"> Создать выгрузку </button>
            <span onclick="activateClearModel()" class="btn btn-outline-dark gradient_focus"> Очистить базу в UDS</span>
        </form>


        <div class="modal fade" id="clearModel" tabindex="-1" role="dialog" aria-labelledby="clearModel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Предупреждение</h5>
                    </div>
                    <div class="modal-body">
                        <p>Вы точно хотите удалить все категории и товары в UDS ?</p>
                    </div>
                    <div class="modal-footer">
                        <button onclick="clearBaseUDS()" type="button" class="btn btn-primary">Да</button>
                        <button onclick="hideClearModel()"  type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('web.Setting.Product.scriptAdd')
    @include('web.Setting.Product.Folder')
    @include('web.Setting.Product.Price')
    @include('web.Setting.Product.Store')
    @include('web.Setting.Product.function')
    @include('web.Setting.Product.set')


@endsection
