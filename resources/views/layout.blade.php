<!doctype html>
<html lang="en">
@include('head')
<body style="background-color:#dcdcdc;">

<div class="page headfull">
        <div class="sidenav">

            <div class="p-2 uds-gradient">
                <div class="row text-white">
                    <div class="col-2">
                        <img src="https://smartuds.kz/Config/UDS.png" width="35" height="35"  alt="">
                    </div>
                    <div class="mt-1 col-10">
                        <label class="s-min-16"> UDS интернет магазин </label>
                    </div>

                </div>
            </div>
            <br>
            <div class="mb-2 toc-list-h1">
                <a id="link_1" href="{{ route('indexMain', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin ] ) }}">Главная </a>
                <div class="mt-2">
                    <button id="btn_1" class="dropdown-btn">Настройки <i class="fa fa-caret-down"></i> </button>
                    <div class="dropdown-container">
                        <a class="mt-1" id="link_2" href="/Setting/{{$accountId}}/{{$isAdmin}}"> Основная </a>
                        <a class="mt-1" id="link_3" href="/Setting/Document/{{$accountId}}/{{$isAdmin}}"> Заказы </a>
                        <a class="mt-1" id="link_4" href="/Setting/sendOperations/{{$accountId}}/{{$isAdmin}}"> Операции </a>
                        <a class="mt-1" id="link_5" href="/Setting/Employees/{{$accountId}}/{{$isAdmin}}"> Сотрудники </a>
                        <a class="mt-1" id="link_6" href="/Setting/Add/{{$accountId}}/{{$isAdmin}}"> Дополнительные настройки </a>
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
                        <a target="_blank" href="https://api.whatsapp.com/send/?phone=77232400545&text=" >
                            <i class="fa-brands fa-whatsapp"></i>
                            Написать на WhatsApp </a>
                        <a target="_blank" href="https://smartuds.bitrix24.site/instruktsiiponastroyke/" >
                            <i class="fa-solid fa-chalkboard-user"></i>
                             Инструкция </a>
                    </div>
            </div>

        </div>

        <div class="main headfull">
                @yield('content')
        </div>
    </div>

</body>
</html>



<script>
    var dropdown = document.getElementsByClassName("dropdown-btn");
    var i;

    for (i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }

    let item = '@yield('item')'

    window.document.getElementById(item).classList.add('active_sprint')
    if (item.replace(/[^+\d]/g, '') > 1 && item.replace(/[^+\d]/g, '') <= 6){
        this_click(window.document.getElementById('btn_1'))
    }

    function this_click(btn){
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
