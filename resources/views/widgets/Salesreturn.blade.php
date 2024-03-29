<!doctype html>
<html lang="en">
@include('head')

<body>

<script>
    const url = 'https://smartuds.kz/'
    //const url = 'https://uds/'
    let GlobalobjectId
    let GlobalURL

    let return_id
    let return_points
    let return_cash
    let return_total
    let setTotal
    let setPoints

    let accountId = "{{ $accountId }}"


    /*let receivedMessage = {
        "name": "Open",
        "extensionPoint": "document.salesreturn.edit",
        "objectId": "341c1ece-b48b-11ee-0a80-119800000503",
        //"objectId": "2c9cde4b-b42c-11ee-0a80-16d900044b0e",
        "messageId": 1,
        "displayMode": "expanded"
    }*/


    window.addEventListener("message", function (event) {
        let receivedMessage = event.data
        GlobalobjectId = receivedMessage.objectId
        if (receivedMessage.name === 'Open') {

            main.style.display = 'none'
            sendError.style.display = 'none'
            sendWarning.style.display = 'none'
            DontExternal.style.display = 'none'
            Private_return.style.display = 'none'
            Private_return_full.style.display = 'none'
            buttonOperations.style.display = 'none'

            let settings = ajax_settings(url + 'CustomerOrder/EditObject/' + accountId + '/salesreturn/' + GlobalobjectId, "GET", null)

            $.ajax(settings).done(function (json) {
                console.log(json)

                if (json.StatusCode === 'error') {
                    window.document.getElementById('DontExternal').style.display = "block"
                    window.document.getElementById('DontExternalMessage').style.display = JSON.stringify(json.message)
                } else {
                    window.document.getElementById('main').style.display = "block"
                    window.document.getElementById('Private_return_full').style.display = "block"

                    if (json.message.id == undefined || json.message.id == null) {
                        window.document.getElementById('main').style.display = "none"
                        window.document.getElementById('Private_return_full').style.display = "none"
                        window.document.getElementById('DontExternal').style.display = "block"
                        window.document.getElementById('DontExternalMessage').style.display = JSON.stringify('У данного документа нету связных документов отгрузки')
                    } else {
                        setDataParameters(json.message)
                    }

                }
            })
        }
    });


    function sendOperations() {

        let params = {
            accountId: accountId,
            objectId: GlobalobjectId,
            return_id: return_id,
            partialAmount: setTotal,
        };

        let settings = ajax_settings(url + 'postSalesreturn/operations/', "GET", params)
        $.ajax(settings).done(function (r_textPars) {
            if (r_textPars.status) {
                window.document.getElementById('sendWarning').style.display = "block"
            } else {
                window.document.getElementById('sendError').style.display = "block"
                window.document.getElementById('ErrorMessage').innerText = r_textPars.Data
            }
        })
    }

</script>


