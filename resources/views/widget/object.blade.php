@php use App\Http\Controllers\mainURL; @endphp
@extends('widget.widget')
@section('content')
    <div class="main-container">
        <div id="activated" class="content bg-white text-Black rounded" style="display: none">
            <div class="row uds-gradient p-2">
                <div class="col-2">
                    <img src="https://smartuds.kz/Config/UDS.png" width="35" height="35" alt="">
                </div>
                <div class="col-10 text-white mt-1 row">
                    <div class="col-11">
                        <label onclick="xRefURL()" style="cursor: pointer"> <span id="infoOrderOrOperations"> </span>
                            <span id="OrderID"></span> <span class="mx-1"></span>
                        </label>
                    </div>
                    <div class="col-1">
                        <i onclick="xRefURL()" class="fa-solid fa-arrow-up-right-from-square"
                           style="cursor: pointer"></i>
                    </div>
                </div>


            </div>
            <div class="row">
                <div class="col-7 row">
                    <div class="mx-1 mt-1"></div>
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
                                <button onclick="ButtonComplete()" class="btn btn-success ">Завершить</button>
                            </div>
                            <div class="col-5 mx-3 rounded-pill bg-danger">
                                <button onclick="xRefURL()" class="btn btn-danger ">Отменить</button>
                            </div>
                        </div>
                        <div id="success" class="mt-2" style="display: none">
                            <div class="row">
                                <div class="col-1"></div>
                                <div class="col-10">
                                    <div class=" alert alert-success fade show in text-center "> Заказ завершён</div>
                                </div>
                            </div>
                        </div>
                        <div id="danger" class="mt-2" style="display: none">
                            <div class="row">
                                <div class="col-1"></div>
                                <div class="col-10">
                                    <div id="error"
                                         class=" alert alert-danger alert-danger fade show in text-center "></div>
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
                    <img src="https://smartuds.kz/Config/UDS.png" width="35" height="35" alt="">
                </div>
                <div class="col-10 text-white mt-1 row">
                    Провести операцию
                </div>
            </div>
            <div id="errorMessage" class="p-2 bg-danger text-white" style="display: none"> </div>
            <div class="mt-2 row mx-2">
                <div class="row mt-2 mx-2 p-1">
                    <div id="labelAccrue" class="col-6">
                        <div class="mx-3 form-check">
                            <input onclick="sendAccrueOrCancellation(this)" class="form-check-input" name="eRadios"
                                   type="radio" id="Accrue" value="sendAccrue" checked>
                            <label class="form-check-label" for="Accrue"> Начислить </label>
                        </div>
                    </div>
                    <div id="labelCancellation" class="col-6">
                        <div class="mx-3  form-check">
                            <input onclick="sendAccrueOrCancellation(this)" class="form-check-input" name="eRadios"
                                   type="radio" id="Cancellation" value="sendCancellation">
                            <label class="form-check-label" for="Cancellation"> Списать</label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="sendQR" style="display: none">
                <div class="mt-2 row mx-2">
                    <small id="emailHelp" class="form-text text-muted text-center ">Введите QR-Код из приложения
                        UDS</small>
                    <div class="col-1 mt-2 mx-2 text-danger"></div>
                    <div class="col-9">
                        <div class="form-group">
                            <input onchange="onchangeQR()" onKeyPress="only_numbers()" type="text" class="form-control"
                                   id="QRCode" placeholder="*** ***">
                        </div>
                    </div>
                </div>
            </div>
            <div id="sendQRError" style="display:none;">
                <div class="row mt-2 row mx-2">
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-danger fade show in text-center "> QR-код состоит из 6 цифр !</div>
                </div>
            </div>
            <div id="sendQRErrorID" style="display:none;">
                <div class="row mt-2 row mx-2">
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-danger fade show in text-center "> QR-код не верный !</div>
                </div>
            </div>
            <div id="sendAccrue" style="display:none;">
                <div class="row mt-2 row mx-2">
                    <div class="col-1"></div>
                    <div class=" col-10 border border-info rounded p-2 text-black ">
                        <div class="row">
                            <div class="col-8"><span> Общая сумма к оплате  </span></div>
                            <div class="col-4 text-end"><span id="total"> *** </span></div>
                            <div class="col-8"><span> Баллы за покупку </span></div>
                            <div class="col-4 text-end"><span id="cashBackOperation"> *** Баллы </span></div>
                            <div id="sendCancellation">
                                <div class="row">
                                    <div class="col-8"><span> Доступное баллов: </span></div>
                                    <div class="col-4 text-end"><span id="availablePoints"> *** </span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="sendWarning" style="display:none;">
                <div class="row mt-2 row mx-2">
                    <div class="col-1"></div>
                    <div class="col-10 alert alert-success fade show in text-center "> Операция прошла успешно
                        <div><b> Пожалуйста закройте заказ без сохранения ! </b></div>
                    </div>
                </div>
            </div>

            <div id="sendPoint" class="mt-2" style="display: none">
                <div class="row">
                    <div class="col-1"></div>
                    <div class="col-10">
                        <div class="input-group">
                            <input type="text" class="form-control" id="QRCodePoint" placeholder="*** ***"
                                   onchange="onchangePoint()" onKeyPress="only_float()"
                                   aria-label="Dollar amount (with dot and two decimal places)">
                            <div class="input-group-append">
                                <span id="maxPoint" class="input-group-text">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="buttonOperations" style="display: none">
                <div class="mt-2 row mx-2">
                    <div class="col-1"></div>
                    <button onclick="sendOperations()" class="btn btn-success col-10"> Провести операцию</button>
                </div>
            </div>
            <div id="outLoud" class="text-center" style="display: none">
                <div class="row mt-2 row mx-2">
                    <div class="col-1"></div>
                    <div id="outLoud_message" class="col-10 alert alert-danger fade show in text-center "> Отправка
                    </div>
                </div>
            </div>
        </div>

        <div id="Error402" class="content bg-white text-Black rounded" style="display: none">
            <div class="row uds-gradient p-2">
                <div class="col-2">
                    <img src="https://smartuds.kz/Config/UDS.png" width="35" height="35" alt="">
                </div>
                <div class="col-10 text-white mt-1 row">
                    Ошибка
                </div>
            </div>
            <div class="row">
                <div class="row mt-2 row mx-2">
                    <div class="col-1"></div>
                    <div id="ErrorMessage" class="col-10 alert alert-danger fade show in text-center "></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const url = "{{ app(mainURL::class)->me_url_host() }}"
        //onst url = "https://uds/"

        let accountId = "{{ $accountId }}"

        let extensionPoint
        let GlobalobjectId

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
        let operations_availablePoints_Nubmer
        let operations_skipLoyaltyTotal
        let operations_user
        let operations_user_uid
        let cashBack
        let operations_cashier_id = "{{ $cashier_id }}"
        let operations_cashier_name = "{{ $cashier_name }}"

        let operationsAccrue
        let operationsCancellation

        /*let receivedMessage = {
            "name": "Open",
            "extensionPoint": "document.customerorder.edit",
            "objectId": "4e1cd8b0-5181-11ee-0a80-09a40013d917",
            "messageId": 1,
            "displayMode": "expanded"
        }*/


        window.addEventListener("message", function (event) {
            let receivedMessage = event.data

            clearWidget()
            GlobalobjectId = receivedMessage.objectId;

            if (receivedMessage.name === 'Open') {

                let settings = ajax_settings(url + 'CustomerOrder/EditObject/' + accountId + '/' + set_extensionPoint(receivedMessage.extensionPoint) + '/' + receivedMessage.objectId, "GET", null)
                console.log('initial request settings  ↓ ')
                console.log(settings)

                //receivedMessage = null;

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


        function ButtonComplete() {
            let settings = ajax_settings(url + "CompletesOrder/{{$accountId}}/" + GlobalUDSOrderID, "GET", null)
            console.log('Button Complete request settings  ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (response) {
                console.log('Button Complete request response  ↓ ')
                console.log(response)
                if (response.StatusCode == 200) {
                    document.getElementById("success").style.display = "block";
                    document.getElementById("danger").style.display = "none";
                } else {
                    document.getElementById("success").style.display = "none";
                    document.getElementById("danger").style.display = "block";
                }
            });
        }

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

        function onchangeQR() {
            let QRCode = document.getElementById("QRCode").value
            if (QRCode.length == 6) {
                document.getElementById("sendQRError").style.display = "none"
                document.getElementById("sendCancellation").style.display = "block"
                document.getElementById("buttonOperations").style.display = "block"
                document.getElementById("sendAccrue").style.display = "block";

                operations_user = QRCode
                OLDQRCode = QRCode

                let data = {
                    accountId: accountId,
                    code: OLDQRCode,
                };

                let settings = ajax_settings(url + 'customers/find', "GET", data)
                console.log('QR-CODE request settings  ↓ ')
                console.log(settings)
                $.ajax(settings).done(function (response) {
                    console.log('QR-CODE request response  ↓ ')
                    console.log(response)

                    operations_availablePoints = response.availablePoints
                    document.getElementById("availablePoints").innerText = response.availablePoints
                    info_operations(operations_user, operations_total, operations_skipLoyaltyTotal, 0, response.availablePoints);
                    if (response.id == 0) {
                        document.getElementById("sendQRErrorID").style.display = "block";
                    } else {
                        document.getElementById("sendQRErrorID").style.display = "none";
                    }

                })
            } else {
                document.getElementById("sendQRError").style.display = "block"
                document.getElementById("sendPoint").style.display = "none"
                document.getElementById("sendCancellation").style.display = "none"
                document.getElementById("buttonOperations").style.display = "none"
                document.getElementById("sendAccrue").style.display = "none";
            }
        }

        function onchangePoint() {
            let QRCodePoint = window.document.getElementById('QRCodePoint');
            operations_points = QRCodePoint.value
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

        function info_operations(user, total, skipTotal, point, availablePoints) {
            let data = {
                accountId: accountId,
                entity_type: extensionPoint,
                object_Id: GlobalobjectId,
                user: user,
                total: total,
                SkipLoyaltyTotal: skipTotal,
                points: point,
            };

            let settings = ajax_settings(url + 'CompletesOrder/operationsCalc', "GET", data);
            console.log('info operations request settings  ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (response) {
                console.log('info operations request response  ↓ ')
                console.log(response)
                if (typeof response.Status != 'undefined') {
                    document.getElementById("undefined").style.display = "none"
                    document.getElementById("Error402").style.display = "block"
                    document.getElementById("ErrorMessage").innerText = response.Message
                } else {
                    document.getElementById("sendQRErrorID").style.display = "none";
                    operations_cash = response.cash;
                    operations_total = response.total;
                    operations_skipLoyaltyTotal = response.skipLoyaltyTotal;

                    cashBack = response.cashBack;
                    document.getElementById("total").innerText = operations_total
                    document.getElementById("cashBackOperation").innerText = cashBack
                    document.getElementById("availablePoints").innerText = availablePoints


                    operations_Max_points = response.maxPoints
                    PointMax(response.maxPoints)
                    document.getElementById("sendAccrue").style.display = "block";
                    document.getElementById('buttonOperations').style.display = 'block'
                }
            })
        }

        function sendOperations() {
            window.document.getElementById('buttonOperations').style.display = 'none'
            window.document.getElementById('outLoud').style.display = 'block'

            if (parseFloat(operations_points) > 0) {
                operations_cash = operations_cash - operations_points
            }
            let data = {
                accountId: accountId,
                objectId: GlobalobjectId,
                entity: extensionPoint,
                user: operations_user,
                user_uid: operations_user_uid,
                cashier_id: operations_cashier_id,
                cashier_name: operations_cashier_name,
                receipt_total: operations_total,
                receipt_cash: operations_cash,
                receipt_points: operations_points,
                receipt_skipLoyaltyTotal: operations_skipLoyaltyTotal,
                cashBack: cashBack,
            };

            let settings = ajax_settings(url + 'CompletesOrder/operations/', "GET", data);
            console.log('send operations parameters  ↓ ')
            console.log(settings)

            $.ajax(settings).done(function (response) {
                console.log('send operations response  ↓ ')
                console.log(response)

                if (response.status) {
                    document.getElementById("sendWarning").style.display = "block";
                    document.getElementById("buttonOperations").style.display = "none";
                } else {
                    window.document.getElementById('buttonOperations').style.display = 'block'
                    window.document.getElementById('errorMessage').style.display = 'block'
                    window.document.getElementById('errorMessage').innerText = response.message
                }
                window.document.getElementById('outLoud').style.display = 'none'
            })

        }


        function setStateByStatus(State) {
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

        function ajax_settings(url, method, data) {
            return {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }

    </script>

    <script>

        document.getElementById("QRCodePoint").addEventListener("change", function () {
            let v = parseInt(this.value);
            if (v < 1) this.value = 1;
            if (v > operations_Max_points) this.value = operations_Max_points;
            operations_points = this.value
        });

        function clearWidget() {
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
            document.getElementById("Error402").style.display = "none"
            document.getElementById("sendQRErrorID").style.display = "none"
            document.getElementById("errorMessage").style.display = "none"
        }

        function PointMax(max) {
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


        function xRefURL() {
            window.open(GlobalxRefURL);
        }

        function only_numbers() {
            if (event.keyCode < 48 || event.keyCode > 57)
                event.returnValue = false;
        }

        function only_float() {
            if (event.keyCode < 48 || event.keyCode > 57) {
                event.returnValue = event.keyCode === 46;
            }
        }


        function set_extensionPoint(params) {
            let result
            switch (params) {
                case "document.customerorder.edit": {
                    result = "customerorder"
                    extensionPoint = "customerorder"
                    break
                }

                case "document.demand.edit": {
                    result = "demand"
                    extensionPoint = "demand"
                    break
                }

                case "document.salesreturn.edit": {
                    result = "salesreturn"
                    extensionPoint = "salesreturn"
                    break
                }
            }
            return result
        }

    </script>

@endsection
