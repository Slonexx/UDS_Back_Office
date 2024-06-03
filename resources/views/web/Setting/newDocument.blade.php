@extends('layout')
@section('item', 'link_3')
@section('name_head', 'Настройки → Заказы')
@section('content')

    @include('div.TopServicePartner')
    @include('div.notification')
    <div class="">
        <form action="/setSetting/Document/{{ $accountId }}/{{ $isAdmin }}" method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div class="box">
                <div class="row p-1 gradient_invert rounded text-black">
                    <div class="col-11">
                        <div style="font-size: 20px"> Настройки заказа</div>
                    </div>
                    <div onclick="toggleClick(1)" class="col-1 d-flex justify-content-end " style="font-size: 30px; cursor: pointer">
                        <i id="toggle_off" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                        <i id="toggle_on" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                    </div>
                </div>
                <div id="T1">
                    <div class="mt-1 columns">
                        <div class="column is-5">
                            <label class="mt-1 from-label">Создавать заказы с UDS ? </label>
                        </div>
                        <div class="column is-2">
                            <select id="creatDocument" name="creatDocument" class="form-select text-black"
                                    onchange="creatDocumentView(this.value)">
                                <option value="0">Нет</option>
                                <option value="1">Да</option>
                            </select>
                        </div>
                    </div>
                    <div id="DocumentView" class="mt-2">

                        <div class="columns">
                            <P class="column"> Выберите на какую организацию создавать заказы: </P>
                            <div class="column is-7">

                                <select name="Organization" id="select_Organization" class="form-select text-black"
                                        onchange="isPaymentAccount(this.value)">{{--onclick="PaymentAccountFun()" >--}}
                                </select>

                            </div>
                        </div>
                        <div class="columns">
                            <P class="column"> Выберите какой тип документов создавать: </P>
                            <div class="column is-7">
                                <select id="Document" name="Document" class="form-select text-black">
                                    <option value="0">Не создавать</option>
                                    <option value="1">Отгрузка</option>
                                    <option value="2">Отгрузка + счет-фактура выданный</option>
                                </select>
                            </div>
                        </div>
                        <div class="columns">
                            <P class="column"> Выберите склад: </P>
                            <div class="column is-7">
                                <select id="Store" name="Store" class="form-select text-black">
                                </select>
                            </div>
                        </div>
                        <div class="columns">
                            <P class="column"> Выберите какой тип платежного документа создавать: </P>
                            <div class="column is-7">
                                <select id="PaymentDocument" name="PaymentDocument" class="form-select text-black">
                                    <option value="0">Не создавать</option>
                                    <option value="1">Приходной ордер</option>
                                    <option value="2">Входящий платёж</option>
                                </select>
                            </div>
                        </div>
                        <div class="columns">
                            <P class="column "> Выберите расчетный счет: </P>
                            <div class="column is-7">
                                <select id="PaymentAccount" name="PaymentAccount" class="form-select text-black"> </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="row mt-1 p-1 gradient_invert rounded text-black">
                    <div class="col-11">
                        <div style="font-size: 20px">Вебхуки
                            <button type="button" class="btn gradient_focus fa-solid fa-circle-info myPopover1"
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Данный вебхуки необходимо вставить в UDS &#8594; Настройки &#8594; Интеграция &#8594; Вебхуки ">
                            </button>
                        </div>
                    </div>
                    <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end "
                         style="font-size: 30px; cursor: pointer">
                        <i id="toggle_off_2" class="fa-solid fa-toggle-off text_gradient"
                           style="display: block"></i>
                        <i id="toggle_on_2" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                    </div>
                </div>
                <div id="T2" class="mt-1" style="display:block;">
                    <div class="row">
                        <div class="col-5"><label class="mt-2"> Получение новых клиентах </label></div>
                        <div class="col-7">
                            <div class=" row mx-2 bg-myBlue rounded">
                                <div class="col-1">
                                    <i onclick="myWebXyk1()" class="gradient_focus btn fa-solid fa-link p-2 "></i>
                                </div>
                                <div id="myWebXyk1" class="mt-1 col-11 myWebXyk1 s-min-16">
                                    https://smartuds.kz/api/webhook/{{$accountId}}/client
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-5 mt-2">
                            <label class="mt-2"> Получение новых заказов </label>
                        </div>
                        <div class="col-7 mt-2">
                            <div class=" row mt-1 mx-2 bg-myBlue rounded">
                                <div class="col-1">
                                    <i onclick="myWebXyk2()" class="gradient_focus btn fa-solid fa-link p-2"></i>
                                </div>
                                <div id="myWebXyk2" class="mt-1 col-11 myWebXyk2 s-min-16">
                                    https://smartuds.kz/api/webhook/{{$accountId}}/order
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="row p-1 gradient_invert rounded text-black">
                    <div class="col-11">
                        <div style="font-size: 20px">Дополнительные настройки при заказе с UDS</div>
                    </div>
                    <div onclick="toggleClick(3)" class="col-1 d-flex justify-content-end "
                         style="font-size: 30px; cursor: pointer">
                        <i id="toggle_off_3" class="fa-solid fa-toggle-off text_gradient"
                           style="display: block"></i>
                        <i id="toggle_on_3" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                    </div>
                </div>
                <div id="T3" class="mt-1" style="display: block">
                    <div class="mt-1 row">
                        <P class="col-5 col-form-label"> Выберите канал продаж: </P>
                        <div class="col-7">
                            <select id="Saleschannel" name="Saleschannel" class="form-select text-black ">
                                <option value="0"> Не выбирать</option>
                                @foreach($arr_Saleschannel as $item)
                                    <option value="{{ $item->name }}"> {{ ($item->name) }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-1 row">
                        <P class="col-5 col-form-label"> Выберите проект: </P>
                        <div class="col-7">
                            <select id="Project" name="Project" class="form-select text-black ">
                                <option value="0"> Не выбирать</option>
                                @foreach($arr_Project as $item)
                                    <option value="{{ $item->name}}"> {{ ($item->name) }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="mt-3">
                        <div style="font-size: 20px">Статусы заказов</div>
                        <div class="row">
                            <label class="col-5 col-form-label"> 1) Новый заказ </label>
                            <div class="col-7">
                                <select id="NEW" name="NEW" class="form-select text-black">
                                    <option value="0"> Статус МойСклад</option>
                                    @foreach($arr_Customerorder as $item)
                                        <option value="{{ $item->name }}"> {{ ($item->name) }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-5 col-form-label"> 2) Завершенный </label>
                            <div class="col-7 ">
                                <select id="COMPLETED" name="COMPLETED" class="form-select text-black">
                                    <option value="0"> Статус МойСклад</option>
                                    @foreach($arr_Customerorder as $item)
                                        <option value="{{ $item->name }}"> {{ ($item->name) }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-5 col-form-label"> 3) Отменный </label>
                            <div class="col-7 ">
                                <select id="DELETED" name="DELETED" class="form-select text-black">
                                    <option value="0"> Статус МойСклад</option>
                                    @foreach($arr_Customerorder as $item)
                                        <option value="{{ $item->name }}"> {{ ($item->name) }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <button class="mt-2 btn btn-outline-dark gradient_focus"> Сохранить</button>
        </form>
    </div>


    <script>

        let message = "{{ $message }}"

        let creatDocument = "{{ $creatDocument }}"
        let Organization = @json($Organization);
        let Store = '{{ $Store }}'
        let Document = '{{ $Document }}'
        let PaymentDocument = '{{ $PaymentDocument }}'
        let PaymentAccount = '{{ $PaymentAccount }}'

        let Saleschannel = '{{ $Saleschannel }}'
        let Project = '{{ $Project }}'
        let NEW = '{{ $NEW }}'
        let COMPLETED = '{{ $COMPLETED }}'
        let DELETED = '{{ $DELETED }}'

        let arr_Organization = @json($arr_Organization);
        let arr_Store = @json($arr_Store);
        let arr_PaymentAccount = @json($arr_PaymentAccount);

        window.document.getElementById('creatDocument').value = creatDocument
        window.document.getElementById('Document').value = Document
        window.document.getElementById('PaymentDocument').value = PaymentDocument
        //проверка


        $(document).ready(function () {
            loading()

            if (Organization !== "0") {
                window.document.getElementById('select_Organization').value = Organization.id
                isPaymentAccount( window.document.getElementById('select_Organization').value)
            }

            creatDocumentView(creatDocument)

            window.document.getElementById('Saleschannel').value = Saleschannel
            window.document.getElementById('Project').value = Project
            window.document.getElementById('NEW').value = NEW
            window.document.getElementById('COMPLETED').value = COMPLETED
            window.document.getElementById('DELETED').value = DELETED

            if (Store !== '') window.document.getElementById('Store').value = Store
        });


        function loading(){
            let select_Organization = window.document.getElementById('select_Organization')
            let Store = window.document.getElementById('Store')


            clearOption(select_Organization)
            clearOption(Store)

            createOptions(arr_Organization, select_Organization)
            createOptions(arr_Store, Store, false)

            isPaymentAccount(select_Organization.value)
        }





        function isPaymentAccount(value) {
            let select = document.getElementById('PaymentAccount');

            clearOption(select)
            if (arr_PaymentAccount[value].length > 0) createOptions(arr_PaymentAccount[value], select)
            else createOptions([{'name': 'Нет расчетного счёта', 'id': '0'}], select)

        }


        $('.myPopover1').popover();
    </script>

    <script>
        function createOptions(data, targetElement, isBool = true) {
            data.forEach((item) => {
                let option = document.createElement("option");

                option.text = item.name || item.accountNumber
                if (isBool) option.value = item.id
                else option.value = item.name

                targetElement.appendChild(option);
            });
        }
        function clearOption(selected) {
            while (selected.firstChild) selected.removeChild(selected.firstChild)
        }
    </script>

@endsection

<script>

    function creatDocumentView(params) {
        let view = window.document.getElementById('DocumentView')
        if (params === 1 || params === '1') {
            view.style.display = 'block'
            if (window.document.getElementById('T2').style.display == 'none') {
                toggleClick(2)
                toggleClick(3)
            }
        } else {
            view.style.display = 'none'
            if (window.document.getElementById('T2').style.display == 'block') {
                toggleClick(2)
                toggleClick(3)
            }
        }
    }


    function showPaymentAccount(divId, element) {
        document.getElementById(divId).style.display = element.value == 2 ? 'block' : 'none';
    }





    function myWebXyk1() {
        let WebXyk = document.querySelector('.myWebXyk1');

        let range = document.createRange();
        range.selectNode(WebXyk);
        window.getSelection().addRange(range);

        try {
            let successful = document.execCommand('copy');
            let msg = successful ? 'successful' : 'unsuccessful';
            console.log('Copy WebXyk command was ' + msg);
        } catch (err) {
            console.log('Oops, unable to copy');
        }
        window.getSelection().removeAllRanges();
    }
    function myWebXyk2() {
        let WebXyk = document.querySelector('.myWebXyk2');
        let range = document.createRange();
        range.selectNode(WebXyk);
        window.getSelection().addRange(range);

        try {
            let successful = document.execCommand('copy');
            let msg = successful ? 'successful' : 'unsuccessful';
            console.log('Copy WebXyk command was ' + msg);
        } catch (err) {
            console.log('Oops, unable to copy');
        }
        window.getSelection().removeAllRanges();
    }


    function toggleClick(id) {

        if (id === 1) {
            let toggle_off = window.document.getElementById('toggle_off')
            let toggle_on = window.document.getElementById('toggle_on')

            let T1 = window.document.getElementById('T1')

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

            let T2 = window.document.getElementById('T2')
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

        if (id === 3) {
            let toggle_off = window.document.getElementById('toggle_off_3')
            let toggle_on = window.document.getElementById('toggle_on_3')

            let T = window.document.getElementById('T3')

            if (toggle_off.style.display == "none") {
                toggle_on.style.display = "none"
                toggle_off.style.display = "block"

                T.style.display = 'block'
            } else {
                toggle_on.style.display = "block"
                toggle_off.style.display = "none"

                T.style.display = 'none'
            }
        }

    }
</script>
