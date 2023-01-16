<style>



    .s-min-16 {
        font-size: 16px;
    }



    .uds-gradient{
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }

    .gradient{
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }

    .text_gradient{
        background-color: rgb(145,0,253);
        background-image: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
        background-size: 100%;
        -webkit-background-clip: text;
        -moz-background-clip: text;
        -webkit-text-fill-color: transparent;
        -moz-text-fill-color: transparent;
    }

    .gradient_focus:hover{
        color: white;
        border: 0px;
        background: rgb(145,0,253);
        background: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
    }

    .gradient_focus:active, .gradient_focus:focus{
        background-color: rgb(145,0,253);
        background-image: linear-gradient(34deg, rgba(145,0,253,1) 0%, rgba(232,0,141,1) 100%);
        border: 0px;
        background-size: 100%;
        -webkit-background-clip: text;
        -moz-background-clip: text;
        -webkit-text-fill-color: transparent;
        -moz-text-fill-color: transparent;
    }


    .gradient_invert{
        background-image: linear-gradient(135deg, #e1eaf8 0%, #f5f7fa 100%);
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
        background-image: linear-gradient(135deg, #e1eaf8 0%, #f5f7fa 100%);
        padding-top: 3px;
        min-height: 3px;
    }
    .buttons-container {
        padding-top: 10px;
        min-height: 10px;
    }


</style>

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
        background-image: radial-gradient( circle farthest-corner at 10% 20%,  rgba(145,0,253,1) 0%, rgba(232,0,141,1) 90% );
        border-radius: 10px 10px 0px 0px;
        color: white;
    }

    /* Основное содержание */
    .main {
        margin-left: 15%; /* То же, что и ширина боковой навигации */
        font-size: 18px; /* Увеличенный текст для включения прокрутки */
        padding: 0 10px;
    }
    /* Добавить активный класс для кнопки активного выпадающего списка */
    .sidenav .active_sprint {
        background-image: radial-gradient( circle farthest-corner at 10% 20%,  rgba(145,0,253,1) 0%, rgba(232,0,141,1) 90% );
        margin-right: 50px;
        border-radius: 10px 10px 0px 0px;
        color: white;
    }

    /* Выпадающий контейнер (по умолчанию скрыт). Необязательно: добавьте более светлый цвет фона и некоторые левые отступы, чтобы изменить дизайн выпадающего содержимого */
    .dropdown-container {
        display: none;
        background-color: #d5d5d5;
        padding: 5px;
    }

    /* Необязательно: стиль курсора вниз значок */
    .fa-caret-down {
        float: right;
        padding-right: 8px;
    }


</style>
