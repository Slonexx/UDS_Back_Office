
@extends('widgets.index')

@section('counterparty')
<div class="content p-1 mt-2 bg-white text-Black rounded">
    @php

    @endphp
    <br>
    <br>
    <p> Объект = <span id="object"></span> </p>
</div>
@endsection

<script type="text/javascript">

    window.addEventListener('message', function (event){
        var receivedMessage = event.data;
        window.document.getElementById("object").value = receivedMessage;
        console.log("receivedMessage = "+receivedMessage);

    })

</script>

{{--<script>
    var hostWindow = window.parent;

    window.addEventListener("message", function(event) {
        var receivedMessage = event.data;

        logReceivedMessage(receivedMessage);

        if (receivedMessage.name === 'Open') {
            var oReq = new XMLHttpRequest();
            oReq.addEventListener("load", function() {
                window.document.getElementById("object").innerHTML = this.responseText;
            });
            // В демо приложении отсутствует авторизация (между виджетом и бэкендом) - в реальных приложениях не делайте так (должна быть авторизация)!
            oReq.open("GET", "<?=$getObjectUrl?>" + receivedMessage.objectId);
            oReq.send();

            window.setTimeout(function() {
                var sendingMessage = {
                    name: "OpenFeedback",
                    correlationId: receivedMessage.messageId
                };
                logSendingMessage(sendingMessage);
                hostWindow.postMessage(sendingMessage, '*');

            }, getOpenFeedbackDelay());
        }
    });

    function logReceivedMessage(msg) {
        logMessage("→ Received", msg)
    }

    function logSendingMessage(msg) {
        logMessage("← Sending", msg)
    }

    function logMessage(prefix, msg) {
        var messageAsString = JSON.stringify(msg);
        console.log(prefix + " message: " + messageAsString);
        addMessage(prefix.toUpperCase() + " " + messageAsString);
    }

    function addMessage(item) {
        var messages = window.document.getElementById("messages");
        messages.innerHTML = item + "<br/>" + messages.innerHTML;
        messages.title += item + "\n";
    }

    function getOpenFeedbackDelay() {
        return window.document.getElementById("openFeedbackDelay").value
    }

    function toggleBorders(value) {
        body().className = value ? "borders" : "";
    }

    function showDimensions() {
        var dimensions = window.document.getElementById("dimensions");
        dimensions.innerText = body().offsetWidth + " x " + body().offsetHeight
    }

    function body() {
        return window.document.body;
    }
</script>--}}
