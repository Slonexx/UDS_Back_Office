
@extends('widgets.index')

@section('counterparty')

    <script>

        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;

            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    window.document.getElementById("object").innerHTML = this.responseText;
                    console.log("text = " + this.responseText);
                });

                oReq.open("GET", "{{$getObjectUrl}}" + receivedMessage.objectId);
                console.log("objectId = " + receivedMessage.objectId);
                oReq.send();
            }
        });

    </script>

    <div class="content p-1 mt-2 bg-white text-Black rounded">
        <h1> Объект =  </h1> <span>{{$getObjectUrl}}</span>
        <br>
        <p> <span id="object"></span> </p>

    </div>
@endsection