<div class="main-container">
    <div class="bg-white text-Black rounded content-container">
        <div class="row uds-gradient p-2">
            <div class="col-2">
                <img src="https://smartuds.kz/Config/UDS.png" width="35" height="35">
            </div>
            <div class="col-10 text-white mt-1 row">
                Возврат по операции
            </div>
        </div>
        <div id="DontExternal" class="row" style="display:none;">
            <div class="row mt-2 row mx-2">
                <div class="col-1"></div>
                <div id="DontExternalMessage" class="col-10 alert alert-danger fade show in text-center "> Нету связных документов
                </div>
            </div>
        </div>
        <div id="main" style="display:none;">
            <div class="mt-2 row mx-2">
                <div class="col-1"></div>
                <div class="col-10 border border-info rounded p-2 text-black ">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div id="Private_return_full" class="row" style="display: none">
                                <div class="col-1"></div>
                                <button onclick="btnPrivateReturn(0)" class="col-10 mx-4 btn btn-outline-secondary">
                                    Частичный возврат
                                </button>
                            </div>
                            <div id="Private_return" class="row" style="display: none">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-8">
                                            <input id="ReturnPointTotal" type="text" class="form-control" placeholder="Сумма к возврату" onchange="onchangePoint()" onKeyPress="only_float()">
                                        </div>
                                        <div class="col-4">
                                            <button onclick="btnPrivateReturn(1)" class="btn btn-outline-secondary">
                                                Отмена
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-8"><span> Сумма  </span></div>
                        <div class="col-4 text-end"><span id="refund_total_dont_point"> *** </span></div>
                        <div class="col-8"><span> Баллы  </span></div>
                        <div class="col-4 text-end"><span id="point"> *** </span></div>
                        <hr class="col-11 mx-3 text-info">
                        <div class="col-8">Итого к возврату</div>
                        <div class="col-4 text-end"><span id="refund_total"> *** </span></div>

                    </div>
                </div>
            </div>
        </div>
        <div id="sendWarning" style="display:none;">
            <div class="row mt-2 row mx-2">
                <div class="col-1"></div>
                <div class="col-10 alert alert-success fade show in text-center "> Операция прошла успешно
                    <div><b> Пожалуйста закройте документ без сохранения ! </b></div>
                </div>
            </div>
        </div>
        <div id="sendError" style="display:none;">
            <div class="row mt-2 row mx-2">
                <div class="col-1"></div>
                <div id="ErrorMessage" class="col-10 alert alert-danger fade show in text-center "></div>
            </div>
        </div>
        <div id="buttonOperations">
            <div class="mt-2 row mx-2">
                <div class="col-1"></div>
                <button onclick="sendOperations()" class="btn btn-danger col-10"> Сделать возврат</button>
            </div>
        </div>
    </div>
</div>

<script>

    document.getElementById("ReturnPointTotal").addEventListener("change", function () {
        let v = parseInt(this.value);
        if (v <= 0) this.value = 0;
        if (v >= return_total) this.value = return_total;
        setTotal = this.value
        onchangePoint();
    });


    function setDataParameters(Data) {
        return_id = Data.id
        return_points = Data.points
        return_cash = Data.cash
        return_total = Data.total
        setTotal = return_total
        setPoints = return_points
        setInnerText_Point_and_Total(setTotal, setPoints);
    }

    function setInnerText_Point_and_Total(Total, Points) {
        if (return_total === 0) {
            window.document.getElementById('buttonOperations').style.display = "none"
        } else {
            window.document.getElementById('buttonOperations').style.display = "block"
        }
        window.document.getElementById('refund_total').innerText = Total
        window.document.getElementById('refund_total_dont_point').innerText = (Total - Points)
        window.document.getElementById('point').innerText = Points
    }

    function onchangePoint() {
        setTotal = window.document.getElementById('ReturnPointTotal').value
        let procent
        if (setTotal !== 0 && setTotal !== undefined) {
            procent = setTotal * 100 / return_total
        } else {
            procent = 0
            setTotal = 0
        }

        if (setPoints !== 0 && setPoints !== undefined) {
            setPoints = (return_points * procent / 100).toFixed(2)
        } else {
            setPoints = 0
        }

        setInnerText_Point_and_Total(setTotal, setPoints);
    }

    function btnPrivateReturn(val) {
        window.document.getElementById('Private_return').style.display = "none"
        window.document.getElementById('Private_return_full').style.display = "none"
        window.document.getElementById('ReturnPointTotal').value = ''
        if (val === 0) {
            window.document.getElementById('Private_return').style.display = "block"
            window.document.getElementById('refund_total').innerText = return_total
            window.document.getElementById('refund_total_dont_point').innerText = return_total - return_points
            window.document.getElementById('point').innerText = return_points
        }
        if (val === 1) {
            window.document.getElementById('Private_return_full').style.display = "block"
        }
    }


    function only_float() {
        if (event.keyCode < 48 || event.keyCode > 57) if (event.keyCode === 46) event.returnValue = true; else event.returnValue = false;
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

    .buttons-container-head {
        background-color: rgba(12, 125, 112, 0.27);
        padding-top: 3px;
        min-height: 3px;
    }

    .buttons-container {
        padding-top: 10px;
        min-height: 100px;
    }

    .text-orange {
        color: orange;
    }

    .uds-gradient {
        background: rgb(145, 0, 253);
        background: linear-gradient(34deg, rgba(145, 0, 253, 1) 0%, rgba(232, 0, 141, 1) 100%);
    }
</style>
</body>
</html>



