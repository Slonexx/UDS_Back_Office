
@extends('widgets.index')

@section('counterparty')

    <script>

        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;

            logReceivedMessage(receivedMessage);

            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    window.document.getElementById("object").innerHTML = this.responseText;
                });
                // В демо приложении отсутствует авторизация (между виджетом и бэкендом) - в реальных приложениях не делайте так (должна быть авторизация)!
                oReq.open("GET", "{{$getObjectUrl}}" + receivedMessage.objectId);
                oReq.send();

            }
        });

    </script>

    <div class="content p-1 mt-2 bg-white text-Black rounded">
        <h1> Данные с обчекта </h1>
        {{$getObjectUrl}}
    </div>
@endsection

