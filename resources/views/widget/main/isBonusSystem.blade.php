<script>
    function sendAccrueFUNCTION(Val) {
        operations_points = 0
        if (Val === 0) {
            document.getElementById("sendQR").style.display = "none"
            document.getElementById("QRCodePoint").value = ""
            operations_user = OLDPhone
            info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, operations_points, operations_availablePoints_Nubmer)
        }
        if (Val === 1) {
            document.getElementById("sendQR").style.display = "block"
            document.getElementById("QRCode").value = ''
            operations_user = OLDQRCode
        }
    }


    function sendAccrueOrCancellation(myRadio) {
        document.getElementById('QRCode').value = ""
        document.getElementById('total').innerText = "0"
        document.getElementById('cashBackOperation').innerText = "0"
        document.getElementById('availablePoints').innerText = "0"
        document.getElementById('QRCodePoint').value = "0"

        document.getElementById('buttonOperations').style.display = "none"
        document.getElementById("sendCancellation").style.display = "none"
        document.getElementById("sendPoint").style.display = "none";
        let div = myRadio.value;
        if (div === "sendAccrue") {
            sendAccrueFUNCTION(operationsAccrue)
        }
        if (div === "sendCancellation") {
            sendCancellationFUNCTION(operationsCancellation)
            document.getElementById("sendPoint").style.display = "block";
        }
    }

    function sendCancellationFUNCTION(Val) {
        operations_points = 0
        document.getElementById("sendCancellation").style.display = "block";
        if (Val === 0) {
            document.getElementById("sendQR").style.display = "none"
            document.getElementById("QRCodePoint").value = ""
            operations_user = OLDPhone
            info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, operations_points, operations_availablePoints_Nubmer)
        }
        if (Val === 1) {
            document.getElementById("sendQR").style.display = "block"
            document.getElementById("QRCode").value = ''
            operations_user = OLDQRCode
        }
    }
</script>
