<?php

use App\Models\Attendance;
use App\Models\SalaryGrade;
use App\Models\Seminar;
use Carbon\Carbon;
use Illuminate\Support\Str;



if (!function_exists('createActivity')) {
    function createActivity($type, $message, $ip_address = null, $model = null, $request = null)
    {
        try {
            $updated_from = null;
            $updated_to = null;

            if ($model) {
                // get the only updated fields
                $updated_from = $model->getOriginal();
                $updated_to = $request->all();
                // to json
                $updated_from = json_encode($updated_from);
                $updated_to = json_encode($updated_to);
            }

            auth()->user()->activities()->create([
                'type' => $type,
                'message' => $message,
                'ip_address' => $ip_address,
                'updated_from' => $updated_from,
                'updated_to' => $updated_to,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

if (!function_exists('toCode')) {
    function toCode($text)
    {
        $words = preg_split("/[\s,]+/", $text);
        $code = "";
        $excludedWords = ["of", "the", "is", "it", "and"]; // Add any words you want to exclude here

        foreach ($words as $w) {
            if (!in_array(strtolower($w), $excludedWords)) {
                $code .= Str::upper($w[0]);
            }
        }

        return $code;
    }
}
if (!function_exists('percentage')) {
    function percentage($number)
    {
        return $number . '%';
    }
}
if (!function_exists('money')) {
    function money($number, $currency = 'PHP')
    {
        $currency = Str::upper($currency);
        return $currency . ' ' . $number;
    }
}
if (!function_exists('computePercentage')) {
    function computePercentage($number)
    {
        return $number . '%';
    }
}
if (!function_exists('getLoan')) {
    function getLoan($loan, $duration)
    {
        return $loan / ($duration * 2);
    }
}

if (!function_exists('getSalaryGradesTotalSteps')) {

    function getSalaryGradesTotalSteps()
    {
        $total_steps = 0;
        $temp = 0;
        foreach (SalaryGrade::all() as $key => $salary_grade) {
            $temp =  count($salary_grade->steps);
            if ($temp > $total_steps) {
                $total_steps = $temp;
            }
            $temp =  $total_steps;
        }

        return $total_steps;
    }
}

if (!function_exists('attendanceCount')) {

    function attendanceCount($employee, $payroll, $from, $to)
    {
        $month = date('m', strtotime($payroll['month']));
        $year = date('Y', strtotime($payroll['year']));
        $lastDayOfTheMonth = $to;
        if (!checkdate($month, $to, $year)) {
            $lastDayOfTheMonth = date('t', mktime(0, 0, 0, $month, 1, $year)); // get last day of the month
        }

        $total_man_hour = 0;
        $present = 0;
        $absent = 0;
        $late = 0;
        $underTime = 0;
        $attendances = [];

        $loopStart = ($to == 15) ? 1 : 16;
        $loopEnd = ($to == 15) ? 15 : $lastDayOfTheMonth;

        $from = sprintf('%04d-%02d-%02d', $year, $month, $from);
        // $to = sprintf('%04d-%02d-%02d', $year, $month, $to);
        // $from = sprintf('%04d-%02d-%02d', $year, $month, $from);
        $to = sprintf('%04d-%02d-%02d', $year, $month, $loopEnd);


        $from = Carbon::parse($from)->format('Y-m-d');
        $to = Carbon::parse($to)->format('Y-m-d');

        for ($i = $loopStart; $i <= $loopEnd; $i++) {
            $day = str_pad($i, 2, '0', STR_PAD_LEFT);
            $date = Carbon::parse(sprintf('%04d-%02d-%02d', $year, $month, $day))->format('Y-m-d');
            $attendance = $employee->attendances()
                ->whereDate('time_in', $date)
                ->where('isPresent', 1)
                ->first();
            // Consider weekends and employee category
            $isWeekend = (Carbon::parse($payroll['month'] . '-' . $day))->isWeekend();
            if ($attendance) {
                $timeIn = Carbon::parse($attendance->time_in);
                $manhours = $attendance->hours;
                $timeInInterval = '';
                $timeOutInterval ='';

                // Define time in interval based on different scenarios
                if ($timeIn->between(Carbon::parse('6:59'), Carbon::parse('7:11'))) {
                    $timeInInterval = Carbon::parse('7:00');
                } elseif ($timeIn->between(Carbon::parse('7:11'), Carbon::parse('7:40'))) {
                    $timeInInterval = Carbon::parse('7:30');
                }elseif ($timeIn->between(Carbon::parse('7:40'), Carbon::parse('8:11'))) {
                    $timeInInterval = Carbon::parse('8:00');
                } else {
                    $timeInInterval =  Carbon::parse('8:00');
                }

                if ($timeIn->between(Carbon::parse('15:00'), Carbon::parse('15:10'))) {
                    $timeOutInterval = Carbon::parse('15:00');
                } elseif ($timeIn->between(Carbon::parse('15:11'), Carbon::parse('15:40'))) {
                    $timeOutInterval = Carbon::parse('15:30');
                }elseif ($timeIn->between(Carbon::parse('15:40'), Carbon::parse('16:11'))) {
                    $timeOutInterval = Carbon::parse('16:00');
                }elseif ($timeIn->between(Carbon::parse('16:11'), Carbon::parse('16:40'))) {
                    $timeOutInterval = Carbon::parse('16:30');
                } else {
                    $timeOutInterval = Carbon::parse('17:00');
                }

                if ($isWeekend && $employee->data->category->category_code !== 'JO') {
                    $attendances[$i] = [
                        'day' => date('d', strtotime($date)) . '-' . Str::substr(date('l', strtotime($date)), 0, 3),
                        'time_in' => '-----',
                        'time_in_interval' => '-----',
                        'time_out' => '-----',
                        'time_out_interval' => '-----',
                        'manhours' => 0, // No manhours for weekends (except JO)
                    ];
                } else {
                    if ($attendance->time_in_status == 'Late') {
                        $late++;
                    }
                    if ($attendance->time_out_status == 'Under-time') {
                        $underTime++;
                    }
                    $attendances[$i] = [
                        'day' => date('d', strtotime($date)) . '-' . Str::substr(date('l', strtotime($date)), 0, 3),
                        'time_in' => $attendance->time_in,
                        'time_in_interval' => $timeInInterval,
                        'time_out' => $attendance->time_out,
                        'time_out_interval' => $timeOutInterval,
                        'deduction' => $attendance->time_in_deduction + $attendance->time_out_deduction,
                        'manhours' => $manhours,
                    ];
                    $total_man_hour += $manhours;
                    $present++;
                }
            } else {
                if (!$isWeekend || $employee->category == 'JO') {
                    $absent++;
                }
                // Absent day details
                $attendances[$i] = [
                    'day' => date('d', strtotime($date)) . '-' . Str::substr(date('l', strtotime($date)), 0, 3),
                    'time_in' => '',
                    'time_in_interval' => '',
                    'time_out' => '',
                    'time_out_interval' => '',
                    'manhours' => '',
                ];
            }
        }

        return [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'under_time' => $underTime,
            'total_man_hour' => $total_man_hour,
            'attendances' => $attendances,
        ];
    }
}
if (!function_exists('calculateSalary')) {

    function calculateSalary($salaryGrade, $employee, $attendance, $timeIn, $timeOut, $currentTime, $isJO)
    {
        // Default working days and hours
        $workingDays = 15;
        $requiredHoursWork = 8;
        $deduction = 0;
        $hourWorked = 0;
        $sickLeave = 0;

        // Carbon instances for attendance and defaults
        $attendanceTimeIn = Carbon::parse(date('H:i:s', strtotime($attendance->time_in)));
        $attendanceTimeOut = Carbon::parse(date('H:i:s', strtotime($currentTime)));
        $formattedTimeIn = $attendanceTimeIn->copy()->format('H:i:s');
        $formattedTimeout = $attendanceTimeOut->copy()->format('H:i:s');

        $defaultTimeIn = Carbon::parse($timeIn);
        $defaultTimeOut = Carbon::parse($timeOut);
        $formattedDefaultTimeIn = $defaultTimeIn->copy()->format('H:i:s');
        $formattedDefaultTimeOut = $defaultTimeOut->copy()->format('H:i:s');
        // Calculate hours worked, handling negative values and exceeding 8 hours
        if ($formattedTimeIn <= $formattedDefaultTimeIn) {
            // early 8am
            if ($formattedTimeout <= $formattedDefaultTimeOut) {
                // undertime
                if ($isJO || $employee->data->category->category_code == "COS") {
                    $status = 'Half-Day';
                } else {
                    $status = 'Under-time';
                }
                $hourWorked = $defaultTimeIn->diffInHours($attendanceTimeOut, true) - 1;
            } else {
                $hourWorked = $defaultTimeIn->diffInHours($defaultTimeOut, true) - 1;
                $status = 'Time-out';
            }
        } else {
            // lates
            if ($formattedTimeout <= $formattedDefaultTimeOut) {
                // undertime
                if ($isJO || $employee->data->category->category_code == "COS") {
                    $status = 'Half-Day';
                } else {
                    $status = 'Under-time';
                }
                $hourWorked = $attendanceTimeIn->diffInHours($attendanceTimeOut, true) - 1;
            } else {
                $hourWorked = $attendanceTimeIn->diffInHours($defaultTimeOut, true) - 1;
                $status = 'Time-out';
            }
        }

        if ($hourWorked > $requiredHoursWork) {
            $hourWorked =  8;
        }
        if ($hourWorked < 0) {
            $hourWorked =  0;
        }
        // Calculate minutes late
        $minutesLate = $attendanceTimeIn->diffInMinutes($attendanceTimeIn);
        if (!$isJO) {
            $salaryPerHour = ($salaryGrade / 22) / $requiredHoursWork;

            if ($attendance->time_in_status === 'Late') {
                // Sick leave handling (requires a `computeSickLeave` function)
                $sickLeave = $employee->data->sick_leave_points;
                if ($sickLeave > 0) {
                    $sickLeave = computeSickLeave($sickLeave, $minutesLate);
                }
            }
        }



        if (!$isJO && $formattedTimeout < $formattedDefaultTimeOut) {
            $notWorkedHour = $defaultTimeOut->diffInHours($attendanceTimeOut);
            $salaryPerHour = $salaryPerHour - $notWorkedHour;
            $deduction = .125;
            $sickLeave = $sickLeave - ($notWorkedHour * $deduction);
            if ($sickLeave < 0) {
                $sickLeave = 0;
            }
        }

        // Calculate total salary for the day (applicable only for non-JO employees)
        if (!$isJO) {
            $totalSalaryForToday = (($salaryPerHour * $hourWorked) < 0) ? 0 : ($salaryPerHour * $hourWorked);
            if ($attendance->time_in_status === 'Late' || ($status === 'Half-Day' || $status === 'Under-time')) {
                $totalSalaryForToday = $totalSalaryForToday - ($sickLeave === 0) ? getLateByMinutes($minutesLate) : 0;
                $employee->data->update(['sick_leave_points' => $sickLeave]);
            }
        } else {
            if ($attendance->time_in_status === 'Half-Day') {
                $totalSalaryForToday = $salaryGrade / 2;
            } else {
                $totalSalaryForToday = $salaryGrade;
            }
        }

        return [
            'salary' => $totalSalaryForToday,
            'status' => $status,
            'hour_worked' => $hourWorked,
            'deduction' => $deduction,
        ];
    }
}
if (!function_exists('computeSickLeave')) {

    function computeSickLeave($sick_leave, $minute_late)
    {
        $sick_leave_left = 0;

        // Compute the sick leave deduction per minute
        $sick_leave_left = $sick_leave - getLateByMinutes($minute_late);

        // check if sick_leave_left is less than 0
        if ($sick_leave_left < 0) {
            $sick_leave_left = 0;
        }

        return $sick_leave_left;
    }
}
if (!function_exists('getLateByMinutes')) {

    function getLateByMinutes($minute_late)
    {
        $maxMinutesPerHour = 60;
        $equivalentPerMinute = 0.025; // Equivalent per minute based on your logic (0.125 for whole hour)

        $hourLate = (int) floor($minute_late / $maxMinutesPerHour);
        $remainingMinutes = $minute_late % $maxMinutesPerHour;

        $equivalent = $hourLate * $equivalentPerMinute;
        if ($remainingMinutes > 0) {
            $equivalent += $equivalentPerMinute * $remainingMinutes;
        }

        return $equivalent;
    }
}
if (!function_exists('getTotalSalaryBy')) {

    function getTotalSalaryBy(string $filter)
    {
        $totalSalaries = 0;
        $attendances = Attendance::with('employee')->get();
        $totalSalary = [];

        if ($filter == 'year') {
            // Group attendances by year
            $attendancesByYear = $attendances->groupBy(function ($attendance) {
                return $attendance->created_at->format('Y');
            });

            foreach ($attendancesByYear as $year => $attendances) {
                $total = $attendances->sum('salary');
                $totalSalaries += $total;
                $totalSalary[] = [
                    'year' => $year,
                    'total' => $total,
                ];
            }
        } else {
            // Group attendances by month
            $attendancesByMonth = $attendances->groupBy(function ($attendance) {
                return $attendance->created_at->format('F');
            });

            foreach ($attendancesByMonth as $month => $attendances) {
                $total = $attendances->sum('salary');
                $totalSalaries += $total;
                $totalSalary[] = [
                    'month' => $month,
                    'total' => $total,
                ];
            }
        }

        return collect($totalSalary);
    }
}
if (!function_exists('getTotalSalaryTest')) {

    function getTotalSalaryTest(string $filter)
    {
        $totalSalary = [];

        if ($filter == 'year') {

            for ($i = 1; $i <= 7; $i++) {
                $totalSalary[] = [
                    'year' => "202{$i}",
                    'total' => random_int(1, 100),
                ];
            }
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $totalSalary[] = [
                    'month' => $i,
                    'total' => random_int(1, 100),
                ];
            }
        }

        return collect($totalSalary);
    }
}
if (!function_exists('countAttendanceBy')) {

    function countAttendanceBy(string $filter, ?int $value = null)
    {
        $attendances = Attendance::query();
        $seminar = Seminar::query();

        if ($filter == 'year') {
            $attendances->whereYear('created_at', $value);
        }
        $seminar->whereYear('date', $value);
        if ($filter == 'month') {
            $attendances->whereMonth('created_at', $value);
            $seminar->whereMonth('date', $value);
        }

        $attendancesData = $attendances->get();
        $seminarCount = $seminar->count();

        $present = -$attendancesData->where('isPresent', 1)->count();
        $absent = -$attendancesData->whereNull('time_out')->where('isPresent', 0)->count();
        $late = -$attendancesData->where('time_in_status', 'Late')->where('isPresent', 1)->count();
        $under_time = -$attendancesData->where('time_out_status', 'Under-time')->where('isPresent', 1)->count();
        $half_day = -$attendancesData->where('time_in_status', 'Half-Day')->where('isPresent', 1)->count();



        return collect([
            [
                'label' =>  'Present',
                'count' => $present
            ],
            [
                'label' =>  'Absent',
                'count' => $absent
            ],
            [
                'label' =>  'Late',
                'count' => $late
            ],
            [
                'label' =>  'Under Time',
                'count' => $under_time
            ],
            [
                'label' =>  'Half Day',
                'count' => $half_day
            ],
            [
                'label' =>  'Seminar',
                'count' => $seminarCount
            ],

        ]);
    }
}
if (!function_exists('countAttendancesTest')) {

    function countAttendancesTest()
    {

        return collect([
            [
                'label' =>  'Present',
                'count' => random_int(1, 100)
            ],
            [
                'label' =>  'Absent',
                'count' => random_int(1, 100)
            ],
            [
                'label' =>  'Late',
                'count' => random_int(1, 100)
            ],
            [
                'label' =>  'Under Time',
                'count' => random_int(1, 100)
            ],
            [
                'label' =>  'Half Day',
                'count' => random_int(1, 100)
            ],
            [
                'label' =>  'Seminar',
                'count' => random_int(1, 100)
            ],

        ]);
    }
}
if (!function_exists('getTotalSalaryDepartment')) {

    function getTotalSalaryDepartment($employeesData, $filterBy, $filter)
    {
        $totalSalary = 0;
        if (!empty($employeesData)) {
            foreach ($employeesData as $key => $employeeData) {
                if ($employeeData->employee->attendances) {
                    $attendances = $employeeData->employee->attendances()
                        ->whereYear('time_in', $filter)
                        ->get();
                    if ($filterBy == 'month') {
                        $attendances = $employeeData->employee->attendances()
                            ->whereMonth('time_in', $filter)
                            ->get();
                    }

                    // Sum up the allowance amounts
                    foreach ($attendances as $attendance) {
                        $totalSalary += $attendance->salary;
                    }
                }
            }
        }

        return $totalSalary;
    }
}
