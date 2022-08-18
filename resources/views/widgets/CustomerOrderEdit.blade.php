
@extends('widgets.index')

@section('counterparty')

    <script>
        var GlobalobjectId;
        var GlobalURL;
        var GlobalxRefURL;
        var UDSClientID;
        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;
            GlobalobjectId = receivedMessage.objectId;

            if (receivedMessage.name === 'Open') {

                var oReq = new XMLHttpRequest();


                oReq.addEventListener("load", function() {
                        var responseTextPars = JSON.parse(this.responseText);

                    var participant = responseTextPars.participant;
                    var membershipTier = participant.membershipTier
                    UDSClientID = participant.id;
                    GlobalxRefURL = "https://admin.uds.app/admin/customers/"+participant.id+'/info';

                    window.document.getElementById("displayName").innerHTML = responseTextPars.displayName;
                    window.document.getElementById("lastTransactionTime").innerHTML = participant.lastTransactionTime.substr(0,10);
                    window.document.getElementById("points").innerHTML = participant.points;
                    window.document.getElementById("membershipTierName").innerHTML = membershipTier.name;
                    window.document.getElementById("membershipTierRate").innerHTML = membershipTier.rate;
                });
                GlobalURL = "{{$getObjectUrl}}" + receivedMessage.objectId;
                oReq.open("GET", GlobalURL);
                oReq.send();
            }
        });
        //Доделать потом обновление кнопка
        /*function update(){
            var xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {

                try {
                    var responseTextPars = JSON.parse(this.responseText);
                    document.getElementById("activated").style.display = "block";
                    document.getElementById("undefined").style.display = "none";
                } catch (error){
                    document.getElementById("activated").style.display = "none";
                    document.getElementById("undefined").style.display = "block";
                }

                var participant = responseTextPars.participant;
                var membershipTier = participant.membershipTier
                UDSClientID = participant.id;
                GlobalxRefURL = "https://admin.uds.app/admin/customers/"+participant.id+'/info';

                window.document.getElementById("displayName").innerHTML = responseTextPars.displayName;
                window.document.getElementById("lastTransactionTime").innerHTML = participant.lastTransactionTime.substr(0,10);
                window.document.getElementById("points").innerHTML = participant.points;
                window.document.getElementById("membershipTierName").innerHTML = membershipTier.name;
                window.document.getElementById("membershipTierRate").innerHTML = membershipTier.rate;

            });

            xmlHttpRequest.open("GET", GlobalURL);
            xmlHttpRequest.send();
        }*/

        function xRefURL(){
            window.open(GlobalxRefURL);
        }

    </script>

    @php
        $View = true;
    @endphp




    <div id="activated" class="content bg-white text-Black rounded">
        <div class="row uds-gradient mx-2">
            <div class="mx-2 p-2 col-9 text-white">
                <img src="https://smartuds.kz/Config/UDS.png" width="30" height="30" >
                <label onclick="xRefURL()" style="cursor: pointer"> Заказ </label>
            </div>
            <div class="mx-2 col-2 p-2">
                <button type="submit" onclick="update()" class="myButton btn "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
            </div>
            <div class="row mx-3 text-white">
                <div class="col-7">
                    <h6 id="displayName" class=""></h6>
                </div>
                <div class="col-5">
                    <div class="s-min-8 my-bg-gray p-1 px-2">
                        <span> Уровень: </span>
                        <span id="membershipTierName"></span>
                        <span id="membershipTierRate"></span>
                        <span>%</span>
                    </div>
                </div>
            </div>
        </div>




        <br>


        </div>




    </div>





@endsection

<style>

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
    .my-bg-gray{
        background-color: #ebefff !important;
        color: #3b3c65;
        border-radius: 14px !important;
        overflow: hidden !important;
    }

    .my-bg-success{
        border-radius: 14px !important;
        overflow: hidden !important;
    }

</style>