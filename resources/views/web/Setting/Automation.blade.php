
@extends('layout')
@section('item', 'link_6')
@section('content')
    <div class="main-container">
        <div class="content-container">
            <div class="content p-4 mt-2 bg-white text-Black rounded">
                <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
                    <div class="col-10" style="margin-top: 1.2rem"> <span class="text-white" style="font-size: 20px">  Настройки &#8594; Автоматизация </span></div>
                    <div class="col-2 text-center">
                        <img src="https://smarttis.kz/Config/logo.png" width="40%"  alt="">
                        <div class="text-white" style="font-size: 11px; margin-top: 8px"> Топ партнёр сервиса МойСклад </div>
                    </div>
                </div>

                @if($message == true)
                    <div class="{{$class}}"> {{$message}}</div>
                @endif

                <div class="mt-3 alert alert-warning alert-dismissible fade show in text-center"> Данный раздел предлагает автоматизировать начисление баллов из "Заказа покупателя", путем смены статуса.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <form action="/setSetting/Automation/{{ $accountId }}/{{ $isAdmin }}" method="post">
                @csrf <!-- {{ csrf_field() }} -->
                    <div class="mt-2 row p-1 gradient_invert rounded text-black">
                        <div class="col-11">
                            <div style="font-size: 20px"> Основное </div>
                        </div>
                        <div onclick="toggleClick(1)" class="col-1 d-flex justify-content-end " style="font-size: 30px; cursor: pointer">
                            <i id="toggle_off" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                            <i id="toggle_on"  class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                        </div>
                    </div>
                    <div id="T1">
                        <div class="mt-2 row">
                            <div class="col-6">
                                <label class="mt-1 from-label">Активировать автоматизацию начисление баллов по статусу ? </label>
                            </div>
                            <div class="col-2">
                                <select id="activateAutomation" name="activateAutomation" class="form-select text-black" onchange="FU_activateAutomation(this.value)">
                                    <option value="0">Нет</option>
                                    <option value="1">Да</option>
                                </select>
                            </div>
                        </div>
                        <div id="T1View" class="">
                            <div class="mt-3 alert alert-warning alert-dismissible fade show in text-center"> Выберите основные проверки для автоматизации
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <div class="row">
                                <label class="col-5 col-form-label"> Выберите статус на котором будет автоматизация </label>
                                <div class="col-7 ">
                                    <select id="statusAutomation" name="statusAutomation" class="form-select text-black">
                                        @foreach($arr_meta as $item)
                                            <option value="{{ $item->name }}"> {{ ($item->name) }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-5 col-form-label"> Выберите проект</label>
                                <div class="col-7 ">
                                    <select id="projectAutomation" name="projectAutomation" class="form-select text-black " >
                                        <option value="0"> Не выбирать  </option>
                                        @foreach($arr_project as $item)
                                            <option value="{{ $item->name}}"> {{ ($item->name) }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-5 col-form-label"> Выберите канал продаж</label>
                                <div class="col-7 ">
                                    <select id="saleschannelAutomation" name="saleschannelAutomation" class="form-select text-black " >
                                        <option value="0"> Не выбирать  </option>
                                        @foreach($arr_saleschannel as $item)
                                            <option value="{{ $item->name}}"> {{ ($item->name) }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="mt-2 row p-1 gradient_invert rounded text-black">
                        <div class="col-11">
                            <div style="font-size: 20px"> Дополнительные настройки </div>
                        </div>
                        <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end " style="font-size: 30px; cursor: pointer">
                            <i id="toggle_off_2" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                            <i id="toggle_on_2"  class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                        </div>
                    </div>
                    <div id="T2" style="display: block">
                        <div class="mt-3 alert alert-warning alert-dismissible fade show in text-center"> После автоматизации будут создаваться документы
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <div class="mt-1 row">
                            <P class="col-5 col-form-label"> Выберите какой тип документов создавать: </P>
                            <div class="col-7">
                                <select id="automationDocument" name="automationDocument" class="form-select text-black" onchange="FU_automationDocument(this.value)" >
                                    <option value="1">Не создавать</option>
                                    <option value="2">Отгрузка</option>
                                    <option value="3">Отгрузка + счет-фактура выданный</option>
                                </select>
                            </div>
                        </div>
                        <div id="T2View" style="display: none">
                            <div class="mt-1 row" >
                                <P class="col-sm-5 col-form-label"> Выберите на какой склад создавать отгрузку: </P>
                                <div class="col-sm-7">
                                    <select name="add_automationStore"  id="add_automationStore" class="form-select text-black">
                                        <option value="0"> Взять склад из заказа покупателя </option>
                                        @foreach($arr_store as $bodyItem)
                                            <option value="{{ $bodyItem->id }}"> {{ ($bodyItem->name) }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-1 row">
                                <P class="col-sm-5 col-form-label"> Выберите какой тип платежного документа создавать: </P>
                                <div class="col-sm-7">
                                    <select id="add_automationPaymentDocument" name="add_automationPaymentDocument" class="form-select text-black">
                                        <option value="0">Не создавать</option>
                                        <option value="1">Приходной ордер</option>
                                        <option value="2">Входящий платёж </option>
                                    </select>
                                </div>
                            </div>
                    </div>

                    <div class="mt-1 buttons-container-head rounded"></div>
                        <button class="mt-2 btn btn-outline-dark gradient_focus"> Сохранить </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>

        let activateAutomation = "{{$activateAutomation}}";
        let statusAutomation = "{{$statusAutomation}}";
        let projectAutomation = "{{$projectAutomation}}";
        let saleschannelAutomation = "{{$saleschannelAutomation}}";

        let automationDocument = "{{$automationDocument}}";
        let add_automationStore = "{{$add_automationStore}}";
        let add_automationPaymentDocument = "{{$add_automationPaymentDocument}}";

        window.document.getElementById('activateAutomation').value = activateAutomation
        if (statusAutomation != "0") window.document.getElementById('statusAutomation').value = statusAutomation
        window.document.getElementById('projectAutomation').value = projectAutomation
        window.document.getElementById('saleschannelAutomation').value = saleschannelAutomation

        window.document.getElementById('automationDocument').value = automationDocument
        if (add_automationStore != "") window.document.getElementById('add_automationStore').value = add_automationStore
        if (add_automationPaymentDocument != "") window.document.getElementById('add_automationPaymentDocument').value = add_automationPaymentDocument

        FU_activateAutomation(activateAutomation)
        FU_automationDocument(automationDocument)

        function FU_activateAutomation(params){
            let view = window.document.getElementById('T1View')
            if (params === 1 || params === '1') {
                view.style.display = 'block'
                if(window.document.getElementById('T2').style.display === 'none') toggleClick(2)
            } else {
                view.style.display = 'none'
                if(window.document.getElementById('T2').style.display === 'block') toggleClick(2)
            }
        }

        function FU_automationDocument(params){
            let view = window.document.getElementById('T2View')
            if (params != 1 || params != '1') {
                view.style.display = 'block'
            } else {
                view.style.display = 'none'
            }
        }

        function toggleClick(id){

            if (id === 1){
                let toggle_off = window.document.getElementById('toggle_off')
                let toggle_on = window.document.getElementById('toggle_on')

                let T1 = window.document.getElementById('T1')

                if (toggle_off.style.display == "none"){
                    toggle_on.style.display = "none"
                    toggle_off.style.display = "block"

                    T1.style.display = 'block'
                } else {
                    toggle_on.style.display = "block"
                    toggle_off.style.display = "none"

                    T1.style.display = 'none'
                }
            }

            if (id === 2){
                let toggle_off = window.document.getElementById('toggle_off_2')
                let toggle_on = window.document.getElementById('toggle_on_2')

                let T1 = window.document.getElementById('T2')

                if (toggle_off.style.display == "none"){
                    toggle_on.style.display = "none"
                    toggle_off.style.display = "block"

                    T1.style.display = 'block'
                } else {
                    toggle_on.style.display = "block"
                    toggle_off.style.display = "none"

                    T1.style.display = 'none'
                }
            }

        }
    </script>

@endsection

