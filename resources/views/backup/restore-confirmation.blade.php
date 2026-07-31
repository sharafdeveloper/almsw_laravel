@extends('layouts.admin')

@section('title', 'Confirm Restore')

@section('content')

<div class="py-6 px-6">

    <div class="bg-white rounded-xl shadow-md p-6 border">

        <h1 class="text-xl font-bold text-red-600">
            Confirm Database Restore
        </h1>


        <p class="mt-3 text-gray-700">

            You are about to restore this database backup.

            This action will replace the current database.

        </p>


        <hr class="my-4">


        <h3 class="font-semibold text-gray-800 text-lg">
            Backup Information
        </h3>


        <div class="mt-4">


            <p class="text-gray-800 mb-2">
                <strong>Database:</strong>
                {{ $result['database'] }}
            </p>


            <p class="text-gray-800 mb-2">
                <strong>Server:</strong>
                {{ $result['server'] }}
            </p>


            <p class="text-gray-800 mb-2">
                <strong>Backup Size:</strong>
                {{ $result['size'] }}
            </p>


            <p class="text-gray-800 mb-2">
                <strong>Total Tables:</strong>
                {{ $result['tables'] }}
            </p>


            <p class="text-gray-800 mb-2">
                <strong>Records:</strong>
                {{ $result['records'] }}
            </p>


        </div>



        <div class="mt-6 bg-red-100 border border-red-300 p-4 rounded-lg">

            <strong class="text-red-700">
                Warning:
            </strong>


            <p class="text-red-700 mt-2">

                The current database will be replaced.
                Make sure you have a backup before continuing.

            </p>

        </div>



        <!-- Buttons -->

        <div class="mt-6 flex gap-4 items-center">


            <!-- Continue Restore -->

            <form method="POST" action="{{ route('backup.restore.ready') }}">

                @csrf


                <!-- Send validation data to next step -->

                <input type="hidden"name="backup_path"
                    value="{{ $result['backup_path'] }}">
                
                
                <input type="hidden" 
                name="database" 
                value="{{ $result['database'] }}">


                <input type="hidden" 
                name="server" 
                value="{{ $result['server'] }}">


                <input type="hidden" 
                name="size" 
                value="{{ $result['size'] }}">


                <input type="hidden" 
                name="tables" 
                value="{{ $result['tables'] }}">


                <input type="hidden" 
                name="records" 
                value="{{ $result['records'] }}">



                <button 
                    type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">

                    Continue Restore

                </button>


            </form>




            <!-- Cancel Button -->

            <a href="{{ route('backup.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">

                Cancel

            </a>


        </div>


    </div>


</div>


@endsection