
@extends('widgets.index')

@section('counterparty')

    <script>
        var GlobalobjectId;
        var GlobalURL;
        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;
            GlobalobjectId = receivedMessage.objectId;
            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    var responseTextPars = JSON.parse(this.responseText);

                    window.document.getElementById("object").innerHTML = responseTextPars.email;
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

    </script>

    <div class="content p-1 mt-2 bg-white text-Black rounded">

        <div class="row uds-gradient ">
            <div class=" p-2 col-10 text-white">
                <img src="https://smartuds.kz/Config/UDS.png" width="30" height="30" class="mx-4" alt="">
                <label class="from-label">Клиент </label>
            </div>
            <div class="col-2 p-2">
                <button type="submit" onclick="update()" class="myButton btn text-white "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
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

    .myButton {
        box-shadow: 0px 10px 14px -7px #276873 !important;
        background-color: #00a6ff !important;
        border-radius:50px !important;
        display:inline-block !important;
        cursor:pointer !important;
        padding:5px 5px !important;
        text-decoration:none !important;
    }
    .myButton:hover {
        background-color: #fffdfd !important;
        color: #111111;
    }
    .myButton:active {
        position: relative !important;
        top: 1px !important;
    }


</style>
