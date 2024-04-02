@extends('layout')
@section('item', 'link_8')
@section('name_head', 'Настройки → Контрагент')
@section('content')


    @include('div.TopServicePartner')
    @include('div.notification')
    <div class="box">
        <form action="  {{ route( 'setAgent' , [ 'accountId' => $accountId,  'isAdmin' => $isAdmin ] ) }} "
              method="post">
            @csrf <!-- {{ csrf_field() }} -->
            <div class="row mt-1 p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">Контрагент</div>
                </div>
                <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end "
                     style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off_2" class="fa-solid fa-toggle-off text_gradient" style="display: block"></i>
                    <i id="toggle_on_2" class="fa-solid fa-toggle-on  text_gradient" style="display: none"></i>
                </div>
            </div>
            <div id="update_uds_data">
                <div class="mt-2 row">
                    <div class="col-6"><label class="mx-3 mt-1 from-label"> Выгрузить всех контрагентов из UDS ? </label>
                    </div>
                    <div class="col-2">
                        <select id="unloading" name="unloading" class="form-select text-black"
                                onchange="FU_sendingGoods(this.value)">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>

                </div>
                <div id="T1View" class="mx-3" style="display: block">


                    {{--проверка--}}
                    <div class="mt-2 row">

                        <div class="col-6">
                            <label class="mt-2"> Выберите способ проверки </label>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <select name="examination" class="form-select text-black ">
                                    <option value="0">по номеру телефона</option>
                                    <option value="1">по названию</option>
                                    <option value="2">по номеру телефона и названию</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn gradient_focus myPopover1 " data-toggle="popover"
                                            data-placement="right" data-trigger="focus"
                                            data-content=" Способ проверки при выгрузки с UDS в МойСклад "><i
                                            class="fa-solid fa-circle-info"></i></button>
                                </div>
                            </div>

                        </div>

                    </div>


                    {{--почта--}}
                    <div class="mt-2 row">

                        <div class="col-6">
                            <label class="mt-2"> Выгружать электронную почту </label>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <select name="email" class="form-select text-black ">
                                    <option value="0">Нет</option>
                                    <option value="1">Да</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn gradient_focus myPopover2 " data-toggle="popover"
                                            data-placement="right" data-trigger="focus"
                                            data-content="Будет добавлять в карточку контрагента электронную почту если она будет">
                                        <i class="fa-solid fa-circle-info"></i></button>
                                </div>
                            </div>

                        </div>

                    </div>


                    {{--гендер--}}
                    <div class="mt-2 row">

                        <div class="col-6">
                            <label class="mt-2"> Выгружать гендер </label>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <select name="gender" class="form-select text-black ">
                                    <option value="0">Нет</option>
                                    <option value="1">Да</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn gradient_focus myPopover1 " data-toggle="popover"
                                            data-placement="right" data-trigger="focus"
                                            data-content="Будет добавлять в карточку контрагента гендер клиента если он есть">
                                        <i class="fa-solid fa-circle-info"></i></button>
                                </div>
                            </div>

                        </div>

                    </div>


                    {{--дата рождения--}}
                    <div class="mt-2 row">

                        <div class="col-6">
                            <label class="mt-2"> Выгружать дату рождения </label>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <select name="birthDate" class="form-select text-black ">
                                    <option value="0">Нет</option>
                                    <option value="1">Да</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn gradient_focus myPopover1 " data-toggle="popover"
                                            data-placement="right" data-trigger="focus"
                                            data-content="Будет добавлять в карточку контрагента дату рождения, если он будет.">
                                        <i class="fa-solid fa-circle-info"></i></button>
                                </div>
                            </div>

                        </div>

                    </div>


                </div>
            </div>

            <hr class="href_padding">
            <button class="btn btn-outline-dark gradient_focus"> Сохранить</button>
        </form>


    </div>


    @include('web.Setting.Agent.function')
    @include('web.Setting.Agent.set')

@endsection
