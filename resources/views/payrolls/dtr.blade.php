<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $employee->name }} - Attendance from {{ date('F', strtotime($payroll['month'])) }} {{ $payroll['date_from_to'] }}, {{ $payroll['year'] }}
            </h2>
    </x-slot>

    <div class="grid grid-cols-1 gap-3 mt-4 md:grid-cols-2 xl:grid-cols-6">

        <!-- Department List Card -->
        <a href="#">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="mb-2 text-lg font-semibold text-center">No. of Times Presents</h2>
                    <!-- Card content here -->
                    <h5 class="text-3xl font-bold text-center">{{ $presents }}</h5>
                </div>
            </div>
        </a>
        <a href="#">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="mb-2 text-lg font-semibold text-center">No. of Times Absents</h2>
                    <!-- Card content here -->
                    <h5 class="text-3xl font-bold text-center">{{ $absents }}</h5>
                </div>
            </div>
        </a>
        <a href="#">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="mb-2 text-lg font-semibold text-center">No. of Late</h2>
                    <!-- Card content here -->
                    <h5 class="text-3xl font-bold text-center">{{ $lates }}</h5>
                </div>
            </div>
        </a>
        <a href="#">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="mb-2 text-lg font-semibold text-center">No. of Under-time</h2>
                    <!-- Card content here -->
                    <h5 class="text-3xl font-bold text-center">{{ $undertimes }}</h5>
                </div>
            </div>
        </a>
        <a href="#">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="mb-2 text-lg font-semibold text-center">Man hours</h2>
                    <!-- Card content here -->
                    <h5 class="text-3xl font-bold text-center">{{ $hours }}</h5>
                </div>
            </div>
        </a>


    </div>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between space-x-2 mb-3 mx-5">
                <div class="relative ">

                </div>
                <div class="relative ">
                    <a href="{{ route('payrolls.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                       Back to Payroll
                    </a>
                </div>
            </div>
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-full bg-white border data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left border-b">#</th>
                                <th class="px-4 py-2 text-left border-b">Date</th>
                                <th class="px-4 py-2 text-left border-b">Time In</th>
                                <th class="px-4 py-2 text-left border-b">Status</th>
                                <th class="px-4 py-2 border-b">Time Out</th>
                                <th class="px-4 py-2 text-left border-b">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendances as $attendance)
                                <tr>
                                    <td class="px-4 py-2 border-b">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2 border-b">{{ date('F d,Y', strtotime($attendance->created_at)) }}</td>
                                    <td class="px-4 py-2 border-b">
                                        {{ date('h:i:s A', strtotime($attendance->time_in)) }}</td>
                                        <td class="px-4 py-2 border-b">{{ $attendance->time_in_status }}</td>
                                    <td class="px-4 py-2 border-b">
                                        {{ $attendance->time_out ? date('h:i:s A', strtotime($attendance->time_out)) : '' }}
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $attendance->time_out_status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
