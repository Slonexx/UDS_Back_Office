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
                        <label class="mx-2 s-min-18"> Интеграция с UDS </label>
                    </div>

                </div>
            </div>
            <br>
            <div class="toc-list-h1">
                <a href="{{ route('indexMain', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin ] ) }}">Главная </a>
                <div>
                    <button class="dropdown-btn">Настройки
                        <i class="fa fa-caret-down"></i></button>
                    <div class="dropdown-container">
                        <a href="/Setting/{{$accountId}}/{{$isAdmin}}"> Основная </a>
                        <a href="/Setting/Document/{{$accountId}}/{{$isAdmin}}"> Документы </a>
                        <a href="/Setting/Add/{{$accountId}}/{{$isAdmin}}"> Дополнительные настройки </a>
                    </div>
                </div>

            </div>

            <div class="">
                <button class="dropdown-btn">Помощь
                    <i class="fa fa-caret-down"></i></button>
                    <div class="dropdown-container">
                        <a target="_blank" href="https://smartkaspi.bitrix24.site/contact/">
                            <i class="fa-solid fa-address-book"></i>
                            Контакты </a>
                        <a target="_blank" href="https://api.whatsapp.com/send/?phone=77232400545&text=" >
                            <i class="fa-brands fa-whatsapp"></i>
                            Написать на WhatsApp </a>
                        <a  href="" >
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


<style>

    .headfull {
        height: 720px;
    }

    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #444444;
        font-size: 8pt;
        background-color: #FAFAFA;
    }

    .s-min-18 {
        font-size: 18px;
    }

    .uds-gradient{
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }

    .alertheight{
        text-align: center;
        float: right;
        position: relative;
        margin-right: auto;
        width: 25%;
    }

    /* Фиксированный боковых навигационных ссылок, полной высоты */
    .sidenav {
        height: 100%;
        width: 15%;
        position: fixed;
        z-index: 1;
        top: 0;
        left: 0;
        background-color: #eaeaea;
        overflow-x: hidden;
        padding-top: 20px;
    }

    /* Стиль боковых навигационных ссылок и раскрывающейся кнопки */
    .sidenav a, .dropdown-btn {
        padding: 6px 8px 6px 16px;
        text-decoration: none;
        font-size: 16px;
        color: #343434;
        display: block;
        border: none;
        background: none;
        width:100%;
        text-align: left;
        cursor: pointer;
        outline: none;
    }

    /* При наведении курсора мыши */
    .sidenav a:hover, .dropdown-btn:hover {
        background-color: #ffffff;
        border-radius: 20px;
        color: orange;
    }

    /* Основное содержание */
    .main {
        margin-left: 15%; /* То же, что и ширина боковой навигации */
        font-size: 18px; /* Увеличенный текст для включения прокрутки */
        padding: 0 10px;
    }
    /* Добавить активный класс для кнопки активного выпадающего списка */
    .active {
        background-color: #dcdcdc;
        margin-right: 50px;
        border-radius: 8px;
        color: #e59300;
    }

    /* Выпадающий контейнер (по умолчанию скрыт). Необязательно: добавьте более светлый цвет фона и некоторые левые отступы, чтобы изменить дизайн выпадающего содержимого */
    .dropdown-container {
        display: none;
        background-color: #bebebe;

        padding: 5px;
    }

    /* Необязательно: стиль курсора вниз значок */
    .fa-caret-down {
        float: right;
        padding-right: 8px;
    }
</style>

<style>
    /* Новый цвет текста */
    .text-orange{
        color: orange;
    }

    .btn-new:hover{
        border-color: white !important
    }
    .btn-new:focus{
        border-color: white !important;
        color: orange !important;
    }
    .form-control-orange:focus{
        background-color: white;
        border-color: black;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(255, 77, 0, 0.16);
    }

    .btnP p:hover {
        color: orange;
    }
    .btnP button:hover {
        color: orange;
    }

</style>

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
</script>

