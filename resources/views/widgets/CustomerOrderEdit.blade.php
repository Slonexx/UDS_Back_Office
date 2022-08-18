
@extends('widgets.index')

@section('counterparty')

    <script>
        var GlobalobjectId;
        var GlobalURL;
        var GlobalxRefURL;
        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;
            GlobalobjectId = receivedMessage.objectId;
            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();

                oReq.addEventListener("load", function() {

                    console.log("responseText = " + this.responseText);

                    var responseTextPars = JSON.parse(this.responseText);
                    var StatusCode = responseTextPars.StatusCode;
                    var message = responseTextPars.message;
                    if (StatusCode == 200) {
                        GlobalxRefURL = "https://admin.uds.app/admin/orders?order="+message.id;
                        window.document.getElementById("OrderID").innerHTML = message.id;
                        var icon = message.icon.replace(/\\/g, '');
                        console.log('icon = ' + icon);
                        window.document.getElementById("state").innerHTML = message.state;
                        window.document.getElementById("icon").innerHTML = icon;
                    } else {

                    }





                });
                GlobalURL = "{{$getObjectUrl}}" + receivedMessage.objectId;
                oReq.open("GET", GlobalURL);
                oReq.send();
            }
        });

        function xRefURL(){
            window.open(GlobalxRefURL);
        }

    </script>

    @php
        $View = true;
    @endphp




    <div id="activated" class="content bg-white text-Black rounded">
        <div class="row uds-gradient mx-2 p-2">
            <div class="col-1">
                <img src="https://smartuds.kz/Config/UDS.png" width="30" height="30" >
            </div>
            <div class="col-6 text-white mt-1 mx-2">
                <label onclick="xRefURL()" style="cursor: pointer">
                    Заказ № <span id="OrderID"></span>
                    <span class="mx-1"></span>
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </label>
            </div>
            <div class="col-12">
                <div class="row bg-light rounded-pill text-black s-min mt-1 ">
                        <label id="icon"></label>
                        <label id="state"></label>

                </div>
            </div>

        </div>


    </div>









@endsection

<style>

    .uds-gradient{
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }
    .s-min{
        font-size: 10pt;
    }
    .s-min-8{
        font-size: 8px;
    }

    .myPM{
        padding-left: 4px !important;
        margin: 2px !important;
        margin-right: 11px !important;
    }

    .myButton {
        box-shadow: 0px 4px 5px 0px #5d5d5d !important;
        background-color: #00a6ff !important;
        color: white !important;
        border-radius:50px !important;
        display:inline-block !important;
        cursor:pointer !important;
        padding:5px 5px !important;
        text-decoration:none !important;
    }
    .myButton:hover {
        background-color: #fffdfd !important;
        color: #111111 !important;
    }
    .myButton:active {
        position: relative !important;
        top: 1px !important;
    }
    .my-bg-gray{
        background-color: #ebefff !important;
        color: #3b3c65;
        border-radius: 14px !important;
        overflow: hidden !important;
    }

    .my-bg-success{
        border-radius: 14px !important;
        overflow: hidden !important;
    }

</style>
