@extends('layout')
@section('item', 'link_4')
@section('name_head', 'Настройки → Операции')
@section('content')

    @include('div.TopServicePartner')
    @include('div.notification')
    <div class="box">
        <div class=" alert alert-info alert-dismissible fade show in text-center" style="font-size: 16px">
            По умолчанию операции на списание бонусных баллов по номеру телефона недоступны, необходимо писать на почту ( <u>support@uds.app</u> ) о включении данного функционала
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <form action="/Setting/sendOperations/{{$accountId}}/{{$isAdmin}}" method="post">
        @csrf <!-- {{ csrf_field() }} -->

            <div class="mt-2 row p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px"> Ручное проведение операции. </div>
                </div>
                <div onclick="toggleClick(1)" class="col-1 d-flex justify-content-end "
                     style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>
            <div id="T1">
                <div class="mb-1 row">
                    <div class="col-6">
                        <label class="mt-1 from-label">Начисление </label>
                    </div>
                    <div class="col-5">
                        <select id="operationsAccrue" name="operationsAccrue" class="form-select text-black" >
                            <option value="0">Номеру телефона</option>
                            <option value="1">QR-коду мобильного приложения</option>
                        </select>
                    </div>
                </div>
                <div id="EnableOffsSelect" class="mb-3">
                    <div class="row">
                        <div class="col-6">
                            <label class="mt-1 from-label">Списание  </label>
                        </div>
                        <div class="col-5">
                            <select id="operationsCancellation" name="operationsCancellation" class="form-select text-black" >
                                <option value="0">Номеру телефона</option>
                                <option selected value="1">QR-коду мобильного приложения</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2 row p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px"> Автоматическое создание документов после ручного проведения операции. </div>
                </div>
                <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end "
                     style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off_2" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on_2" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>
            <div id="T2">
                <div id="Enable_true_or_false" style="display:block;">
                    <div class="row">
                        <P class="col-6 col-form-label"> Выберите какой тип документов создавать: </P>
                        <div class="col-5">
                            <select id="operationsDocument" name="operationsDocument" class="form-select text-black" >
                                <option selected value="0">Не создавать</option>
                                <option value="1">Отгрузка</option>
                                <option value="2">Отгрузка + счет-фактура выданный</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <P class="col-6 col-form-label"> Выберите какой тип платежного документа создавать: </P>
                        <div class="col-5">
                            <select id="operationsPaymentDocument" name="PaymentDocument" class="form-select text-black"  onclick="">
                                <option selected value="0">Не создавать</option>
                                <option value="1">Приходной ордер</option>
                                <option value="2">Входящий платёж </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2 row p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px"> Настройка индивидуального начисления/списание на товары </div>
                </div>
                <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end "
                     style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off_3" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on_3" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>
            <div id="T3">
                <div style="display:block;">
                    <div class="row">
                        <P class="col-6 col-form-label"> Выберите режим
                            <button type="button"
                                    class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover4 "
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Ограниченный - Списание и начисление НЕ может превышать настроек программу лояльности.
                                    Свободный - Помимо стандартной операции в UDS, будет проводиться до начисление/списание баллов.
                                    Подробнее - смотреть в инструкции
                                    ">
                            </button>

                        </P>
                        {{--Ограниченный - Списание и начисление Не может привышать настроек программу лояльности --}}
                        {{--Сводобный - Помимо стандратной операции в UDS, будет проводиться донаичслени или списание баллов --}}
                        <div class="col-5">
                            <select id="customOperation" name="customOperation" class="form-select text-black" >
                                <option selected value="3"> Стандартный </option>
                                <option value="0"> Ограниченный </option>
                                <option value="1"> Свободный </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


            <hr class="href_padding">
            <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить
            </button>
        </form>
    </div>

    <script>

        let operationsAccrue = document.getElementById('operationsAccrue').value = {{ $operationsAccrue }};
        let operationsCancellation = document.getElementById('operationsCancellation').value = {{ $operationsCancellation }};
        //EnableOffs_true_or_false(operations);
        let operationsDocument_value  = document.getElementById('operationsDocument').value = {{ $operationsDocument }};
        let operationsPaymentDocument_value  = document.getElementById('operationsPaymentDocument').value = {{ $operationsPaymentDocument }};
        document.getElementById('customOperation').value = {{ $customOperation }};

        function EnableOffs_true_or_false(This_value){
            let value = parseInt(This_value);
            if (value === 0){
                window.document.getElementById('EnableOffsSelect').style.display = 'block';
            }
            if (value === 1){
                window.document.getElementById('EnableOffsSelect').style.display = 'none';
            }
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
                let toggle_off = window.document.getElementById('toggle_off_2')
                let toggle_on = window.document.getElementById('toggle_on_2')

                let T1 = window.document.getElementById('T2')

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

        }

        $('.myPopover4').popover();

    </script>

@endsection
