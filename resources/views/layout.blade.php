<!doctype html>
<html lang="en">
@include('head')
<body style="background-color:#dcdcdc;">

<div class="headfull">
    <div class="sidenav">

        <div class="p-2 gradient_layout box columns" style="margin-bottom: 0rem !important;">
            <div class="column is-2 field" style="height: 3rem">
                <figure class="image is-32x32"><img src="{{  ( Config::get("Global") )['url'].'fonts/UDS.png' }}"/>
                </figure>
            </div>
            <div class="column field" style="font-size: 1rem; height: 3rem"> UDS интернет магазин</div>
        </div>

        <br>
        <div class="mb-2 toc-list-h1">
            <a id="link_1" href="{{ route('indexMain', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin ] ) }}">Главная </a>
            <div class="mt-2">
                <button id="btn_1" class="dropdown-btn">Настройки <i class="fa fa-caret-down"></i></button>
                <div class="dropdown-container">
                    <a class="mt-1" id="link_2" href="/Setting/Main/{{$accountId}}/{{$isAdmin}}"> Подключение </a>
                    <a class="mt-1" id="link_7" href="/Setting/createProduct/{{$accountId}}/{{$isAdmin}}"> Товары </a>
                    <a class="mt-1" id="link_8" href="/Setting/createAgent/{{$accountId}}/{{$isAdmin}}">
                        Контрагенты </a>

                    <a class="mt-1" id="link_3" href="/Setting/Document/{{$accountId}}/{{$isAdmin}}"> Заказы </a>
                    <a class="mt-1" id="link_4" href="/Setting/sendOperations/{{$accountId}}/{{$isAdmin}}">
                        Операции </a>
                    <a class="mt-1" id="link_5" href="/Setting/Employees/{{$accountId}}/{{$isAdmin}}"> Сотрудники </a>
                    <a class="mt-1" id="link_6" href="/Setting/Automation/{{$accountId}}/{{$isAdmin}}">
                        Автоматизация </a>
                </div>
            </div>

        </div>

        <div class="">
            <button class="dropdown-btn">Помощь
                <i class="fa fa-caret-down"></i></button>
            <div class="dropdown-container">
                <a target="_blank" href="https://smartuds.bitrix24.site/contact/">
                    <i class="fa-solid fa-address-book"></i>
                    Контакты </a>
                <a target="_blank" href="https://api.whatsapp.com/send/?phone=77232400545&text=">
                    <i class="fa-brands fa-whatsapp"></i>
                    Написать на WhatsApp </a>
                <a target="_blank" href="https://smartuds.bitrix24.site/instruktsiiponastroyke/">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    Инструкция </a>
            </div>
        </div>

    </div>

    <div class="main main-container content-container" style="background-color:#dcdcdc; padding-top: 10px">
        @yield('content')
    </div>
</div>

</body>
</html>


<script>
    let dropdown = document.getElementsByClassName("dropdown-btn");
    let i;

    for (i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function () {
            this.classList.toggle("active");
            let dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }

    let item = '@yield('item')'

    window.document.getElementById(item).classList.add('active_sprint')
    if (item.replace(/[^+\d]/g, '') > 1 && item.replace(/[^+\d]/g, '') <= 8) {
        this_click(window.document.getElementById('btn_1'))
    }

    function this_click(btn) {
        btn.classList.toggle("active");
        let dropdownContent = btn.nextElementSibling;
        if (dropdownContent.style.display === "block") {
            dropdownContent.style.display = "none";
        } else {
            dropdownContent.style.display = "block";
        }
    }

</script>

@include('style')
