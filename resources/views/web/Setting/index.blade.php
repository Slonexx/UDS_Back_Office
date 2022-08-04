
@extends('layout')

@section('content')


    <div class="content p-4 mt-2 bg-white text-Black rounded">
        <h4> <i class="fa-solid fa-gears text-orange"></i> Данные для интеграции</h4>

        <br>



        <form action="  {{ route( 'setSettingIndex' , ['accountId' => $accountId] ) }} " method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="mb-3 row mx-1">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="mx-3">ID компании</label>
                        <div class="col-sm-10">
                            <input type="text" name="companyId" id="companyId" placeholder="ID компании"
                                   class="form-control form-control-orange" required maxlength="255" value="{{$companyId}}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <label class="mx-3">API Key</label>
                        <div class="col-sm-10">
                            <input type="text" name="TokenUDS" id="TokenUDS" placeholder="API Key"
                                   class="form-control form-control-orange" required maxlength="255" value="{{$TokenUDS}}">
                        </div>
                    </div>
                </div>

            </div>



            <hr class="href_padding">



            <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить </button>


        </form>
    </div>





@endsection



<style>
    .selected {
        margin-right: 0px !important;
        background-color: rgba(17, 17, 17, 0.14) !important;
        border-radius: 3px !important;
    }
    .dropdown-item:active {
        background-color: rgba(123, 123, 123, 0.14) !important;
    }

    .block {
        display: none;
        margin: 10px;
        padding: 10px;
        border: 2px solid orange;
    }

</style>
