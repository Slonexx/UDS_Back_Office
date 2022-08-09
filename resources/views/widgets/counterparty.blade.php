
@extends('widgets.index')

@section('counterparty')

    <script>

        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;

            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    window.document.getElementById("object").innerHTML = this.responseText;
                });

                oReq.open("GET", "{{$getObjectUrl}}" + receivedMessage.objectId);
                oReq.send();
                console.log("oReq = "+oReq)
            }
        });

    </script>

    <div class="content p-1 mt-2 bg-white text-Black rounded">
       <button id="update" class=" btn text-orange "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
        <br>
        <p> <span id="object"></span> </p>

    </div>
@endsection

    <script type="text/javascript">
        $('#update').on('change',function(){

            window.addEventListener("message", function(event) {
                var receivedMessage = event.data;

                if (receivedMessage.name === 'Open') {
                    var oReq = new XMLHttpRequest();
                    oReq.addEventListener("load", function() {
                        window.document.getElementById("object").innerHTML = this.responseText;
                    });

                    oReq.open("GET", "{{$getObjectUrl}}" + receivedMessage.objectId);
                    oReq.send();
                }
            });

        });


    </script>

