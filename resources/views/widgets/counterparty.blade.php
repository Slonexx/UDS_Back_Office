
@extends('widgets.index')

@section('counterparty')

    <script>

        document.getElementById("update").addEventListener("click", update);

        function update(){
            window.addEventListener("message", function(event) {
                var receivedMessage = event.data;

                if (receivedMessage.name === 'Open') {
                    var oReq = new XMLHttpRequest();
                    oReq.addEventListener("load", function() {
                        var responseTextPars = JSON.parse(this.responseText);
                        window.document.getElementById("object").innerHTML = this.responseText;
                        console.log(" Pars = " + responseTextPars.email)
                    });

                    oReq.open("GET", "{{$getObjectUrl}}" + receivedMessage.objectId);
                    oReq.send();
                }
            });
        }

        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;

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

    </script>

    <div class="content p-1 mt-2 bg-white text-Black rounded">
       <button type="submit" id="update" class="btn-new btn text-orange "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
        <br>
        <p> email =  <span id="object"></span> </p>

    </div>
@endsection

    <script>

        document.getElementById("demo").addEventListener("click", update);


        function update() {


        }

    </script>

