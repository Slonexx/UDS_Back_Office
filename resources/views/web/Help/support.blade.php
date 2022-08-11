@extends('layout')

@section('content')
    <style>

        .full {
            height: 100%;
            width: 100%;
        }

    </style>


    <div class="headfull">
        <div class="content content p-4 mt-2 bg-white text-Black rounded">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item full" src="https://smartkaspi.bitrix24.site/"  allowfullscreen>
                </iframe>
            </div>
        </div>

        {{--<div class="content content p-4 mt-2 bg-white text-Black rounded">
            <h2 align="center">
                <i class="fa-solid fa-envelope text-orange"></i>
                Написать нам на почту </h2>



           <div class="mt-3">
                <form action=" {{ route('indexSendSupport', ['accountId'=>$accountId] )}} " method="post">
                @csrf <!-- {{ csrf_field() }} -->


                    <div class="form-group row ">
                        <label for="TokenKaspi" class="col-form-label ">Введите имя</label>
                        <div class="col-sm-12">
                            <input type="text" name="name" placeholder="Введите имя, фамилию" id="name" class="form-control form-control-orange"
                                   required maxlength="100" value="{{ old('name') ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row ">
                        <label for="TokenKaspi" class="col-form-label ">Адрес почты</label>
                        <div class="col-sm-12">
                            <input type="email" name="email" placeholder="Адрес почты" id="email" class="form-control form-control-orange"
                                   required maxlength="100" value="{{ old('email') ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="TokenKaspi" class="col-form-label"></label>
                        <div class="col-sm-12">
                        <textarea class="form-control form-control-orange" name="message" placeholder="Ваше сообщение"
                                  required maxlength="500" rows="3">{{ old('message') ?? '' }}</textarea>
                        </div>
                    </div>

                    <br>

                    <div class='d-flex justify-content-end text-black btnP' >
                        <p class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                            <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить </p>
                    </div>
                    <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"> Вопрос <i class="fa-solid fa-circle-question text-danger"></i></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Отправить сообщение ? </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Нет</button>
                                    <button type="submit" class="btn btn-outline-success">Да</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>



            </div>
        </div>--}}

    </div>

@endsection

<style>
    .headfull {
        height: 720px;
    }
</style>

