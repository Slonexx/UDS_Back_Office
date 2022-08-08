<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">

    <title>DummyApp: UDS </title>
    <meta name="description" content="DummyApp widget for Marketplace of MoySklad">
    <meta name="author" content="onekludov@moysklad.ru">

    <style>
        html {
            height: 100%;
        }
        body {
            line-height: 1;
            font-size: 12px;
            height: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .hint {
            cursor: default;
        }
        .borders {
            border: 1px solid silver;
        }
    </style>

    <script>
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
    </script>
</head>
<body>
    <div >

        {{$getObjectUrl}}

    </div>
</body>
</html>
