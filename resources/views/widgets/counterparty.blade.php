
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
                    var responseTextPars = JSON.parse(this.responseText);
                    var participant = responseTextPars.participant;
                    GlobalxRefURL = "https://admin.uds.app/admin/customers/"+participant.id+'/info';
                    window.document.getElementById("object").innerHTML = responseTextPars.email;
                    window.document.getElementById("displayName").innerHTML = responseTextPars.displayName;
                    window.document.getElementById("lastTransactionTime").innerHTML = participant.lastTransactionTime.substr(0,10);
                });
                GlobalURL = "{{$getObjectUrl}}" + receivedMessage.objectId;
                oReq.open("GET", GlobalURL);
                oReq.send();
            }
        });

        function update(){
            var xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                window.document.getElementById("object").innerHTML = this.responseText;
            });

            xmlHttpRequest.open("GET", GlobalURL);
            xmlHttpRequest.send();
        }

        function xRefURL(){
            window.open(GlobalxRefURL);
        }
    </script>

    <div class="content bg-white text-Black rounded">

        <div class="row uds-gradient ">
            <div class="mx-2 p-1 col-10 text-white">
                <img src="https://smartuds.kz/Config/UDS.png" width="30" height="30" class="mx-2" >
                <label> Клиент <button onclick="xRefURL()" class="btn btn-light"><i class="fa-solid fa-chart-bar"></i></button>
                </label>
            </div>
            <div class="col-2 p-2">
                <button type="submit" onclick="update()" class="myButton btn "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
            </div>
            <div class="row mx-2 text-white">
                <h5 id="displayName" class=""></h5>
                <div class="s-min text-muted">Последняя покупка <span id="lastTransactionTime"></span> </div>
            </div>

        </div>

         <br>
        <p> email =  <span id="object"></span> </p>

    </div>
@endsection

<style>

    .uds-gradient{
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }
    .s-min{
        font-size: 8pt;
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


</style>
