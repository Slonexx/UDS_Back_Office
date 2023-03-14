
@extends('layout')
@section('item', 'link_4')
@section('content')

    <div class="content p-4 mt-2 bg-white text-Black rounded">
        @include('div.TopServicePartner')
        <script> NAME_HEADER_TOP_SERVICE("Настройки → Операции") </script>

        <br>
        @isset($message)

            <div class="{{$message['alert']}}"> {{ $message['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset

        <div class=" alert alert-info alert-dismissible fade show in text-center" style="font-size: 16px">
            По умолчанию операции на списание бонусных баллов по номеру телефона недоступны, необходимо писать на почту ( <u>support@uds.app</u> ) о включении данного функционала
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <form action="/Setting/sendOperations/{{$accountId}}/{{$isAdmin}}" method="post">
        @csrf <!-- {{ csrf_field() }} -->
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

        function EnableOffs_true_or_false(This_value){
            let value = parseInt(This_value);
            if (value === 0){
                window.document.getElementById('EnableOffsSelect').style.display = 'block';
            }
            if (value === 1){
                window.document.getElementById('EnableOffsSelect').style.display = 'none';
            }
        }

    </script>

@endsection
