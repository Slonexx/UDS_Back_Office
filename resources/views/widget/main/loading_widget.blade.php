<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener("message", function (event) {
            //const receivedMessage = event.data;
            if (receivedMessage.name === 'Open') {
                clearWidget();
                GlobalobjectId = receivedMessage.objectId;
                let settings = ajax_settings(`${url}CustomerOrder/EditObject/${accountId}/${set_extensionPoint(receivedMessage.extensionPoint)}/${receivedMessage.objectId}`, "GET", null);
                console.log('initial request settings  ↓ ');
                console.log(settings);
                receivedMessage = null;
                $.ajax(settings).done(function (response) {
                    console.log('initial request response  ↓ ');
                    console.log(response);
                    let message = response.message;
                    switch (response.StatusCode) {
                        case "error": {
                            displayError(response.message);
                            break;
                        }
                        case "orders":
                        case "successfulOperation": {
                            displayOrderOrOperation(response, message);
                            break;
                        }
                        case "operation": {
                            displayOperation(response, message);
                            break;
                        }
                        default: {
                            displayError(response.message);
                            break;
                        }
                    }
                });
            }
        });





        function displayError(message) {
            document.getElementById("Error402").style.display = "block";
            document.getElementById("ErrorMessage").innerText = message;
        }

        function displayOrderOrOperation(response, message) {
            document.getElementById("activated").style.display = "block";
            document.getElementById("undefined").style.display = "none";
            GlobalUDSOrderID = message.id;
            let BonusPoint = message.BonusPoint;
            let points = message.points;
            document.getElementById("infoOrderOrOperations").innerText = response.StatusCode === "orders" ? 'Заказ №' : 'Операция №';
            GlobalxRefURL = response.StatusCode === "orders" ? `https://admin.uds.app/admin/orders?order=${message.id}` : "https://admin.uds.app/admin/operations";
            window.document.getElementById("OrderID").innerHTML = message.id;
            let icon = message.icon.replace(/\\/g, '');
            window.document.getElementById("icon").innerHTML = icon;
            window.document.getElementById("cashBack").innerHTML = BonusPoint;
            window.document.getElementById("points").innerHTML = points;
            setStateByStatus(message.state);
        }

        function displayOperation(response, message) {
            document.getElementById("activated").style.display = "none";
            document.getElementById("sendWarning").style.display = "none";
            document.getElementById("undefined").style.display = "block";
            document.getElementById("labelAccrue").style.display = "block";
            operationsAccrue = message.operationsAccrue;
            operationsCancellation = message.operationsCancellation;
            OLDPhone = message.phone;
            operations_user = message.phone;
            operations_user_uid = message.uid;
            operations_total = message.total;
            operations_availablePoints_Nubmer = message.availablePoints;
            operations_availablePoints = message.availablePoints;
            operations_skipLoyaltyTotal = message.SkipLoyaltyTotal;
            document.getElementById("labelAccrue").style.display = "block";
            document.getElementById("labelCancellation").style.display = "block";
            sendAccrueOrCancellation(window.document.getElementById("Accrue"));
        }
    });
</script>

{{--OLD CODE--}}
<script>
    /*
    *    window.addEventListener("message", function (event) {
            let receivedMessage = event.data

            if (receivedMessage.name === 'Open') {
                clearWidget()
                GlobalobjectId = receivedMessage.objectId;

                let settings = ajax_settings(url + 'CustomerOrder/EditObject/' + accountId + '/' + set_extensionPoint(receivedMessage.extensionPoint) + '/' + receivedMessage.objectId, "GET", null)
                console.log('initial request settings  ↓ ')
                console.log(settings)

                receivedMessage = null;

                $.ajax(settings).done(function (response) {
                    console.log('initial request response  ↓ ')
                    console.log(response)
                    let message = response.message
                    switch (response.StatusCode) {
                        case "error": {
                            document.getElementById("Error402").style.display = "block"
                            document.getElementById("ErrorMessage").innerText = response.message
                            break
                        }
                        case "orders": {
                            document.getElementById("activated").style.display = "block";
                            document.getElementById("undefined").style.display = "none";
                            GlobalUDSOrderID = message.id;
                            let BonusPoint = message.BonusPoint;
                            let points = message.points;
                            document.getElementById("infoOrderOrOperations").innerText = 'Заказ №';
                            GlobalxRefURL = "https://admin.uds.app/admin/orders?order=" + message.id;
                            window.document.getElementById("OrderID").innerHTML = message.id;
                            let icon = message.icon.replace(/\\/g, '');
                            window.document.getElementById("icon").innerHTML = icon;
                            window.document.getElementById("cashBack").innerHTML = BonusPoint;
                            window.document.getElementById("points").innerHTML = points;
                            setStateByStatus(message.state)
                            break
                        }
                        case "successfulOperation": {
                            document.getElementById("activated").style.display = "block";
                            document.getElementById("undefined").style.display = "none";
                            GlobalUDSOrderID = message.id;
                            let BonusPoint = message.BonusPoint;
                            let points = message.points;
                            document.getElementById("infoOrderOrOperations").innerText = 'Операция №';
                            GlobalxRefURL = "https://admin.uds.app/admin/operations"
                            window.document.getElementById("OrderID").innerHTML = message.id;
                            let icon = message.icon.replace(/\\/g, '');
                            window.document.getElementById("icon").innerHTML = icon;
                            window.document.getElementById("cashBack").innerHTML = BonusPoint;
                            window.document.getElementById("points").innerHTML = points;
                            setStateByStatus(message.state)
                            break
                        }
                        case "operation": {
                            document.getElementById("activated").style.display = "none"
                            document.getElementById("sendWarning").style.display = "none"
                            document.getElementById("undefined").style.display = "block"
                            document.getElementById("labelAccrue").style.display = "block"
                            operationsAccrue = message.operationsAccrue
                            operationsCancellation = message.operationsCancellation

                            OLDPhone = message.phone
                            operations_user = message.phone
                            operations_user_uid = message.uid
                            operations_total = message.total
                            operations_availablePoints_Nubmer = message.availablePoints
                            operations_availablePoints = message.availablePoints
                            operations_skipLoyaltyTotal = message.SkipLoyaltyTotal

                            document.getElementById("labelAccrue").style.display = "block"
                            document.getElementById("labelCancellation").style.display = "block"

                            sendAccrueOrCancellation(window.document.getElementById("Accrue"))
                            break
                        }
                        default: {
                            document.getElementById("Error402").style.display = "block"
                            document.getElementById("ErrorMessage").innerText = response.message
                            break
                        }
                    }
                })
            }
        });
    * */
</script>
