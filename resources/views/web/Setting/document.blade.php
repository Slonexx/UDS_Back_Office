
@extends('layout')

@section('content')


    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <h4> <i class="fa-solid fa-gears text-orange"></i> Настройка документов </h4>

        <br>

        @isset($message)

            <div class="{{$message['alert']}}"> {{ $message['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset

        <div class=" alert alert-warning alert-dismissible fade show in text-center "> Склад на который будет создаваться заказ, это тот же склад который выбирается по остаткам в настройках основная
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>


        <form action=" {{ route('setSettingDocument' , ['accountId' => $accountId] ) }} " method="post">
        @csrf <!-- {{ csrf_field() }} -->


            <div class="mb-3 row">
                <div class="col-sm-5">
                    <label class="mt-1 from-label">Создавать заказы с UDS ? </label>
                </div>
                <div class="col-sm-2">


                        @php

                        if ($creatDocument == "1") {
                            $creatDocument_data = "anti_hidden_div";
                            $creatDocument_data_option_0 = "";
                            $creatDocument_data_option_1 = "selected";
                        }
                        else {
                            $creatDocument_data = "hidden_div";
                            $creatDocument_data_option_0 = "selected";
                            $creatDocument_data_option_1 = "";
                        }

                        @endphp


                  <select name="creatDocument" class="form-select text-black" onchange="showDiv('{{$creatDocument_data}}', this)">
                      <option {{$creatDocument_data_option_0}} value="0">Нет</option>
                      <option {{$creatDocument_data_option_1}} value="1">Да</option>
                  </select>



                </div>

                    <div id="{{$creatDocument_data}}">
                    <br>
                        <div class="mb-3 row" >
                            <P class="col-sm-5 col-form-label"> Выберите на какую организацию создавать заказы: </P>
                            <div class="col-sm-7">



                                <select name="Organization"  id="hidden_Organization" class="form-select text-black" onclick="PaymentAccountFun()" >
                                    @if ($Organization != "0")
                                        <option selected value="{{ $Organization->id }}"> {{ ($Organization->name) }} </option>
                                    @endif
                                        @foreach($Body_organization as $bodyItem)
                                            @if ($Organization->id != $bodyItem->id)
                                                <option value="{{ $bodyItem->id }}"> {{ ($bodyItem->name) }} </option>
                                            @endif
                                        @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="mb-3 row">
                            <P class="col-sm-5 col-form-label"> Выберите какой тип документов создавать: </P>
                            <div class="col-sm-7">
                                <select name="Document" class="form-select text-black" >
                                    @if($Document == "0")
                                        <option selected value="0">Не создавать</option>
                                        <option value="1">Отгрузка</option>
                                        <option value="2">Отгрузка + счет-фактура выданный</option>
                                    @endif
                                    @if($Document == "1")
                                        <option value="0">Не создавать</option>
                                        <option selected value="1">Отгрузка</option>
                                        <option value="2">Отгрузка + счет-фактура выданный</option>
                                    @endif
                                    @if($Document == "2")
                                        <option value="0">Не создавать</option>
                                        <option value="1">Отгрузка</option>
                                        <option selected value="2">Отгрузка и счет-фактура выданный</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <P class="col-sm-5 col-form-label"> Выберите какой тип платежного документа создавать: </P>
                            <div class="col-sm-7">
                                <select id="PaymentDocument" name="PaymentDocument" class="form-select text-black"  onclick="PaymentDocumentFun()">
                                    @if($PaymentDocument == "0")
                                        <option selected value="0">Не создавать</option>
                                        <option value="1">Приходной ордер</option>
                                        <option value="2">Входящий платёж </option>
                                    @endif
                                    @if($PaymentDocument == "1")
                                        <option value="0">Не создавать</option>
                                        <option selected value="1">Приходной ордер</option>
                                        <option value="2">Входящий платёж </option>
                                    @endif
                                    @if($PaymentDocument == "2")
                                        <option value="0">Не создавать</option>
                                        <option value="1">Приходной ордер</option>
                                        <option selected value="2">Входящий платёж </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="mb-3" id="hidden_PaymentAccount">
                            <div class="row">
                            <P class="col-sm-5 col-form-label"> Выберите расчетный счет: </P>
                            <div class="col-sm-7">
                                @foreach($Body_organization as $row)
                                    <div class="Payment" id="Payment_{{$row->id}}">
                                        @php
                                            $id = $row->id;
                                            $array_element = [];
                                            $url_accounts = "https://online.moysklad.ru/api/remap/1.2/entity/organization/".$id."/accounts";
                                            $clinet = new \App\Http\Controllers\GuzzleClient\ClientMC($url_accounts, $apiKey);
                                            $Body_accounts = $clinet->requestGet()->rows;
                                        @endphp

                                        <select name="{{$row->id}}" class="form-select text-black">
                                            @if (array_key_exists(0, $Body_accounts))

                                                @if ($Organization->id == $row->id)
                                                    <option selected value="{{$PaymentAccount}}"> {{ $PaymentAccount }}</option>
                                                @endif

                                            @foreach ($Body_accounts as $Body_accounts_item)
                                                @if($PaymentAccount != $Body_accounts_item->accountNumber)
                                                    <option value="{{$Body_accounts_item->accountNumber}}"> {{ $Body_accounts_item->accountNumber }}</option>
                                                @endif
                                            @endforeach
                                            @else <option>Нет расчетного счёта</option>
                                            @endif
                                        </select>



                                    </div>

                                @endforeach
                            </div>
                        </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="form-label mb-3"> Вебхуки   <button type="button" class="btn btn-new fa-solid fa-circle-info myPopover1"
                                                                              data-toggle="popover" data-placement="right" data-trigger="focus"
                                                                              data-content="Данный вебхуки необходимо вставить в UDS &#8594; Настройки &#8594; Интеграция &#8594; Вебхуки ">
                                </button></label>
                            <script> $('.myPopover1').popover(); </script>

                            <div class="col-5">
                                <label class="mt-2"> Получение новых заказов </label>
                            </div>
                            <div class="col-7">
                                <div  class=" row mb-2 mx-2 bg-myBlue rounded">
                                    <div class="col-1">
                                       <i onclick="myWebXyk1()" class="text-orange btn fa-solid fa-link p-2 "></i>
                                    </div>
                                    <div id="myWebXyk1" class="mt-1 col-11 myWebXyk1 s-min-16">
                                        https://smartuds.kz/api/webhook/{{$accountId}}/product
                                    </div>
                                    </div>
                            </div>
                            <div class="col-5 mt-2">
                                <label class="mt-2"> Получение новых клиентах </label>
                            </div>
                            <div class="col-7 mt-2">
                                <div  class=" row mb-2 mx-2 bg-myBlue rounded">
                                    <div class="col-1">
                                        <i onclick="myWebXyk2()" class="text-orange btn fa-solid fa-link p-2"></i>
                                    </div>
                                    <div id="myWebXyk2" class="mt-1 col-11 myWebXyk2 s-min-16">
                                        https://smartuds.kz/api/webhook/{{$accountId}}/client
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>

                </div>




            </div>

                <hr class="href_padding">
                <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                    <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить
                </button>

        </form>
    </div>

    <script>
        function myWebXyk1() {
            var WebXyk = document.querySelector('.myWebXyk1');

            var range = document.createRange();
            range.selectNode(WebXyk);
            window.getSelection().addRange(range);

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copy WebXyk command was ' + msg);
            } catch(err) {
                console.log('Oops, unable to copy');
            }
            window.getSelection().removeAllRanges();
        }

        function myWebXyk2() {
            var WebXyk = document.querySelector('.myWebXyk2');
            var range = document.createRange();
            range.selectNode(WebXyk);
            window.getSelection().addRange(range);

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copy WebXyk command was ' + msg);
            } catch(err) {
                console.log('Oops, unable to copy');
            }
            window.getSelection().removeAllRanges();
        }

        function PaymentAccountFun(){
            var select = document.getElementById('hidden_Organization');
            var option = select.options[select.selectedIndex];
            $(".Payment").hide();
            $("#Payment_" + option.value).show();
        }
        PaymentAccountFun();

        function PaymentDocumentFun(){
            var select = document.getElementById('PaymentDocument');
            var option = select.options[select.selectedIndex];
            if (option.value == 2){
                document.getElementById("hidden_PaymentAccount").style.display = "block";
            }else {
                document.getElementById("hidden_PaymentAccount").style.display = "none";
            }
        }
        PaymentDocumentFun()

    </script>


@endsection

<script>
    function showDiv(divId, element)
    {
        document.getElementById(divId).style.display = element.value == 1 ? 'block' : 'none';
    }
    function showPaymentAccount(divId, element)
    {
        document.getElementById(divId).style.display = element.value == 2 ? 'block' : 'none';
    }

</script>

<style>

    #hidden_div {
        display: none;
    }

    #anti_hidden_div {
        display: block;
    }

    #hidden_PaymentAccount {
        display: none;
    }

    .s-min-16 {
        font-size: 16px;
    }

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

    .bg-myBlue {
        color: black;
        background-color: #ffffff;
        border-radius: 20px;
        border-width: 1px;
        border-style: solid;
        border-color: #ced4da;
    }
