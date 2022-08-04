@extends('layout')

@section('content')

    <form action="  {{ route( 'CheckSave' , ['accountId' => $accountId] ) }} " method="post">
    @csrf <!-- {{ csrf_field() }} -->
        <button class="btn btn-outline-dark textHover"> check </button>

    </form>


@endsection

