@extends('widgets.index')
@section('content')
    <script>
        const hostWindow = window.parent
        let productID
        let accountId = "{{$accountId}}"

        let receivedMessage = {
            "name":"Open",
            "extensionPoint":"entity.product.edit",
            "objectId":"fca739a0-b2cc-11ed-0a80-045f0017a3c2",
            "messageId":1,
            "displayMode":"expanded"
        };


        //window.document.getElementById('message').style.display = "none"
        //window.document.getElementById('Name').value = ""
        //window.document.getElementById('offer').checked = false
        //window.document.getElementById('skipLoyalty').checked  = false
        //window.document.getElementById('price').checked  = false


        //window.addEventListener("message", function(event) {
            //const receivedMessage = event.data;
            if (receivedMessage.name === 'Open') {

                productID = receivedMessage.objectId;
                let data = {
                    accountId: accountId,
                    productID: productID,
                };

                let settings = ajax_settings('https://smartuds.kz/Product/Info', 'GET', data)
                //let settings = ajax_settings('https://uds/Product/Info', 'GET', data)
                console.log('widget setting attributes: ↓')
                console.log(settings)

                $.ajax(settings).done(function (response) {
                    console.log("https://smartuds.kz/Product/Info" + ' response ↓ ')
                    console.log(response)
                    window.document.getElementById('download').style.display = "none"
                    window.document.getElementById('activated').style.display = "block"


                    let sendingMessage = {
                        name: "OpenFeedback",
                        correlationId: receivedMessage.messageId
                    };
                    hostWindow.postMessage(sendingMessage, '*');

                    if (response.code === 200){

                        if (response.ProductToUDS === true){
                            window.document.getElementById('Name').value = response.MainName

                            let select = document.getElementById('Category')
                            for (let i = 0; i < response.Category.length; i++) {
                                let opt = document.createElement('option')
                                opt.value = response.Category[i].id
                                opt.innerHTML = response.Category[i].name
                                select.appendChild(opt)
                            }

                            if (response.nodeId != null) window.document.getElementById('Category').value = response.nodeId
                            window.document.getElementById('Measurement').value = response.Measurement
                            if (response.offer != null) {
                                window.document.getElementById('offer').checked = true
                                window.document.getElementById('skipLoyalty').checked  = true
                            }
                            window.document.getElementById('price').value = response.price
                            window.document.getElementById('sku').value = response.sku
                        }



                    }




                    if (response.code === 205){
                       window.document.getElementById('message').style.display = "block"
                       window.document.getElementById('message').innerText = response.message
                    }
                });
            }

       // });

        function ajax_settings(url, method, data){
            return {
                "url": url,
                "method": "GET",
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }
    </script>


    <div id="download" style="display: block">
        <img style="width: 100%" src="https://smartrekassa.kz/Config/download.gif" alt="">
    </div>

    <div id="activated" class="content bg-white text-Black rounded" style="display: none">
        <div id="message" class="mt-2 alert alert-danger alert-dismissible fade show in text-center" style="display: none"> </div>
        <div id="IdUDSProduct" style="display: block">
            <div class="row uds-gradient rounded p-2 pb-2" style="margin-top: -1.5rem">
                <div class="text-center" style="margin-top: 1.2rem"> <span class="text-white"> Данный товар есть в UDS </span></div>
            </div>
        </div>
     <div class="row">
         <div class="col-1"></div>
         <div class="col-10">

             <div class="row mt-1">
                 <div class="col-12">Название</div>
                 <div class="col-12">
                     <input type="text" name="Name" id="Name" class="form-control" required maxlength="100" value=" ">
                 </div>
             </div>
             <div class="row mt-1">
                 <div class="col-12">Разместить в категории</div>
                 <div class="col-12">
                     <select type="text" name="Category" id="Category" class="form-control">
                         <option selected value="0">Без категорий</option>
                     </select>
                 </div>
             </div>
             <div class="row mt-1">
                 <div class="col-12">Единица измерения</div>
                 <div class="col-12">
                     <select type="text" name="Measurement" id="Measurement" class="form-control">
                         <option value="PIECE">Штука</option>
                         <option value="CENTIMETRE">Сантиметры</option>
                         <option value="METRE">Метры</option>
                         <option value="MILLILITRE">Миллилитры</option>
                         <option value="LITRE">Литры</option>
                         <option value="GRAM">Граммы</option>
                         <option value="KILOGRAM">Килограммы</option>
                     </select>
                 </div>
             </div>
             <div class="row mt-1">
                 <div class="col-12">
                     <div class="form-check">
                         <input class="form-check-input" type="checkbox" value="" id="offer">
                         <label class="form-check-label" for="offer">
                             Акционный товар
                         </label>
                     </div>
                     <div class="form-check">
                         <input class="form-check-input" type="checkbox" value="" id="skipLoyalty">
                         <label class="form-check-label" for="skipLoyalty">
                             Не применять бонусную программу
                         </label>
                     </div>
                 </div>
             </div>
             <div class="row mt-1">
                 <div class="col-6">Цена</div>
                 <div class="col-6">Акционная цена</div>
                 <div class="col-6">
                     <input type="text" name="price" id="price" class="form-control" required maxlength="100" value=" ">
                 </div>
                 <div class="col-6">
                     <input type="text" name="offerPrice" id="offerPrice" class="form-control" required maxlength="100" value=" ">
                 </div>
             </div>
             <div class="row mt-1">
                 <div class="col-12">Артикул</div>
                 <div class="col-12">
                     <input type="text" name="sku" id="sku" class="form-control" required maxlength="100" value=" ">
                 </div>
             </div>

             <button class="mt-1 btn btn-outline-dark gradient_focus"> Изменить товар </button>
         </div>
     </div>
    </div>

@endsection
<style>

    .uds-gradient{
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }

    .gradient_focus:hover{
        color: white;
        border: 0px;
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }

    .gradient_focus:active, .gradient_focus:focus{
        background-color: rgb(145,0,253);
        background-image: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
        border: 0px;
        background-size: 100%;
        -webkit-background-clip: text;
        -moz-background-clip: text;
        -webkit-text-fill-color: transparent;
        -moz-text-fill-color: transparent;
    }


    .gradient_invert{
        background-image: linear-gradient(135deg, #e1eaf8 0%, #f5f7fa 100%);
    }


</style>
