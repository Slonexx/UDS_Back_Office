<!doctype html>
<html lang="en">
@include('head')

<body>

<script>
        const url = 'https://dev.smartuds.kz/'
        let GlobalobjectId
        let GlobalURL
        let GlobalxRefURL
        let GlobalUDSOrderID
        let OLDPhone
        let OLDQRCode

        let operations_total
        let operations_cash
        let operations_points
        let operations_skipLoyaltyTotal
        let operations_user
        let operations_cashier_id = "{{ $cashier_id }}"
        let operations_cashier_name = "{{ $cashier_name }}"

        window.addEventListener("message", function(event) {
            let receivedMessage = event.data;
            GlobalobjectId = receivedMessage.objectId;
            if (receivedMessage.name === 'Open') {
                let oReq = new XMLHttpRequest();
                document.getElementById("success").style.display = "none";
                document.getElementById("danger").style.display = "none";
                document.getElementById("sendWarning").style.display = "none";
                document.getElementById("buttonOperations").style.display = "none";
                oReq.addEventListener("load", function() {
                    let responseTextPars = JSON.parse(this.responseText);
                    let StatusCode = responseTextPars.StatusCode;
                    let message = responseTextPars.message;
                    GlobalUDSOrderID = message.id;
                    let BonusPoint = message.BonusPoint;
                    let points = message.points;

                    if (StatusCode == 200) {
                        document.getElementById("activated").style.display = "block";
                        document.getElementById("undefined").style.display = "none";

                        GlobalxRefURL = "https://admin.uds.app/admin/orders?order="+message.id;
                        window.document.getElementById("OrderID").innerHTML = message.id;
                        var icon = message.icon.replace(/\\/g, '');
                        window.document.getElementById("icon").innerHTML = icon;

                        window.document.getElementById("cashBack").innerHTML = BonusPoint;
                        window.document.getElementById("points").innerHTML = points;

                        if (message.state == "NEW") {
                            document.getElementById("ButtonComplete").style.display = "block";
                            document.getElementById("Complete").style.display = "none";
                            document.getElementById("Deleted").style.display = "none";
                        }
                        if (message.state == "COMPLETED") {
                            document.getElementById("Complete").style.display = "block";
                            document.getElementById("ButtonComplete").style.display = "none";
                            document.getElementById("Deleted").style.display = "none";
                        }
                        if (message.state == "DELETED") {
                            document.getElementById("Deleted").style.display = "block";
                            document.getElementById("Complete").style.display = "none";
                            document.getElementById("Complete").style.display = "none";
                        }

                    } else {
                        document.getElementById("activated").style.display = "none"
                        document.getElementById("undefined").style.display = "block"
                        document.getElementById("sendWarning").style.display = "none"
                        document.getElementById("buttonOperations").style.display = "block"

                        sendAccrueOrCancellation(window.document.getElementById("Accrue"))

                        OLDPhone = message.phone
                        operations_user = message.phone
                        operations_total = message.total
                        operations_skipLoyaltyTotal = message.SkipLoyaltyTotal
                        info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, 0);

                    }
                });
                GlobalURL = "{{ $getObjectUrl }}" + receivedMessage.objectId;
                oReq.open("GET", GlobalURL);
                oReq.send();
            }
        });



        function xRefURL(){
            window.open(GlobalxRefURL);
        }

        function ButtonComplete(){
            var xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                var responseTextPars = JSON.parse(this.responseText);
                var StatusCode = responseTextPars.StatusCode;
                if (StatusCode == 200) {
                    document.getElementById("success").style.display = "block";
                    document.getElementById("danger").style.display = "none";
                } else {
                    document.getElementById("success").style.display = "none";
                    document.getElementById("danger").style.display = "block";


                }
            });
            xmlHttpRequest.open("GET", "https://dev.smartuds.kz/ompletesOrder/{{$accountId}}/" + GlobalUDSOrderID);
            xmlHttpRequest.send();
        }

        function update(){
            var oReq = new XMLHttpRequest();
            document.getElementById("success").style.display = "none";
            document.getElementById("danger").style.display = "none";
            oReq.addEventListener("load", function() {
                var responseTextPars = JSON.parse(this.responseText);
                var StatusCode = responseTextPars.StatusCode;
                var message = responseTextPars.message;
                GlobalUDSOrderID = message.id;
                var BonusPoint = message.BonusPoint;
                var points = message.points;

                if (StatusCode == 200) {
                    GlobalxRefURL = "https://admin.uds.app/admin/orders?order="+message.id;
                    window.document.getElementById("OrderID").innerHTML = message.id;
                    var icon = message.icon.replace(/\\/g, '');
                    window.document.getElementById("icon").innerHTML = icon;

                    window.document.getElementById("cashBack").innerHTML = BonusPoint;
                    window.document.getElementById("points").innerHTML = points;

                    if (message.state == "NEW") {
                        document.getElementById("ButtonComplete").style.display = "block";
                        document.getElementById("Complete").style.display = "none";
                        document.getElementById("Deleted").style.display = "none";
                    }

                    if (message.state == "COMPLETED") {
                        document.getElementById("Complete").style.display = "block";
                        document.getElementById("ButtonComplete").style.display = "none";
                        document.getElementById("Deleted").style.display = "none";
                    }

                    if (message.state == "DELETED") {
                        document.getElementById("Deleted").style.display = "block";
                        document.getElementById("ButtonComplete").style.display = "none";
                        document.getElementById("Complete").style.display = "none";
                    }


                } else {

                }
            });
            GlobalURL = "{{$getObjectUrl}}" + receivedMessage.objectId;
            oReq.open("GET", GlobalURL);
            oReq.send();
        }

        function CheckPhoneOrQR(Selector){
            let option = Selector.options[Selector.selectedIndex];
            if (option.value === "0") {
                document.getElementById("sendQR").style.display = "none";
                operations_user = OLDPhone
            }
            if (option.value === "1") {
                document.getElementById("sendQR").style.display = "block";
                operations_user = OLDQRCode
            }
        }

        function onchangeQR(){
            let QRCode = parseInt(document.getElementById("QRCode").value)
            if (QRCode < 999999 && QRCode > 99999){
                document.getElementById("sendQRError").style.display = "none"
                operations_user = QRCode
                OLDQRCode = QRCode
                info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, 0);
            } else {
                document.getElementById("sendQRError").style.display = "block"
            }
        }


        function sendAccrueOrCancellation(myRadio){
            document.getElementById("sendAccrue").style.display = "none";
            document.getElementById("sendCancellation").style.display = "none";
            let div = myRadio.value;
            if (div == "sendAccrue"){
                document.getElementById("sendAccrue").style.display = "block";
            }
            if (div == "sendCancellation"){
                document.getElementById("sendCancellation").style.display = "block";
            }
        }

        function info_operations(user, total, skipTotal, point){
            let params = {
                accountId: "{{ $accountId }}",
                user: user,
                total: total,
                SkipLoyaltyTotal: skipTotal,
                points: point,
            };
            let final = url + '/CompletesOrder/operationsCalc/' + formatParams(params);
            console.log('final = ' + final)
            let xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                let r_textPars = JSON.parse(this.responseText);
                operations_cash = r_textPars.cash;
                operations_total = r_textPars.total;
                operations_skipLoyaltyTotal = r_textPars.skipLoyaltyTotal;

                let cashBack = r_textPars.cashBack;
                document.getElementById("total").innerText = operations_total
                document.getElementById("cashBackOperation").innerText = cashBack + ' Баллы'
            })
            xmlHttpRequest.open("GET", final);
            xmlHttpRequest.send();
        }

        function sendOperations(){
            let params = {
                accountId: "{{ $accountId }}",
                objectId: GlobalobjectId,
                user: operations_user,
                cashier_id: operations_cashier_id,
                cashier_name: operations_cashier_name,
                receipt_total: operations_total,
                receipt_cash: operations_cash,
                receipt_points: operations_points,
                receipt_skipLoyaltyTotal: operations_skipLoyaltyTotal,
            };

            let final = url + '/CompletesOrder/operations/' + formatParams(params);
            console.log('final = ' + final)
            let xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                let r_textPars = JSON.parse(this.responseText);
                if (r_textPars.code == 200) {
                    document.getElementById("sendWarning").style.display = "block";
                    document.getElementById("buttonOperations").style.display = "none";
                }

            })
            xmlHttpRequest.open("GET", final);
            xmlHttpRequest.send();


        }

    </script>



    <div class="main-container">
        <div id="activated" class="content bg-white text-Black rounded" style="display: none">
            <div class="row uds-gradient p-2">
                <div class="col-2">
                    <img src="https://dev.smartuds.kz/Config/UDS.png" width="35" height="35" >
                </div>
                <div class="col-10 text-white mt-1 row">
                        <div class="col-11">
                            <label onclick="xRefURL()" style="cursor: pointer">
                                Заказ № <span id="OrderID"></span> <span class="mx-1"></span>
                            </label>
                        </div>
                        <div class="col-1">
                            <i onclick="xRefURL()" class="fa-solid fa-arrow-up-right-from-square" style="cursor: pointer"></i>
                        </div>
                </div>

                <div class="row">
                    <div class="col-8 row">
                        <div class="mx-1 mt-1">
                            <button type="submit" onclick="update()" class="myButton btn "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
                        </div>
                    </div>
                    <div class="col-4 bg-light rounded-pill s-min mt-1 p-1">
                        <span class="mx-1 mt-2" id="icon"></span>
                    </div>
                </div>
            </div>

            <div class="row mt-3 s-min">
                <div class="col-1">

                </div>
                <div class="col-11 row">
                    <div id="ButtonComplete" class="row text-center" style="display: none;">
                        <div class="row mx-1">
                            <div class="col-5 mx-1 rounded-pill bg-success">
                                <button onclick="ButtonComplete()" class="btn btn-success ">Завершить </button>
                            </div>
                            <div class="col-5 mx-3 rounded-pill bg-danger">
                                <button onclick="xRefURL()" class="btn btn-danger ">Отменить </button>
                            </div>
                        </div>
                        <div id="success" class="mt-2" style="display: none">
                            <div class="row">
                                <div class="col-1"></div>
                                <div class="col-10">
                                    <div class=" alert alert-success fade show in text-center "> Заказ завершён </div>
                                </div>
                            </div>
                        </div>
                        <div id="danger" class="mt-2" style="display: none">
                            <div class="row">
                                <div class="col-1"></div>
                                <div class="col-10">
                                    <div id="error" class=" alert alert-danger alert-danger fade show in text-center ">  </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="Complete" class="row" style="display: none;">
                        <div class="row mt-2">
                            <div class="col-10">
                                Бонусов потрачено:
                            </div>
                            <div class="col-1">
                                <span class="p-1 px-3 text-white bg-primary rounded-pill" id="points"></span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-10">
                                Бонусов начислено:
                            </div>
                            <div class="col-1">
                                <span class="p-1 px-3 text-white bg-success rounded-pill" id="cashBack"></span>
                            </div>
                        </div>

                    </div>
                    <div id="Deleted" class="row" style="display: none;">
                        <div class="bg-white text-Black rounded">
                            <div class="text-center">
                                <div class="p-3 mb-2 bg-danger rounded text-white">
                                    Заказ был отменён в UDS
                                    <i class="fa-solid fa-delete-left"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div id="undefined" class="bg-white text-Black rounded content-container" style="display: none">
            <div class="row uds-gradient p-2">
                <div class="col-2">
                    <img src="https://dev.smartuds.kz/Config/UDS.png" width="35" height="35" >
                </div>
                <div class="col-10 text-white mt-1 row">
                        Провести операцию
                </div>
            </div>

            <div class="mt-2 row mx-2">
                    <div class="col-5 mt-2 mx-2"> Тип проведение </div>
                    <div class="col-6">
                        <select onchange="CheckPhoneOrQR(valueSelector)" id="valueSelector" class="p-1 form-select">
                            <option value="0" selected> по номеру телефона </option>
                            <option value="1"> по QR-коду </option>
                        </select>
                    </div>
                </div>
            <div id="sendQR" style="display: none">
                <div class="mt-2 row mx-2">
                    <small id="emailHelp" class="form-text text-muted text-center ">Введите QR-Код из приложения UDS</small>
                    <div class="col-1 mt-2 mx-2 text-danger"> </div>
                    <div class="col-9">
                        <div class="form-group">
                            <input onchange="onchangeQR()" onKeyPress="only_numbers()" type="number" class="form-control" id="QRCode" placeholder="*** ***">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-2 row mx-2">
                    <div class="row mt-2 mx-2 p-1">
                        <div class="col-6">
                            <div class="form-check">
                                <input onclick="sendAccrueOrCancellation(this)" class="form-check-input" name="eRadios" type="radio" id="Accrue" value="sendAccrue" checked>
                                <label class="form-check-label" for="exampleRadios1"> Начислить </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input onclick="sendAccrueOrCancellation(this)" class="form-check-input" name="eRadios" type="radio" id="Cancellation" value="sendCancellation">
                                <label class="form-check-label" for="exampleRadios2"> Списать</label>
                            </div>
                        </div>
                    </div>

                </div>
            <div id="sendAccrue" style="display:none;">
                    <div class="row mt-2 row mx-2" >
                        <div class="col-1"></div>
                        <div class=" col-10 my-bg-gray-2 rounded p-2 text-black ">
                            <div class="row">
                                <div class="col-8"> <span> Общая сумма к оплате  </span> </div>
                                <div class="col-4 text-end"> <span id="total"> *** </span> </div>
                                <div class="col-8"> <span> Баллы за покупку </span> </div>
                                <div class="col-4 text-end"> <span id="cashBackOperation"> *** Баллы </span> </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div id="sendCancellation" style="display:none;">
                    <div class="row mt-2 row mx-2" >
                        <div class="col-1"></div>
                        <div class=" col-10 my-bg-gray-2 rounded p-2 text-black ">
                            <div class="row">
                                <div class="col-8"> <span> Общая сумма к оплате  </span> </div>
                                <div class="col-4 text-end"> <span> *** </span> </div>
                                <div class="col-8"> <span> Доступное к списанию: </span> </div>
                                <div class="col-4 text-end"> <span> 20 </span> </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div id="sendWarning" style="display:none;">
                <div class="row mt-2 row mx-2" >
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-success fade show in text-center "> Операция прошла успешно
                        <div>Пожалуйста закройте заказ без сохранения !</div>
                    </div>
                </div>
            </div>
            <div id="sendQRError" style="display:none;">
                <div class="row mt-2 row mx-2" >
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-success fade show in text-center "> QR-код состоит из 6 цифр !</div>
                    </div>
                </div>
            </div>
            <div id="buttonOperations">
                <div class="mt-2 row mx-2">
                    <div class="col-1"></div>
                    <button onclick="sendOperations()" class="btn btn-success col-10"> Провести операцию </button>
                </div>
            </div>

        </div>
    </div>


