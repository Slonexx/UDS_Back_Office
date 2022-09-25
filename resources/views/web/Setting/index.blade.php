
@extends('layout')

@section('content')

    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <h4> <i class="fa-solid fa-gears text-orange"></i> Данные для интеграции</h4>

        <br>
        @isset($message)

            <div class="{{$message['alert']}}"> {{ $message['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset


        <form action="  {{ route( 'setSettingIndex' , [ 'accountId' => $accountId,  'isAdmin' => $isAdmin ] ) }} " method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="text-black mx-2 mb-2">
                UDS данные
            </div>
            <div class="mb-3 row mx-1">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="mx-1">
                            <button type="button" class="btn btn-new fa-solid fa-circle-info myPopover1"
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Данный ID находится в UDS &#8594; Настройки &#8594; Интеграция &#8594; Данные для интеграции ">
                            </button> ID компании </label>

                        <script> $('.myPopover1').popover(); </script>

                        <div class="col-sm-10">
                            <input type="text" name="companyId" id="companyId" placeholder="ID компании"
                                   class="form-control form-control-orange" required maxlength="255" value="{{$companyId}}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <label class="mx-1">
                            <button type="button" class="btn btn-new fa-solid fa-circle-info myPopover2"
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Данный ID находится в UDS &#8594; Настройки &#8594; Интеграция &#8594; Данные для интеграции ">
                            </button>  API Key </label>

                        <script> $('.myPopover2').popover(); </script>

                        <div class="col-sm-10">
                            <input type="text" name="TokenUDS" id="TokenUDS" placeholder="API Key"
                                   class="form-control form-control-orange" required maxlength="255" value="{{$TokenUDS}}">
                        </div>
                    </div>
                </div>

            </div>

            <br>
            <div class="text-black mx-2 mb-2">
                Товары
            </div>

            <div class="mb-3 row mx-1">
                <div class="col-sm-6">
                    <label class="mx-1">
                        <button type="button" class="btn btn-new fa-solid fa-circle-info myPopover5"
                                data-toggle="popover" data-placement="right" data-trigger="focus"
                                data-content="Выберите откуда будет браться товары">
                        </button>  Выберите категорию:  </label>

                    <script> $('.myPopover5').popover(); </script>


                    <div class="col-sm-10">
                        <select id="ProductFolder" name="ProductFolder" class="form-select text-black" onchange="CountProduct()">

                            @if($ProductFolder != null)
                                <option value="{{ $ProductFolder['value'] }}"> {{ $ProductFolder['name'] }} </option>
                                @foreach ($Body_productFolder as $productFolderItem)
                                    @if ($productFolderItem->id != $ProductFolder['value'])
                                    <option value="{{$productFolderItem->id}}"> {{$productFolderItem->name}} </option>
                                    @endif
                                @endforeach
                            @else
                                @foreach ($Body_productFolder as $productFolderItem)
                                        <option value="{{$productFolderItem->id}}" >{{$productFolderItem->name}} </option>
                                @endforeach
                            @endif

                        </select>
                    </div>
                </div>
                <div id="VisibleCountProduct" class="col-sm-6 row mt-4" style="display: block">
                    <div class="row">
                        <div class="col-3 mt-2">
                        </div>
                        <div class="col-7 mt-2">
                            Товаров в категории:
                            <span id="CountProduct" class="mx-1 p-1 px-3 text-white bg-primary rounded-pill">  </span>
                        </div>
                        <div class="col-2 mt-2">
                            <i onclick="Visible()" class="fa-solid fa-circle-xmark text-danger" style="cursor: pointer"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3 row mx-1">
                <div class="col-sm-6">
                    <label class="mx-1">
                        <button type="button" class="btn btn-new fa-solid fa-circle-info myPopover3"
                                data-toggle="popover" data-placement="right" data-trigger="focus"
                                data-content="Выберите откуда будет изменяться товары">
                        </button>  Изменение товаров:  </label>

                    <script> $('.myPopover3').popover(); </script>


                    <div class="col-sm-10">
                        <select name="UpdateProduct" class="form-select text-black ">
                            <option value="0">МойСклад</option>
                            <option value="1">UDS</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mx-1">
                <div class="col-sm-6">
                    <label class="mx-1">
                        <button type="button" class="btn btn-new fa-solid fa-circle-info myPopover4"
                                data-toggle="popover" data-placement="right" data-trigger="focus"
                                data-content="По данному складу будут отправляться остатки в UDS и на данный склад будет создаваться заказ">
                        </button>  Выберите склад, для остатков товара:  </label>

                    <script> $('.myPopover4').popover(); </script>


                    <div class="col-sm-10">
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

            <hr class="href_padding">



            <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить </button>


        </form>
    </div>


    <script>
        var GlobalURL = "https://dev.smartuds.kz/CountProduct/{{$accountId}}/";
        CountProduct();

        function Visible(){
            document.getElementById("VisibleCountProduct").style.display = "none";
        }

        function CountProduct(){
            var select = document.getElementById('ProductFolder');
            var option = select.options[select.selectedIndex];
            var folderName = option.text;
            var xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                var responseTextPars = JSON.parse(this.responseText);
                var StatusCode = responseTextPars.StatusCode;

                if (StatusCode == 200) {
                    var Body = responseTextPars.Body;
                    window.document.getElementById("CountProduct").innerHTML = Body;
                    document.getElementById("VisibleCountProduct").style.display = "block";
                } else {
                    Visible();
                }

            });
            xmlHttpRequest.open("GET", GlobalURL + folderName);
            xmlHttpRequest.send();
        }

    </script>


@endsection



<style>
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
