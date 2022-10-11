<!doctype html>
<html lang="en">
@include('head')

<body>

<script>
        const url = 'https://dev.smartuds.kz/'
        let GlobalobjectId
        let GlobalURL

        let operations_cashier_id = "{{ $cashier_id }}"
        let operations_cashier_name = "{{ $cashier_name }}"


        window.addEventListener("message", function(event) {
            let receivedMessage = event.data;
            GlobalobjectId = receivedMessage.objectId;
            if (receivedMessage.name === 'Open') {
                let oReq = new XMLHttpRequest()
                oReq.addEventListener("load", function() {
                    let responseTextPars = JSON.parse(this.responseText);
                    let StatusCode = responseTextPars.StatusCode;
                    let message = responseTextPars.message;
                    if (StatusCode == 402){
                        document.getElementById("Error402").style.display = "block"
                        document.getElementById("ErrorMessage").innerText = responseTextPars.message
                    } else {



                    }
                });
                GlobalURL = "{{ $getObjectUrl }}" + receivedMessage.objectId;
                console.log('GlobalURL = ' + GlobalURL)
                oReq.open("GET", GlobalURL);
                oReq.send();
            }
        });


        function sendOperations(){

        }

    </script>



    <div class="main-container">
        <div id="undefined" class="bg-white text-Black rounded content-container">
            <div class="row uds-gradient p-2">
                <div class="col-2">
                    <img src="https://dev.smartuds.kz/Config/UDS.png" width="35" height="35" >
                </div>
                <div class="col-10 text-white mt-1 row">
                    Возврат по операции
                </div>
            </div>
            <div class="mt-2 row mx-2">
                <div class="col-1"></div>
                <div class="col-10 border border-info rounded p-2 text-black ">
                    <div class="row">
                        <button class="col-12 btn btn-danger">Частичный возврат</button>
                        <hr>
                        <div class="col-12">Итого к возврату</div>
                        <div class="col-8"> <span> Сумма  </span> </div>
                        <div class="col-4 text-end"> <span id="refund_total"> *** </span> </div>
                        <div class="col-8"> <span> Баллы  </span> </div>
                        <div class="col-4 text-end"> <span id="point"> *** </span> </div>
                        <hr>
                    </div>
                </div>
            </div>
            <div id="buttonOperations">
                <div class="mt-2 row mx-2">
                    <div class="col-1"></div>
                    <button onclick="sendOperations()" class="btn btn-danger col-10"> Сделать возврат </button>
                </div>
            </div>
        </div>
    </div>


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
</style>
</body>
</html>



