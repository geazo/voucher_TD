@extends('layouts.appF')

@section('content')
    <form method="POST" action="{{ url('/') }}" class="max-w-xl p-6 mx-auto mt-10 bg-white rounded-lg shadow">
        @csrf

        <h2 class="mb-6 text-2xl font-bold text-center text-blue-600">Registration Form Customer</h2>

        <div class="mb-4">
            <label for="name" class="block mb-1 font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div class="mb-4">
            <label for="email" class="block mb-1 font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div class="mb-4">
            <label for="phone" class="block mb-1 font-medium text-gray-700">Whatsapp</label>
            <input type="text" name="phone" id="phone" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div class="mb-6">
            <label for="outlet_id" class="block mb-1 font-medium text-gray-700">Outlets Name</label>
            <select name="outlet_id" id="outlet_id" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="" disabled selected>-- Choose Outlet --</option>
                @foreach ($outlets as $outlet)
                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                @endforeach
            </select>
        </div>


        <div class="mb-4">
            <label for="pin" class="block mb-1 font-medium text-gray-700">PIN</label>
            <input type="text" name="pin" id="pin" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <button type="submit"
            class="w-full py-2 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">Register</button>
    </form>
@endsection
