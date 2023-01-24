
@extends('layout')
@section('item', 'link_2')
@section('content')

    <div class="content p-4 mt-2 bg-white text-Black rounded">

        <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
            <div class="col-10" style="margin-top: 1.2rem"> <span class="text-white" style="font-size: 20px">  Настройки &#8594; Главное </span></div>
            <div class="col-2 text-center">
                <img src="https://smarttis.kz/Config/logo.png" width="40%"  alt="">
                <div class="text-white" style="font-size: 11px; margin-top: 8px"> Топ партнёр сервиса МойСклад </div>
            </div>
        </div>

        @isset($message)

            <div class="{{$message['alert']}} mt-1"> {{ $message['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset


        <form action="  {{ route( 'setSettingIndex' , [ 'accountId' => $accountId,  'isAdmin' => $isAdmin ] ) }} " method="post">
        @csrf <!-- {{ csrf_field() }} -->


            <div class="row mt-1 p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">UDS данные</div>
                </div>
                <div onclick="toggleClick(1)" class="col-1 d-flex justify-content-end " style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on"  class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
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
                <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end " style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off_2" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on_2"  class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>


           <div id="update_uds_data">

               <div class="mt-2 row">
                   <div class="col-6">
                       <label class="row mx-1">
                           <div class="col-9"> Выберите категорию: </div>
                           <button type="button" class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover3 "
                                   data-toggle="popover" data-placement="right" data-trigger="focus"
                                   data-content="Выберите откуда будет браться товары">
                           </button>
                       </label>
                       <div class="col-10">
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
               <div class="mt-2 row">
                   <div class="col-6">
                       <label class="row mx-1">
                           <div class="col-9">  Изменение товаров: </div>
                           <button type="button" class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover4 "
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
                           <div class="col-9">  Выберите склад, для остатков товара: </div>
                           <button type="button" class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover5 "
                                   data-toggle="popover" data-placement="right" data-trigger="focus"
                                   data-content="По данному складу будут отправляться остатки в UDS и на данный склад будет создаваться заказ">
                           </button>
                       </label>
                       <div class="col-10">
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


    <script>
        let URL = "https://smartuds.kz/CountProduct"
        //let URL = "https://uds/CountProduct"
        let accountId = "{{ $accountId }}"
        let companyId = "{{ $companyId }}"
        let TokenUDS = "{{ $TokenUDS }}"

        Visible("none")
        window.document.getElementById('companyId').value = companyId
        window.document.getElementById('TokenUDS').value = TokenUDS




        function toggleClick(id){

            if (id === 1){
                let toggle_off = window.document.getElementById('toggle_off')
                let toggle_on = window.document.getElementById('toggle_on')

                let T1 = window.document.getElementById('uds_data')

                if (toggle_off.style.display == "none"){
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

                let  T2 = window.document.getElementById('update_uds_data')
                if (toggle_off_2.style.display == 'none'){
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


        window.onload = function() {
            CountProduct()
        };


        function Visible(params){
            document.getElementById("VisibleCountProduct").style.display = params;
        }

        function CountProduct(){
            let select = document.getElementById('ProductFolder');
            let option = select.options[select.selectedIndex];
            let folderName = option.text;

            let data = {
                accountId: accountId,
                folderName: folderName,
            };

            let settings = ajax_settings(URL, 'POST', data);
            console.log( URL + ' setting: ↓')
            console.log(settings)

            $.ajax({
                url: URL,
                method: 'post',
                dataType: 'json',
                data: data,
                success: function(response){
                    console.log( URL + ' response: ↓')
                    console.log(response)
                    let responseTextPars = response
                    let StatusCode = responseTextPars.StatusCode

                    if (StatusCode === 200) {
                        window.document.getElementById("CountProduct").innerHTML = responseTextPars.Body
                        Visible("block");
                    } else {
                        Visible("none");
                    }
                }
            });
        }

        function ajax_settings(url, method, data){
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

