
@extends('widgets.index')

@section('counterparty')

    <script>
        var globalReceivedMessage;
        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;
            globalReceivedMessage = receivedMessage;
            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    var responseTextPars = JSON.parse(this.responseText);
                    window.document.getElementById("object").innerHTML = responseTextPars.email;
                    console.log(" Pars = " + responseTextPars.email)
                });

                oReq.open("GET", "{{$getObjectUrl}}" + receivedMessage.objectId);
                oReq.send();
            }
        });

        function update(){
            if (globalReceivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    var responseTextPars = JSON.parse(this.responseText);
                    window.document.getElementById("object").innerHTML = responseTextPars.email;
                    console.log(" Pars = " + responseTextPars.email)
                });

                oReq.open("GET", "{{$getObjectUrl}}" + globalReceivedMessage.objectId);
                oReq.send();
            }
        }

    </script>

    <div class="content p-1 mt-2 bg-white text-Black rounded">
       <button type="submit" onclick="update()" class="btn-new btn text-orange "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
        <br>
        <p> email =  <span id="object"></span> </p>

    </div>
@endsection