<script>

    function formatParams(params) {
        return "?" + Object
            .keys(params)
            .map(function (key) {
                return key + "=" + encodeURIComponent(params[key])
            })
            .join("&")
    }
    function only_numbers(){
        if (event.keyCode < 48 || event.keyCode > 57)
            event.returnValue= false;
    }

</script>

<style>
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 12pt;
    }
    body {
        overflow: hidden;
    }
    .main-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }
    .content-container {
        overflow-y: auto;
        overflow-x: hidden;
        flex-grow: 1;
    }
    .buttons-container-head{
        background-color: rgba(12, 125, 112, 0.27);
        padding-top: 3px;
        min-height: 3px;
    }
    .buttons-container {
        padding-top: 10px;
        min-height: 100px;
    }

    .text-orange{
        color: orange;
    }
    .uds-gradient{
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }
    .s-min{
        font-size: 10pt;
    }
    .s-min-8{
        font-size: 8px;
    }

    .myPM{
        padding-left: 4px !important;
        margin: 2px !important;
        margin-right: 11px !important;
    }

    .myButton {
        box-shadow: 0px 4px 5px 0px #5d5d5d !important;
        background-color: #00a6ff !important;
        color: white !important;
        border-radius:50px !important;
        display:inline-block !important;
        cursor:pointer !important;
        padding:5px 5px !important;
        text-decoration:none !important;
    }
    .myButton:hover {
        background-color: #fffdfd !important;
        color: #111111 !important;
    }
    .myButton:active {
        position: relative !important;
        top: 1px !important;
    }

    .myButton {
        box-shadow: 0px 4px 5px 0px #5d5d5d !important;
        background-color: #00a6ff !important;
        color: white !important;
        border-radius:50px !important;
        display:inline-block !important;
        cursor:pointer !important;
        padding:5px 5px !important;
        text-decoration:none !important;
    }
    .myButton:hover {
        background-color: #fffdfd !important;
        color: #111111 !important;
    }
    .myButton:active {
        position: relative !important;
        top: 1px !important;
    }
    .my-bg-gray{
        background-color: #ebefff !important;
        color: #3b3c65;
        border-radius: 14px !important;
        overflow: hidden !important;
    }

    .my-bg-gray-2{
        background-color: #e8e8e8 !important;
        color: #3b3c65;
        border-radius: 14px !important;
        overflow: hidden !important;
    }

    .my-bg-success{
        border-radius: 14px !important;
        overflow: hidden !important;
    }

</style>
</body>
</html>



