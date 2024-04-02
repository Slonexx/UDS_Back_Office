@extends('layout')
@section('item', 'link_2')
@section('name_head', 'Настройки → Подключение')
@section('content')

    @include('div.TopServicePartner')
    @include('div.notification')
    <div class="box">
        <form action="{{route('setSettingIndex', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin ])}}" method="post">
            @csrf <!-- {{ csrf_field() }} -->
            <div class="row mt-1 p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">UDS данные</div>
                </div>
                <div onclick="toggleClick(1)" class="col-1 d-flex justify-content-end "
                     style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>
            <div id="uds_data" class="mb-3 row">
                <div class="col-6 mt-1">

                    <div class="row">
                        <label class="row mx-1">
                            <div class="col-9">ID компании</div>
                            <button type="button" class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover1 "
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Данный ID находится в UDS &#8594; Настройки &#8594; Интеграция &#8594; Данные для интеграции ">
                            </button>
                        </label>
                        <div class="col-10">
                            <input type="text" name="companyId" id="companyId" placeholder="ID компании"
                                   class="form-control form-control-orange" required maxlength="255" value=" ">
                        </div>
                    </div>

                    <div class="row mt-2">

                        <label class="row mx-1">
                            <div class="col-9">API Key</div>
                            <button type="button" class=" col-1 btn gradient_focus fa-solid fa-circle-info myPopover2 "
                                    data-toggle="popover" data-placement="right" data-trigger="focus"
                                    data-content="Данный ID находится в UDS &#8594; Настройки &#8594; Интеграция &#8594; Данные для интеграции ">
                            </button>
                        </label>

                        <div class="col-sm-10">
                            <input type="text" name="TokenUDS" id="TokenUDS" placeholder="API Key"
                                   class="form-control form-control-orange" required maxlength="255" value=" ">
                        </div>
                    </div>
                </div>

            </div>

            <hr class="href_padding">
            <button class="btn btn-outline-dark gradient_focus"> Сохранить</button>
        </form>
    </div>


    <script>
        let companyId = "{{ $companyId }}"
        let TokenUDS = "{{ $TokenUDS }}"


        window.document.getElementById('companyId').value = companyId
        window.document.getElementById('TokenUDS').value = TokenUDS

        function toggleClick(id) {

            if (id === 1) {
                let toggle_off = window.document.getElementById('toggle_off')
                let toggle_on = window.document.getElementById('toggle_on')

                let T1 = window.document.getElementById('uds_data')

                if (toggle_off.style.display == "none") {
                    toggle_on.style.display = "none"
                    toggle_off.style.display = "block"

                    T1.style.display = 'block'
                } else {
                    toggle_on.style.display = "block"
                    toggle_off.style.display = "none"

                    T1.style.display = 'none'
                }
            }

            if (id === 2) {
                let toggle_off_2 = window.document.getElementById('toggle_off_2')
                let toggle_on_2 = window.document.getElementById('toggle_on_2')

                let T2 = window.document.getElementById('update_uds_data')
                if (toggle_off_2.style.display == 'none') {
                    toggle_on_2.style.display = "none"
                    toggle_off_2.style.display = "block"

                    T2.style.display = 'block'
                } else {
                    toggle_on_2.style.display = "block"
                    toggle_off_2.style.display = "none"

                    T2.style.display = 'none'
                }
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


        $('.myPopover1').popover();
        $('.myPopover2').popover();
        $('.myPopover3').popover();
        $('.myPopover4').popover();
        $('.myPopover5').popover();
    </script>

@endsection
