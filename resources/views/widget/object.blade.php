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
                            <input type="text" class="form-control" id="QRCodePoint" placeholder="*** ***" onchange="onchangePoint()" onKeyPress="only_float()">
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


    @include('widget.main.parameters')
    @include('widget.main.clear')
    @include('widget.main.lite_script')
    @include('widget.main.set_is')
    @include('widget.main.set_is_input')
    @include('widget.main.isOrder')
    @include('widget.main.isBonusSystem')


    <script>

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

            let settings = ajax_settings(url + 'Completes/Order/operationsCalc', "GET", data);
            $.ajax(settings).done(function (response) {
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
                    let formattedCashBack = cashBack.toFixed(1).replace(/\d(?=(\d{3})+\.)/g, '$& ');
                    let formattedOperations_total = operations_total.toFixed(1).replace(/\d(?=(\d{3})+\.)/g, '$& ');
                    let formattedAvailablePoints = availablePoints.toFixed(1).replace(/\d(?=(\d{3})+\.)/g, '$& ');


                    document.getElementById("total").innerText = formattedOperations_total

                    document.getElementById("cashBackOperation").innerText = formattedCashBack;
                    document.getElementById("availablePoints").innerText = formattedAvailablePoints


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

            if (parseFloat(operations_points) > 0) operations_cash = operations_cash - operations_points

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

            let settings = ajax_settings(url + 'Completes/Order/operations/', "GET", data);
            $.ajax(settings).done(function (response) {
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




    </script>
    @include('widget.main.loading_widget')
@endsection
