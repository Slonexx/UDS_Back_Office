
@extends('widgets.index')

@section('counterparty')

    <script>
        var GlobalobjectId;
        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;
            GlobalobjectId = receivedMessage.objectId;
            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    var responseTextPars = JSON.parse(this.responseText);

                    window.document.getElementById("object").innerHTML = responseTextPars.email;
                });

                oReq.open("GET", "{{$getObjectUrl}}" + receivedMessage.objectId);
                oReq.send();
            }
        });

        function update(){
            var xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                window.document.getElementById("object").innerHTML = this.responseText;
            });

            xmlHttpRequest.open("GET", "{{$getObjectUrl}}" + GlobalobjectId);
            xmlHttpRequest.send();
        }

    </script>

    <div class="content p-1 mt-2 bg-white text-Black rounded">

        <div class="row">
            <div class="col-sm-5">

                <img src="https://uds.app/img/fav.png" width="20" height="20" class="d-inline-block align-top" alt="">
                Клиент UDS

            </div>

            <div class="col-sm-5">

                <button type="submit" onclick="update()" class="btn-new btn text-orange "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>

            </div>

        </div>

         <br>
        <p> email =  <span id="object"></span> </p>

    </div>
@endsection
