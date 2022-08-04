@extends('layout')

@section('content')

    <div class="content p-4 mt-2 bg-white text-Black rounded">

        <div class="row">
            <div class="col-sm-8">
                <h2 class=" text-black"> <i class="fa-solid fa-circle-plus text-orange"></i> Отправить товар на Kaspi </h2>
                <p class="mt-3">1. Выберите товар и поставьте галочку под пунктом &#34;Добавлять товар на Kaspi&#34;</p>
                            <p>2. Перейдите в приложение Магазин Kaspi.kz</p>
                            <p>3. В меню выберите &#34;Отправить товар&#34;</p>
                            <p>4. Нажмите на кнопку &#34;Скачать Excel&#34;</p>
                            <p>5. Откройте свой кабинет Kaspi продавца</p>
                            <p>6. В меню выберите Товары→Загрузить прайс-лист</p>
                            <p>7. В открывшемся окне выберите &#34;Загрузить файл вручную&#34; и вставьте ранее скаченный файл</p>
            </div>
            <div class="col-sm-4 d-flex justify-content-end text-black btnP" data-bs-toggle="modal" data-bs-target="#modal">


            </div>
           {{-- <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"> Инструкция по отправленю товаров в Kaspi </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Ok</button>
                        </div>
                    </div>
                </div>
            </div>--}}

        </div>


        {{-- <div class="alert  alert-warning alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>--}}
        <br>
        <div id="myModal" class="modal fade" id="modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> Инструкция по отправленю товаров в Kaspi </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mt-3">1. Выберите товар и поставьте галочку под пунктом &#34;Добавлять товар на Kaspi&#34;</p>
                        <p>2. Перейдите в приложение Магазин Kaspi.kz</p>
                        <p>3. В меню выберите &#34;Отправить товар&#34;</p>
                        <p>4. Нажмите на кнопку &#34;Скачать Excel&#34;</p>
                        <p>5. Откройте свой кабинет Kaspi продавца</p>
                        <p>6. В меню выберите Товары→Загрузить прайс-лист</p>
                        <p>7. В открывшемся окне выберите &#34;Загрузить файл вручную&#34; и вставьте ранее скаченный файл</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            // $(window).on('load',function(){
            //     $('#myModal').modal('show');
            // });
        </script>

            <div class="">


                <br>

               <form action=" {{  route('ExcelProducts' , ['TokenMoySklad' => $TokenMoySklad] ) }} " method="get">
                  <div class="row">
                      <div class="col-sm-8">Количество товаров которые можно отправить в Магазин Kaspi: {{$Count}} </div>
                      <div class="col-sm-4 d-flex justify-content-end text-black btnP">
                          <button type="submit" class="btn btn-outline-dark">Скачать Excel</button>
                      </div>
                  </div>
               </form>
            </div>

        <br>


    </div>



@endsection

