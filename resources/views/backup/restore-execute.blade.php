@extends('layouts.admin')

@section('title', 'Restore Database')

@section('content')


<div class="py-6 px-6">


    <div class="bg-white rounded-xl shadow-md p-6 border">


        <h1 class="text-xl font-bold text-green-600">
            Ready To Restore Database
        </h1>


        <p class="mt-4 text-gray-700">

            Your backup has been verified successfully.

            Click the button below to start database restoration.

        </p>


        <div class="mt-6 bg-yellow-100 border border-yellow-300 p-4 rounded-lg">

            <strong class="text-yellow-700">
                Important:
            </strong>

            <p class="text-yellow-700 mt-2">

                A backup of the current database will be created before restoration.

            </p>

        </div>



        <form method="POST" action="{{ route('backup.restore.execute') }}">

    @csrf


    <input type="hidden"
           name="backup_path"
           value="{{ $result['backup_path'] ?? '' }}">


    <input type="hidden"
           name="database"
           value="{{ $result['database'] ?? '' }}">


    <input type="hidden"
           name="server"
           value="{{ $result['server'] ?? '' }}">


    <input type="hidden"
           name="size"
           value="{{ $result['size'] ?? '' }}">


    <input type="hidden"
           name="tables"
           value="{{ $result['tables'] ?? '' }}">


    <input type="hidden"
           name="records"
           value="{{ $result['records'] ?? '' }}">



    <button
        type="submit"
        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md">

        Start Database Restore

    </button>


    <a href="{{ route('backup.index') }}"
       class="ml-4 bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md">

        Cancel

    </a>


</form>



    </div>


</div>


@endsection