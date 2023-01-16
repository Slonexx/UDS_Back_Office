
@extends('layout')
@section('item', 'link_6')
@section('content')


        <div class="content p-4 mt-2 bg-white text-Black rounded">
            <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
                <div class="col-10" style="margin-top: 1.2rem"> <span class="text-white" style="font-size: 20px">  Настройки &#8594; Дополнительные настройки </span></div>
                <div class="col-2 text-center">
                    <img src="https://smarttis.kz/Config/logo.png" width="40%"  alt="">
                    <div class="text-white" style="font-size: 11px; margin-top: 8px"> Топ партнёр сервиса МойСклад </div>
                </div>
            </div>

        <br>

        @isset($message)
            <div class="{{$message['alert']}}"> {{ $message['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endisset

        <h5> <i class="fa-solid fa-list-check text-orange"></i> Дополнительные настройки при заказе с UDS </h5>



        <form action=" {{ route('setSettingAdd' , [ 'accountId' => $accountId, 'isAdmin' => $isAdmin ]) }} " method="post">
        @csrf <!-- {{ csrf_field() }} -->


            <div class="mb-2 row">
                <P class="col-sm-5 col-form-label"> Выберите канал продаж: </P>
                <div class="col-sm-7">
                    <select name="Saleschannel" class="form-select text-black " >
                        @if ($Saleschannel == "0")
                            <option value="0" selected>Не выбирать </option>
                        @else  <option value="{{$Saleschannel}}" selected> {{$Saleschannel}} </option>
                        <option value="0" >Не выбирать </option>
                        @endif
                        @foreach($Body_saleschannel as $Body_saleschannel_item)
                            @if ($Saleschannel != $Body_saleschannel_item->name)
                                <option value="{{ $Body_saleschannel_item->name }}"> {{ ($Body_saleschannel_item->name) }} </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <P class="col-sm-5 col-form-label"> Выберите проект:  </P>
                <div class="col-sm-7">
                    <select name="Project" class="form-select text-black " >
                        @if ($Project == "0")
                            <option value="0" selected>Не выбирать </option>
                        @else  <option value="{{$Project}}" selected> {{$Project}} </option>
                        <option value="0" >Не выбирать </option>
                        @endif
                        @foreach($Body_project as $Body_project_item)
                            @if ($Project != $Body_project_item->name)
                                <option value="{{ $Body_project_item->name}}"> {{ ($Body_project_item->name) }} </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-1 row pt-3">

                <H5 class="mt-1 from-label"> <i class="fa-solid fa-list-check text-orange"></i> Статусы заказов </H5>

            </div>

            <div class="mb-3 row">
                    <div class="mb-2 row mx-3">
                            <label class="col-sm-5 col-form-label"> 1) Новый заказ </label>
                            <div class="col-sm-7 ">
                                <select name="NEW" class="form-select text-black">
                                    @if($NEW == null)
                                        <option selected> Статус МойСклад </option>
                                    @else <option value="{{$NEW}}" selected>{{$NEW}}</option>
                                    @endif
                                    @foreach($Body_customerorder as $Body_customerorder_item => $dat)
                                        @if($dat->name != $NEW) <option value="{{ $dat->name }}"> {{ ($dat->name) }} </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    <div class="mb-2 row mx-3">
                        <label class="col-sm-5 col-form-label"> 2) Завершенный </label>
                        <div class="col-sm-7 ">
                            <select name="COMPLETED" class="form-select text-black">
                                @if($COMPLETED == null)
                                    <option selected> Статус МойСклад </option>
                                @else <option value="{{$COMPLETED}}" selected>{{$COMPLETED}}</option>
                                @endif
                                @foreach($Body_customerorder as $Body_customerorder_item => $dat)
                                    @if($dat->name != $COMPLETED) <option value="{{ $dat->name }}"> {{ ($dat->name) }} </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-2 row mx-3">
                        <label class="col-sm-5 col-form-label"> 3) Отменный </label>
                        <div class="col-sm-7 ">
                            <select name="DELETED" class="form-select text-black">
                                @if($DELETED == null)
                                    <option selected> Статус МойСклад </option>
                                @else <option value="{{$DELETED}}" selected>{{$DELETED}}</option>
                                @endif
                                @foreach($Body_customerorder as $Body_customerorder_item => $dat)
                                    @if($dat->name != $DELETED) <option value="{{ $dat->name }}"> {{ ($dat->name) }} </option>
                                    @endif
                                @endforeach
                            </select>
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

    #anti_hidden_div {
        display: block;
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
