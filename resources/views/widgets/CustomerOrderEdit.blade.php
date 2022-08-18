
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
                    var BonusPoint = message.BonusPoint;
                    var points = message.points;

                    if (StatusCode == 200) {
                        GlobalxRefURL = "https://admin.uds.app/admin/orders?order="+message.id;
                        window.document.getElementById("OrderID").innerHTML = message.id;
                        var icon = message.icon.replace(/\\/g, '');
                        window.document.getElementById("icon").innerHTML = icon;

                        window.document.getElementById("cashBack").innerHTML = BonusPoint;
                        window.document.getElementById("points").innerHTML = points;

                        if (message.state == "NEW") {
                            document.getElementById("ButtonComplete").style.display = "block";
                            document.getElementById("Complete").style.display = "none";
                        } else {
                            document.getElementById("ButtonComplete").style.display = "none";
                            document.getElementById("Complete").style.display = "block";
                        }

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
        <div class="row uds-gradient p-2">
            <div class="col-2">
                <img src="https://smartuds.kz/Config/UDS.png" width="30" height="30" >
            </div>
            <div class="col-10 text-white mt-1 row">
                    <div class="col-11">
                        <label onclick="xRefURL()" style="cursor: pointer">
                            Заказ № <span id="OrderID"></span> <span class="mx-1"></span>
                        </label>
                    </div>
                    <div class="col-1">
                        <i onclick="xRefURL()" class="fa-solid fa-arrow-up-right-from-square" style="cursor: pointer"></i>
                    </div>
            </div>

            <div class="row">
                <div class="col-8">
                </div>
                <div class="col-4 bg-light rounded-pill s-min mt-1 p-1">
                    <span class="mx-1" id="icon"></span>
                </div>
            </div>
        </div>

        <div class="row mt-3 s-min">
            <div class="col-1">

            </div>
            <div class="col-10 row">
                <div id="ButtonComplete" class="row text-center" style="display: none;">
                    <button onclick="" class="btn btn-success rounded-pill">Завершить заказ</button>
                </div>
                <div id="Complete" class="row" style="display: none;">
                    <div class="row mt-2">
                        <div class="col-10">
                            Бонусов потрачено:
                        </div>
                        <div class="col-1">
                            <span class="p-1 text-white bg-primary rounded-pill" id="points"></span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-10">
                            Бонусов начислено:
                        </div>
                        <div class="col-1">
                            <span class="p-1 text-white bg-success rounded-pill" id="cashBack"></span>
                        </div>
                    </div>

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
