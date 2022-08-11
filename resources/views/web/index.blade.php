@extends('layout')

@section('content')

    <form action="  {{ route( 'CheckSave' , ['accountId' => $accountId] ) }} " method="post">
    @csrf <!-- {{ csrf_field() }} -->
        <button class="btn btn-outline-dark textHover"> check </button>

    </form>

    <script>



    </script>

    <div class="row">
        <div class="col-1">

        </div>
        <div class="col-10">
        <select onclick="Bonus()" class="p-1 form-select" id="Bonus">
            <option selected> Действия с баллами </option>
            <option value="1"> Начислить баллы </option>
            <option value="2"> Списать баллы </option>
        </select>
        </div>
        {{--Начисление--}}
        <div id="Accrue" class="row" style="display: none">
            <div class="row mt-2">

                <div class="col-4">
                    <label class="form-label"> Количество баллов </label>
                </div>
                <div class="col-6">
                    <input type="text" name="Accrue" id="Accrue" class="form-control"
                           required maxlength="10" >
                </div>
                <div class="col-2">
                    <button class="btn btn-success">Начислить</button>
                </div>

            </div>
        </div>
        {{--Списание--}}
        <div id="Cancellation" class="row" style="display: none">
            Списание
        </div>

    </div>

    <script>
        function Bonus() {
            var select = document.getElementById('Bonus');
            var option = select.options[select.selectedIndex];
            if (option.value == 1) {
                document.getElementById("Accrue").style.display = "block";
                document.getElementById("Cancellation").style.display = "none";
            }else if (option.value == 2) {
                document.getElementById("Cancellation").style.display = "block";
                document.getElementById("Accrue").style.display = "none";
            }
            else {
                document.getElementById("Cancellation").style.display = "none";
                document.getElementById("Accrue").style.display = "none";
            }
        }
    </script>

@endsection

