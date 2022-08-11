@extends('layout')

@section('content')
    <div class="headfull">
        <div class="content p-4 mt-2 bg-white text-black rounded">
            <h2 align="center">Написать на WhatsApp
                <i class="fa-brands fa-whatsapp text-success"></i>
            </h2>


            <div class="mt-3">
                <form action=" {{  route('indexSendWhatsapp', ['accountId' => $accountId] ) }} " method="post">
                @csrf <!-- {{ csrf_field() }} -->

                    <div class="form-group mb-3 row ">
                        <label for="TokenKaspi" class="col-form-label">Введите имя</label>
                        <div class="col-sm-12">
                            <input type="text" name="name" placeholder="Введите имя, фамилию" id="name" class="form-control form-control-orange"
                                   required maxlength="100" value="{{ old('name') ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="TokenKaspi" class="col-form-label"></label>
                        <div class="col-sm-12">
                        <textarea class="form-control form-control-orange" name="message" placeholder="Ваше сообщение"
                                  required maxlength="500" rows="3">{{ old('message') ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class='d-flex justify-content-end text-black btnP' >
                        <button target="_blank"  type="submit" class="mt-3 btn btn-outline-dark"> <i class="fa-brands fa-whatsapp"></i> Отправить </button>
                    </div>


                </form>

            </div>
        </div>
    </div>
@endsection
