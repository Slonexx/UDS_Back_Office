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

        let tmp_operations_style
        let operations_total
        let operations_cash
        let operations_points
        let operations_Max_points
        let operations_availablePoints
        let operations_skipLoyaltyTotal
        let operations_user
        let operations_cashier_id = "{{ $cashier_id }}"
        let operations_cashier_name = "{{ $cashier_name }}"

        let operationsAccrue
        let operationsCancellation

        window.addEventListener("message", function(event) {
            let receivedMessage = event.data;
            GlobalobjectId = receivedMessage.objectId;
            if (receivedMessage.name === 'Open') {
                let oReq = new XMLHttpRequest()
                clearWidget()
                oReq.addEventListener("load", function() {
                    let responseTextPars = JSON.parse(this.responseText);
                    let StatusCode = responseTextPars.StatusCode;
                    let message = responseTextPars.message;
                    if (StatusCode == 402){
                        document.getElementById("Error402").style.display = "block"
                        document.getElementById("ErrorMessage").innerText = responseTextPars.message
                    } else {
                        if (StatusCode == 200) {
                            document.getElementById("activated").style.display = "block";
                            document.getElementById("undefined").style.display = "none";
                            GlobalUDSOrderID = message.id;
                            let BonusPoint = message.BonusPoint;
                            let points = message.points;
                            if (message.info == 'Order') {
                                document.getElementById("infoOrderOrOperations").innerText = 'Заказ №';
                                GlobalxRefURL = "https://admin.uds.app/admin/orders?order="+message.id;
                            }
                            if (message.info == 'Operations'){
                                document.getElementById("infoOrderOrOperations").innerText = 'Операция №';
                                GlobalxRefURL = "https://admin.uds.app/admin/operations"
                            }
                            window.document.getElementById("OrderID").innerHTML = message.id;
                            let icon = message.icon.replace(/\\/g, '');
                            window.document.getElementById("icon").innerHTML = icon;
                            window.document.getElementById("cashBack").innerHTML = BonusPoint;
                            window.document.getElementById("points").innerHTML = points;
                            setStateByStatus(message.state)
                        } else {
                            document.getElementById("activated").style.display = "none"
                            document.getElementById("sendWarning").style.display = "none"
                            document.getElementById("undefined").style.display = "block"
                            document.getElementById("buttonOperations").style.display = "block"
                            document.getElementById("labelAccrue").style.display = "block"
                            document.getElementById("labelCancellation").style.display = "block"
                            operationsAccrue = message.operationsAccrue
                            operationsCancellation = message.operationsCancellation

                            OLDPhone = message.phone
                            operations_user = message.phone
                            operations_total = message.total
                            operations_availablePoints = message.availablePoints
                            operations_skipLoyaltyTotal = message.SkipLoyaltyTotal

                            sendAccrueOrCancellation(window.document.getElementById("Accrue"))
                            console.log("message = " + JSON.stringify(message))

                           /* if (EnableOffs == true){
                                document.getElementById("labelCancellation").style.display = "block"
                            }*/


                            /*if (tmp_operations_style == true) {
                                document.getElementById("valueSelector").value = 1;
                                CheckPhoneOrQR(document.getElementById("valueSelector"))
                            } else {
                                document.getElementById("valueSelector").value = 0;
                                CheckPhoneOrQR(document.getElementById("valueSelector"))
                                info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, 0, operations_availablePoints);
                            }*/
                        }
                    }
                });
                GlobalURL = "{{ $getObjectUrl }}" + receivedMessage.objectId;
                console.log('GlobalURL = ' + GlobalURL)
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

        function CheckPhoneOrQR(Selector){
            let option = Selector.options[Selector.selectedIndex];
            document.getElementById("sendAccrue").style.display = "none"
            document.getElementById('buttonOperations').style.display = 'none'
            document.getElementById("labelCancellation").style.display = "none"
            if (option.value === "0") {
                document.getElementById("sendQR").style.display = "none"
                document.getElementById("labelAccrue").style.display = "block"
                document.getElementById("labelCancellation").style.display = "none"
                document.getElementById("QRCodePoint").value = ""
                operations_points = 0
                operations_user = OLDPhone

                document.getElementById("Accrue").checked = true
                if (operationsCancellation === 0){
                    document.getElementById("labelCancellation").style.display = "block"
                }

                info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, 0, operations_availablePoints)
            }
            if (option.value === "1") {
                document.getElementById("sendQR").style.display = "block"
                document.getElementById("QRCode").value = ''
                document.getElementById("labelAccrue").style.display = "block"
                document.getElementById("labelCancellation").style.display = "block"
                operations_user = OLDQRCode
            }
        }

        function onchangeQR(){
            let QRCode = (document.getElementById("QRCode").value)
            if (QRCode.length == 6){
                document.getElementById("sendQRError").style.display = "none"
                document.getElementById("sendCancellation").style.display = "block"
                document.getElementById("sendAccrue").style.display = "block";
                operations_user = QRCode
                OLDQRCode = QRCode

                let params = {
                    accountId: "{{ $accountId }}",
                    code:OLDQRCode,
                };
                let final = url + 'customers/find'+ formatParams(params);
                console.log('customers/find final = ' + final)
                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function() {
                    let r_textPars = JSON.parse(this.responseText);
                    operations_availablePoints = r_textPars.availablePoints
                    document.getElementById("availablePoints").innerText = r_textPars.availablePoints
                    info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, 0, operations_availablePoints);
                    if (r_textPars.id == 0) {
                        document.getElementById("sendQRErrorID").style.display = "block";
                    } else  {
                        document.getElementById("sendQRErrorID").style.display = "none";
                    }
                })
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();
            } else {
                document.getElementById("sendQRError").style.display = "block"
                document.getElementById("sendCancellation").style.display = "none"
                document.getElementById("sendAccrue").style.display = "none";
            }
        }

        function onchangePoint(){
            let QRCodePoint = window.document.getElementById('QRCodePoint');
            operations_points = QRCodePoint.value
        }

        function sendAccrueOrCancellation(myRadio){
            document.getElementById("sendCancellation").style.display = "none";
            document.getElementById("sendPoint").style.display = "none";
            let div = myRadio.value;
            console.log('div = ' + div)
            console.log('operationsAccrue = ' + operationsAccrue)
            console.log('operationsCancellation = ' + operationsCancellation)
            if (div === "sendAccrue"){

                if (operationsAccrue === 1) {
                    document.getElementById("valueSelector").value = "1"
                    CheckPhoneOrQR(document.getElementById("valueSelector"))
                } else {
                    document.getElementById("valueSelector").value = "0"
                    CheckPhoneOrQR(document.getElementById("valueSelector"))
                }


            }
            if (div === "sendCancellation"){
                document.getElementById("sendCancellation").style.display = "block";
                if (operations_user != undefined && OLDQRCode != undefined){
                    document.getElementById("sendPoint").style.display = "block";
                }
                if (operations_user != undefined && EnableOffs == true){
                    document.getElementById("sendPoint").style.display = "block";
                }

                if (operationsCancellation === 1) {
                    document.getElementById("valueSelector").value = "1"
                    CheckPhoneOrQR(document.getElementById("valueSelector"))
                } else {
                    document.getElementById("valueSelector").value = "0"
                    CheckPhoneOrQR(document.getElementById("valueSelector"))
                }

            }
        }

        function info_operations(user, total, skipTotal, point, availablePoints){
            let params = {
                accountId: "{{ $accountId }}",
                user: user,
                total: total,
                SkipLoyaltyTotal: skipTotal,
                points: point,
            };
            let final = url + '/CompletesOrder/operationsCalc/' + formatParams(params);
            console.log('info_operations final = ' + final)
            let xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                let r_textPars = JSON.parse(this.responseText);
                if (typeof r_textPars.Status !== 'undefined'){
                } else {
                    document.getElementById("sendQRErrorID").style.display = "none";
                    operations_cash = r_textPars.cash;
                    operations_total = r_textPars.total;
                    operations_skipLoyaltyTotal = r_textPars.skipLoyaltyTotal;

                    let cashBack = r_textPars.cashBack;
                    document.getElementById("total").innerText = operations_total
                    document.getElementById("cashBackOperation").innerText = cashBack
                    document.getElementById("availablePoints").innerText = operations_availablePoints
                    operations_Max_points = r_textPars.maxPoints
                    PointMax(r_textPars.maxPoints)
                    document.getElementById("sendAccrue").style.display = "block";
                    document.getElementById('buttonOperations').style.display = 'block'
                }

            })
            xmlHttpRequest.open("GET", final);
            xmlHttpRequest.send();
        }

        function sendOperations(){
            if (parseFloat(operations_points) > 0) {
                operations_cash = operations_cash - operations_points
            }
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
            console.log('sendOperations final = ' + final)
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


        function clearWidget(){
            document.getElementById("activated").style.display = "none";
            document.getElementById("undefined").style.display = "none";
            document.getElementById("Error402").style.display = "none"
            document.getElementById("success").style.display = "none";
            document.getElementById("danger").style.display = "none";
            document.getElementById("sendWarning").style.display = "none";
            document.getElementById("buttonOperations").style.display = "none";
            document.getElementById("labelAccrue").style.display = "none";
            document.getElementById("labelCancellation").style.display = "none";
            document.getElementById("Accrue").checked = true;
            document.getElementById("valueSelector").value = "0"
            /*CheckPhoneOrQR(document.getElementById("valueSelector"))*/
            document.getElementById("Error402").style.display = "none"
            document.getElementById("sendQRErrorID").style.display = "none"
            document.getElementById("operations_style").style.display = "none"
        }
        function setStateByStatus(State){
            if (State == "NEW") {
                document.getElementById("ButtonComplete").style.display = "block";
                document.getElementById("Complete").style.display = "none";
                document.getElementById("Deleted").style.display = "none";
            }
            if (State == "COMPLETED") {
                document.getElementById("Complete").style.display = "block";
                document.getElementById("ButtonComplete").style.display = "none";
                document.getElementById("Deleted").style.display = "none";
            }
            if (State == "DELETED") {
                document.getElementById("Deleted").style.display = "block";
                document.getElementById("Complete").style.display = "none";
                document.getElementById("Complete").style.display = "none";
            }
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
                            <label onclick="xRefURL()" style="cursor: pointer"> <span id="infoOrderOrOperations"> </span>
                                <span id="OrderID"></span> <span class="mx-1"></span>
                            </label>
                        </div>
                        <div class="col-1">
                            <i onclick="xRefURL()" class="fa-solid fa-arrow-up-right-from-square" style="cursor: pointer"></i>
                        </div>
                </div>


            </div>
            <div class="row">
                <div class="col-7 row">
                    <div class="mx-1 mt-1">
                        {{--<button type="submit" onclick="update()" class="myButton btn "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>--}}
                    </div>
                </div>
                <div class="col-5 bg-light rounded-pill s-min mt-1 p-1">
                    <span class="mx-1 mt-2" id="icon"></span>
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

            <div id="operations_style" class="mt-2 row mx-2" style="font-size: 16px; display: none">
                    <div class="col-5 mt-2 mx-2"> Тип проведения </div>
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
                            <input onchange="onchangeQR()" onKeyPress="only_numbers()" type="text" class="form-control" id="QRCode" placeholder="*** ***">
                        </div>
                    </div>
                </div>
            </div>
            <div id="sendQRError" style="display:none;">
                <div class="row mt-2 row mx-2" >
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-danger fade show in text-center "> QR-код состоит из 6 цифр !</div>
                </div>
            </div>
            <div id="sendQRErrorID" style="display:none;">
                <div class="row mt-2 row mx-2" >
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-danger fade show in text-center "> QR-код не верный !</div>
                </div>
            </div>
            <div class="mt-2 row mx-2">
                    <div class="row mt-2 mx-2 p-1">
                        <div id="labelAccrue" class="col-6">
                            <div class="mx-3 form-check">
                                <input onclick="sendAccrueOrCancellation(this)" class="form-check-input" name="eRadios" type="radio" id="Accrue" value="sendAccrue" checked>
                                <label class="form-check-label" for="Accrue"> Начислить </label>
                            </div>
                        </div>
                        <div id="labelCancellation" class="col-6">
                            <div class="mx-3  form-check">
                                <input onclick="sendAccrueOrCancellation(this)" class="form-check-input" name="eRadios" type="radio" id="Cancellation" value="sendCancellation"
                                <label class="form-check-label" for="Cancellation"> Списать</label>
                            </div>
                        </div>
                    </div>
                </div>
            <div id="sendAccrue" style="display:none;">
                    <div class="row mt-2 row mx-2" >
                        <div class="col-1"></div>
                        <div class=" col-10 border border-info rounded p-2 text-black ">
                            <div class="row">
                                <div class="col-8"> <span> Общая сумма к оплате  </span> </div>
                                <div class="col-4 text-end"> <span id="total"> *** </span> </div>
                                <div class="col-8"> <span> Баллы за покупку </span> </div>
                                <div class="col-4 text-end"> <span id="cashBackOperation"> *** Баллы </span> </div>
                                <div id="sendCancellation">
                                    <div class="row">
                                        <div class="col-8"> <span> Доступное баллов: </span> </div>
                                        <div class="col-4 text-end"> <span id="availablePoints"> *** </span> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div id="sendWarning" style="display:none;">
                <div class="row mt-2 row mx-2" >
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-success fade show in text-center "> Операция прошла успешно
                        <div> <b> Пожалуйста закройте заказ без сохранения ! </b> </div>
                    </div>
                </div>
            </div>

            <div id="sendPoint" class="mt-2" style="display: none">
                <div class="row">
                    <div class="col-1"></div>
                    <div class="col-10">
                        <div class="input-group">
                            <input type="text" class="form-control" id="QRCodePoint" placeholder="*** ***"
                                   onchange="onchangePoint()" onKeyPress="only_float()" aria-label="Dollar amount (with dot and two decimal places)">
                            <div class="input-group-append">
                                <span id="maxPoint" class="input-group-text">0.00</span>
                            </div>
                        </div>
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

        <div id="Error402" class="content bg-white text-Black rounded" style="display: none">
            <div class="row uds-gradient p-2">
                <div class="col-2">
                    <img src="https://dev.smartuds.kz/Config/UDS.png" width="35" height="35" >
                </div>
                <div class="col-10 text-white mt-1 row">
                    Ошибка
                </div>
            </div>
            <div class="row">
                <div class="row mt-2 row mx-2" >
                    <div class="col-1"></div>
                    <div id="ErrorMessage" class="col-10 alert alert-danger fade show in text-center "> </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.getElementById("QRCode").addEventListener("change", function() {
        let Selector = document.getElementById('valueSelector')
        let option = Selector.options[Selector.selectedIndex];
        if (option === '1') {
            document.getElementById('buttonOperations').style.display = 'block'
        }
    });

    document.getElementById("QRCodePoint").addEventListener("change", function() {
        let v = parseInt(this.value);
        if (v < 1) this.value = 1;
        if (v > operations_Max_points) this.value = operations_Max_points;
        operations_points = this.value
    });

    function PointMax(max){
        let idPoint = window.document.getElementById('maxPoint');
        idPoint.innerText = max.toString();
    }
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
    function only_float(){
        if (event.keyCode < 48 || event.keyCode > 57){
            if ( event.keyCode === 46) event.returnValue = true; else  event.returnValue = false;
        }
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



