@extends('layout')
@section('item', 'link_2')
@section('content')

    <div class="content p-4 mt-2 bg-white text-Black rounded main-container content-container">

        @include('div.TopServicePartner')
        <script> NAME_HEADER_TOP_SERVICE("Настройки → Главное") </script>

        @if($message['status'] == true)
            <div class="{{$message['alert']}} mt-1"> {{ $message['message'] }} </div>
        @endif
        <form action="  {{ route( 'setSettingIndex' , [ 'accountId' => $accountId,  'isAdmin' => $isAdmin ] ) }} "
              method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="row mt-1 p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">UDS данные</div>
                </div>
                <div onclick="toggleClick(1)" class="col-1 d-flex justify-content-end "
                     style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>
            <div id="uds_data" class="mb-3 row">
                <div class="col-6 mt-1">

                    <div class="row">
                        <label class="row mx-1">
                            <div class="col-9">ID компании</div>
                            <button type="button" class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover1 "
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Данный ID находится в UDS &#8594; Настройки &#8594; Интеграция &#8594; Данные для интеграции ">
                            </button>
                        </label>
                        <div class="col-10">
                            <input type="text" name="companyId" id="companyId" placeholder="ID компании"
                                   class="form-control form-control-orange" required maxlength="255" value=" ">
                        </div>
                    </div>

                    <div class="row mt-2">

                        <label class="row mx-1">
                            <div class="col-9">API Key</div>
                            <button type="button" class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover2 "
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Данный ID находится в UDS &#8594; Настройки &#8594; Интеграция &#8594; Данные для интеграции ">
                            </button>
                        </label>

                        <div class="col-sm-10">
                            <input type="text" name="TokenUDS" id="TokenUDS" placeholder="API Key"
                                   class="form-control form-control-orange" required maxlength="255" value=" ">
                        </div>
                    </div>
                </div>

            </div>
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
                    <div class="col-6"><label class="mx-3 mt-1 from-label">Отправлять товары и категории в UDS </label>
                    </div>
                    <div class="col-2">
                        <select id="ProductFolder" name="ProductFolder" class="form-select text-black"
                                onchange="FU_sendingGoods(this.value)">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>
                </div>
                <div id="T1View" style="display: block">
                    <div class="mt-2 row">

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
                    <div class="mt-2 row">
                        <div class="col-6">
                            <label class="row mx-1">
                                <div class="col-9"> Изменение товаров:</div>
                                <button type="button"
                                        class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover4 "
                                        data-toggle="popover" data-placement="right" data-trigger="focus"
                                        data-content="Выберите откуда будет изменяться товары">
                                </button>
                            </label>
                            <div class="col-10">
                                <select name="UpdateProduct" class="form-select text-black ">
                                    <option value="0">МойСклад</option>
                                    <option value="1">UDS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 row">
                        <div class="col-sm-6">
                            <label class="row mx-1">
                                <div class="col-9"> Выберите склад, для остатков товара:</div>
                                <button type="button"
                                        class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover5 "
                                        data-toggle="popover" data-placement="right" data-trigger="focus"
                                        data-content="По данному складу будут отправляться остатки в UDS и на данный склад будет создаваться заказ">
                                </button>
                            </label>
                            <div class="col-10">
                                <select name="Store" class="form-select text-black ">
                                    @foreach($Body_store as $Body_store_item)
                                        @if ( $Store == $Body_store_item->name )
                                            <option selected
                                                    value="{{ $Body_store_item->name }}"> {{ ($Body_store_item->name) }} </option>
                                        @else
                                            <option
                                                value="{{ $Body_store_item->name }}"> {{ ($Body_store_item->name) }} </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 row">
                        <div class="col-sm-6">
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
            <button class="btn btn-outline-dark gradient_focus"> Сохранить</button>
        </form>
    </div>


    <script>
        let ProductFolder = "{{$ProductFolder}}"
        let Folders = @json($Folders);

        window.document.getElementById('ProductFolder').value = ProductFolder
        FU_sendingGoods(ProductFolder);


        if (Folders.length > 0) {
            for (let i = 0; i < Folders.length; i++) {
                window.document.getElementById(Folders[i].id).click()
            }
        }


        function clearSendingGoodsArr() {
            window.document.getElementById('sendingGoodsArr').innerText = ""
            let children = $("#childrenProduct").children()
            for (let i = 0; i < children.length; i++) {
                if (children[i].style.display === 'none') {
                    children[i].style.display = 'block'
                }
            }
        }

        function productItem(id, name) {
            if (name == 'Корневая папка') {
                clearSendingGoodsArr()
                $('#sendingGoodsArr').append('<input type="hidden" id="1' + '" name="Folder ' + id + '" value="Folder' + id + '" class="customSpan" >' + "1) " + name + " </input>")
                let children = $("#childrenProduct").children()
                for (let i = 0; i < children.length; i++) {
                    children[i].style.display = 'none'
                }
            } else {
                if ($("#sendingGoodsArr").children().length > 0) {
                    let i = $("#sendingGoodsArr").children().length + 1
                    $('#sendingGoodsArr').append('<input type="hidden" id="' + i + '" name="Folder ' + id + '" value="Folder' + id + '"  class="customSpan" >' + i + ") " + name + " </input>")
                } else {
                    $('#sendingGoodsArr').append('<input type="hidden" id="1' + '" name="Folder ' + id + '" value="Folder' + id + '"  class="customSpan" >' + "1) " + name + " </input>")
                }
                window.document.getElementById(id).style.display = "none"
            }

        }


        function FU_sendingGoods(value) {
            let view = window.document.getElementById('T1View')
            if (value === 1 || value === '1') {
                view.style.display = 'block'
                clearSendingGoodsArr()
            } else {
                view.style.display = 'none'
            }
        }

        let URL = "https://smartuds.kz/CountProduct/"
        //let URL = "https://uds/CountProduct"
        let accountId = "{{ $accountId }}"
        let companyId = "{{ $companyId }}"
        let TokenUDS = "{{ $TokenUDS }}"
        let productHidden = "{{ $hiddenProduct }}";

        //Visible("none")
        window.document.getElementById('companyId').value = companyId
        window.document.getElementById('TokenUDS').value = TokenUDS
        window.document.getElementById('productHidden').value = productHidden

        function toggleClick(id) {

            if (id === 1) {
                let toggle_off = window.document.getElementById('toggle_off')
                let toggle_on = window.document.getElementById('toggle_on')

                let T1 = window.document.getElementById('uds_data')

                if (toggle_off.style.display == "none") {
                    toggle_on.style.display = "none"
                    toggle_off.style.display = "block"

                    T1.style.display = 'block'
                } else {
                    toggle_on.style.display = "block"
                    toggle_off.style.display = "none"

                    T1.style.display = 'none'
                }
            }

            if (id === 2) {
                let toggle_off_2 = window.document.getElementById('toggle_off_2')
                let toggle_on_2 = window.document.getElementById('toggle_on_2')

                let T2 = window.document.getElementById('update_uds_data')
                if (toggle_off_2.style.display == 'none') {
                    toggle_on_2.style.display = "none"
                    toggle_off_2.style.display = "block"

                    T2.style.display = 'block'
                } else {
                    toggle_on_2.style.display = "block"
                    toggle_off_2.style.display = "none"

                    T2.style.display = 'none'
                }
            }


        }


        function ajax_settings(url, method, data) {
            return {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }


        $('.myPopover1').popover();
        $('.myPopover2').popover();
        $('.myPopover3').popover();
        $('.myPopover4').popover();
        $('.myPopover5').popover();
    </script>


@endsection
