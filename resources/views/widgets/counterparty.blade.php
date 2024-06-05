@extends('widget.widget')
@section('content')

    <div id="activated" class="content bg-white text-Black rounded">
        <div class="row uds-gradient">
            <div class="p-2 col-6 text-white">&nbsp;
                <img src="https://smartuds.kz/Config/UDS.png" width="40" height="40" alt="">&nbsp;
                <label onclick="xRefURL()" style="cursor: pointer"><i
                        class="fa-solid fa-arrow-up-right-from-square"></i> Клиент</label>
            </div>
            <div class="col-6 text-right text-white">
                <span id="displayName" class=""></span>&nbsp;
                <div style="font-size: 12px">
                    <div class="my-bg-gray p-1 px-2">
                        <span id="membershipTierName"></span>
                        <span id="membershipTierRate"></span>
                        <span>%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3 mx-2 text-black mt-1">
            <div class="col-8">
                <div class="s-min ">Последняя покупка</div>
            </div>
            <div class="col-4">
                <span id="lastTransactionTime" class="s-min"></span>
            </div>

            <div class="col-8 mt-2">
                <div class="s-min">Бонусные баллы:</div>
            </div>
            <div class="col-4 bg-success my-bg-success text-white p-1">
                <span id="points" class="s-min mx-2"></span>
            </div>
        </div>


        <div class="row mb-3">
            <div class="col-1"></div>
            <div class="col-10">
                <select onchange="Bonus_UDS(this.value)" class="p-1 form-select" id="Bonus">
                    <option value="0" selected> Действия с баллами</option>
                    <option value="1"> Начислить баллы</option>
                    <option value="2"> Списать баллы</option>
                </select>
            </div>
            <div class="col-1"></div>
        </div>

        <div id="Accrue" class="row" style="display: none; background-color: #f7f7f7">
            <div class="col-1"></div>
            <div class="col-10">
                <div class="input-group">
                    <div class="input-group-text" id="btnGroupAddon">Введите баллы</div>
                    <input type="text" name="Accrue" id="inputAccrue" class="form-control" required maxlength="10"
                           oninput="handleInput(event)" onkeydown="handleKeyDown(event)">
                    <button onclick="BonusProgramme('inputAccrue')" class="btn btn-success rounded-end">Начислить
                    </button>
                </div>
            </div>
            <div class="col-1"></div>
        </div>
        <div id="Cancellation" class="row" style="display: none; background-color: #f7f7f7">
            <div class="col-1"></div>
            <div class="col-10">
                <div class="input-group">
                    <div class="input-group-text" id="CancellationAddon">Введите баллы</div>
                    <input type="text" name="Cancellation" id="inputCancellation" class="form-control" required
                           maxlength="10" oninput="handleInput(event)" onkeydown="handleKeyDown(event)">
                    <button onclick="BonusProgramme('inputCancellation')" class="btn btn-danger rounded-end">Списать
                    </button>
                </div>
            </div>
            <div class="col-1"></div>
        </div>

        <div id="success" class="mt-2" style="display: none">
            <div class="row">
                <div class="col-1"></div>
                <div class="col-10">
                    <div class=" alert alert-success fade show in text-center "> Баллы начислены !</div>
                </div>
            </div>
        </div>
        <div id="NotSuccess" class="mt-2" style="display: none">
            <div class="row">
                <div class="col-1"></div>
                <div class="col-10">
                    <div class=" alert alert-success fade show in text-center "> Баллы списаны !</div>
                </div>
            </div>
        </div>
        <div id="danger" class="mt-2" style="display: none">
            <div class="row">
                <div class="col-1"></div>
                <div class="col-10">
                    <div class=" alert alert-danger alert-danger fade show in text-center "> Ошибка 400</div>
                </div>
            </div>

        </div>

    </div>

    <div id="undefined" class="bg-white text-Black rounded" style="display: none">
        <div class="uds-gradient mb-2">
            <div class="p-2 text-white">
                <img src="https://smartuds.kz/Config/UDS.png" width="30" height="30" alt="">
                UDS - полноценная интеграция
            </div>
        </div>
        <div class="text-center">
            <div class="p-3 mb-2 bg-danger text-white">
                <i class="fa-solid fa-ban text-danger "></i>
                Данного контрагента нет в UDS
                <i class="fa-solid fa-ban text-danger "></i>
            </div>
        </div>
    </div>
    <div id="NoAdmin" class="bg-white text-Black rounded" style="display: none">
        <div class="uds-gradient mb-2">
            <div class="p-2 text-white">
                <img src="https://smartuds.kz/Config/UDS.png" width="30" height="30" alt="">
                UDS - полноценная интеграция
            </div>
        </div>
        <div class="text-center">
            <div class="p-3 mb-2 bg-danger text-white">
                <i class="fa-solid fa-ban text-danger "></i>У вас недостаточно прав<i
                    class="fa-solid fa-ban text-danger "></i>
            </div>
        </div>
    </div>

    <script>
        let admin = "{{ $admin }}"
        let accountId = "{{ $accountId }}"
        const url = "{{ app(\App\Http\Controllers\mainURL::class)->me_url_host() }}"

        let ObjectID
        let GlobalURL
        let GlobalxRefURL
        let UDSClientID

        /*let receivedMessage = {
            "name": "Open",
            "extensionPoint": "document.counterparty.edit",
            "objectId": "11185d66-0aa3-11ee-0a80-07c20069e1a4",
            "messageId": 1,
            "displayMode": "expanded"
        }
*/
        window.addEventListener("message", function (event) {
            let receivedMessage = event.data
            ObjectID = receivedMessage.objectId
            if (receivedMessage.name === 'Open') {
                clr()
                let settings = ajax_settings(url + 'CounterpartyObject/' + accountId + '/' + receivedMessage.objectId, "GET", null)
                $.ajax(settings).done(function (json) {
                    if (json.Bool === true) {
                        document.getElementById("activated").style.display = "block";
                        document.getElementById("undefined").style.display = "none";

                        let participant = json.customers.participant;
                        let membershipTier = participant.membershipTier
                        UDSClientID = participant.id;
                        GlobalxRefURL = "https://admin.uds.app/admin/customers/" + participant.id + '/info';

                        window.document.getElementById("displayName").innerHTML = json.customers.displayName;
                        window.document.getElementById("lastTransactionTime").innerHTML = participant.lastTransactionTime.substr(0, 10);
                        window.document.getElementById("points").innerHTML = formatNumberWithSpaces(participant.points);
                        window.document.getElementById("membershipTierName").innerHTML = membershipTier.name;
                        window.document.getElementById("membershipTierRate").innerHTML = membershipTier.rate;

                    } else {
                        document.getElementById("activated").style.display = "none";
                        document.getElementById("undefined").style.display = "block";
                    }

                })

            }
        });




        function BonusProgramme(ById) {
            document.getElementById("success").style.display = "none"
            document.getElementById("NotSuccess").style.display = "none"
            document.getElementById("danger").style.display = "none"
            let settings = '';
            if (ById === 'inputAccrue') {
                settings = ajax_settings(url + "Accrue/" + accountId + "/" + document.getElementById(ById).value + "/" + UDSClientID, "GET", null)
            } else settings = ajax_settings(url + "Cancellation/" + accountId + "/" + document.getElementById(ById).value + "/" + UDSClientID, "GET", null)

            $.ajax(settings).done(function (json) {
                if (json.Bool === true) {
                    if (ById === 'inputAccrue') {
                        document.getElementById("success").style.display = "block"
                        window.document.getElementById('points').innerText = parseFloat(window.document.getElementById('points').innerText) + parseFloat(document.getElementById(ById).value)
                    } else {
                        document.getElementById("NotSuccess").style.display = "block"
                        window.document.getElementById('points').innerText = parseFloat(window.document.getElementById('points').innerText) - parseFloat(document.getElementById(ById).value)
                    }
                } else {
                    document.getElementById("danger").style.display = "block"
                }
            })
        }

        function Bonus_UDS(val) {
            document.getElementById("Cancellation").style.display = "none";
            document.getElementById("Accrue").style.display = "none";
            document.getElementById("inputAccrue").value = 0;
            document.getElementById("inputCancellation").value = 0;

            if (val === "1") {
                document.getElementById("Accrue").style.display = "flex";
            } else if (val === "2") {
                document.getElementById("Cancellation").style.display = "flex";
            }
        }




        function clr() {
            displayName.innerText = ''
            lastTransactionTime.innerText = ''
            points.innerText = ''

            Accrue.style.display = 'none'
            Cancellation.style.display = 'none'
            success.style.display = 'none'
            NotSuccess.style.display = 'none'
            danger.style.display = 'none'
            window.document.getElementById('undefined').style.display = 'none'
            NoAdmin.style.display = 'none'


            document.getElementById('Bonus').value = 0;
            Bonus_UDS()

            document.getElementById("success").style.display = "none";
            document.getElementById("danger").style.display = "none";
        }


    </script>
    <script>
        function xRefURL() {
            window.open(GlobalxRefURL);
        }

        function handleInput(event) {
            const input = event.target;
            input.value = input.value.replace(/[^0-9.]/g, '');
        }
        function handleKeyDown(event) {
            if (event.key === 'Backspace' || event.key === 'Delete') return;

            // Запретить ввод символов и букв
            if (!/[\d.]/.test(event.key)) {
                event.preventDefault();
            }
        }

        function formatNumberWithSpaces(number) {
            // Преобразуем число в строку и используем регулярное выражение для добавления пробелов
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
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
@endsection

<style>

    .uds-gradient {
        background: rgb(145, 0, 253);
        background: linear-gradient(34deg, rgba(145, 0, 253, 1) 0%, rgba(232, 0, 141, 1) 100%);
    }

    .s-min {
        font-size: 10pt;
    }

    .my-bg-gray {
        background-color: #ebefff !important;
        color: #3b3c65;
        border-radius: 14px !important;
        overflow: hidden !important;
    }


    .my-bg-success {
        border-radius: 14px !important;
        overflow: hidden !important;
    }

</style>
