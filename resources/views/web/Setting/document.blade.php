
@extends('layout')

@section('content')


    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <h4> <i class="fa-solid fa-gears text-orange"></i> Настройка документов </h4>

        <br>



        <form action=" " method="post">
        @csrf <!-- {{ csrf_field() }} -->


            <div class="mb-3 row">
                <div class="col-sm-5">
                    <label class="mt-1 from-label">Создавать документы при заказе с UDS ? </label>
                </div>
                <div class="col-sm-2">
                  <select name="creatDocument" class="form-select text-black" onchange="showDiv('hidden_div', this)">
                      <option selected value="0">Нет</option>
                      <option value="1">Да</option>
                  </select>
                </div>
                <div id="hidden_div">
                    <br>
                    <div class="mb-3 row" >
                        <P class="col-sm-5 col-form-label"> Выберите на какую организацию создавать заказы: </P>
                        <div class="col-sm-7">
                            <select name="Organization"  id="parent_id" class="form-select text-black dynamic" ><?php $value = 0; ?>
                                    @foreach($Body_organization as $bodyItem)
                                            <option value="{{ $bodyItem->id }}"> {{ ($bodyItem->name) }} </option> <?php $value++; ?>
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
                            <select name="PaymentDocument" class="form-select text-black" onchange="showPaymentAccount('hidden_PaymentAccount', this)">
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
                    <div id="hidden_PaymentAccount">
                        <div class="row">
                            <P class="col-sm-5 col-form-label"> Выберите расчетный счет: </P>
                            <div class="col-sm-7">
                                @foreach($Body_organization as $row)
                                <div class="some"  id="some_{{  $row->id }}"  style="display:none;">
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
                            </div>
                        </div>
                            <script type="text/javascript">
                                $('#parent_id').on('change',function(){
                                    $(".some").hide();
                                    var some = $(this).find('option:selected').val();
                                    $("#some_" + some).show();});
                            </script>
                    </div>
                    <br>
                    <div class="mb-3 row">

                        <P class="col-sm-5 col-form-label"> Выберите на какой склад создавать заказ: </P>
                        <div class="col-sm-7">
                            <select name="Store" class="form-select text-black " >
                                @foreach($Body_store as $Body_store_item)
                                    @if ( $Store == $Body_store_item->name )
                                        <option selected value="{{ $Body_store_item->name }}"> {{ ($Body_store_item->name) }} </option>
                                    @else
                                        <option value="{{ $Body_store_item->name }}"> {{ ($Body_store_item->name) }} </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>




            </div>

            <hr class="href_padding">
            <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить </button>


        </form>
    </div>





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

    #hidden_PaymentAccount {
        display: none;
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

</style>
