@extends('layout')

@section('content')
    <form action="">
        <div class="form-group">
            <label>Table Name:</label>
            <input type="text" name="table_name" class="form-control">
        </div>

        <hr>

        <migration-columns></migration-columns>

        <hr>

        <button class="btn btn-primay">Create Migration</button>
    </form>
@endsection