</style>

{{--

<div id="hidden_PaymentAccount">
    <div class="row">
        <P class="col-sm-5 col-form-label"> Выберите расчетный счет: </P>
        <div class="col-sm-7">

            @foreach($Body_organization as $row)

                <div>
                    @php
                        $id = $row->id;
                        $array_element = [];
                        $url_accounts = "https://online.moysklad.ru/api/remap/1.2/entity/organization/".$id."/accounts";
                        $clinet = new \App\Http\Controllers\GuzzleClient\ClientMC($url_accounts, $apiKey);
                        $Body_accounts = $clinet->requestGet()->rows;

                        if (array_key_exists(0, $Body_accounts)) {
                            foreach ($Body_accounts as $item) { array_push($array_element, $item->accountNumber); } }
                        else { $array_element = [ 0 => "Нету Расчетного счета"];
                        }
                    @endphp
                    <select name="PaymentAccount" class="form-select text-black">
                        <option selected></option>
                        @foreach ($array_element as $array_element_item)
                            <option value="{{$array_element_item}}"> {{ $array_element_item }}</option>
                        @endforeach
                    </select>
                </div>

            @endforeach



            --}}
{{--  @php
            $param = 1;
            @endphp
            @foreach($Body_organization as $row)
                @if($param == 1)
                     <div class="some"  id="some_{{  $row->id }}"  style="display:block;">
                @else
                    <div class="some"  id="some_{{  $row->id }}"  style="display:none;">
                @endif
                @php
                    $id = $row->id;
                    $array_element = [];
                    $url_accounts = "https://online.moysklad.ru/api/remap/1.2/entity/organization/".$id."/accounts";
                    $clinet = new \App\Http\Controllers\GuzzleClient\ClientMC($url_accounts, $apiKey);
                    $Body_accounts = $clinet->requestGet()->rows;

                    if (array_key_exists(0, $Body_accounts)) {
                        foreach ($Body_accounts as $item) { array_push($array_element, $item->accountNumber); } }
                    else { $array_element = [ 0 => "Нету Расчетного счета"];
                    }
                @endphp
                <select name="PaymentAccount" class="form-select text-black">
                    <option selected></option>
                    @foreach ($array_element as $array_element_item)
                        <option value="{{$array_element_item}}"> {{ $array_element_item }}</option>
                    @endforeach
                </select>
            </div>
            @php
                $param = $param+1;
            @endphp
        @endforeach--}}{{--


        </div>
    </div>
    <script type="text/javascript">
        $('#parent_id').on('change',function(){
            $(".some").hide();
            var some = $(this).find('option:selected').val();
            $("#some_" + some).show();});
    </script>
</div>--}}
