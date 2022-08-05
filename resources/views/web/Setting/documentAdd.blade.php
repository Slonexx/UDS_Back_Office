
@extends('layout')

@section('content')


    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <h4> <i class="fa-solid fa-gears text-orange"></i> Дополнительные настройки </h4>
        <h6> Дополнительные настройки при заказе с UDS </h6>

        <br>



        <form action=" {{ route('setSettingAdd' , ['accountId' => $accountId] ) }} " method="post">
        @csrf <!-- {{ csrf_field() }} -->


            <div class="mb-3 row">
                <P class="col-sm-5 col-form-label"> Выберите проект: </P>
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
                <P class="col-sm-5 col-form-label"> Выберите канал продаж: </P>
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
