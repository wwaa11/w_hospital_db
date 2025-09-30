<?php
namespace App\Http\Controllers;

use DateTime;

class HelperController extends Controller
{
    /**
     * Generate full name from first and last name
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    public static function FullName($firstName, $lastName)
    {
        mb_internal_encoding('UTF-8');
        $setname = mb_substr($firstName, 1);
        $setlast = mb_substr($lastName, 1);
        if (str_contains($setname, '\\')) {
            $setname = explode("\\", $setname);
            $setname = $setname[0];
        }
        $name = $setname . " " . $setlast;

        return $name;
    }

    /**
     * Calculate age as integer from birth date
     *
     * @param string $birthDate
     * @return int
     */
    public static function setAgeInterger($birthDate)
    {
        $now      = new DateTime();
        $date     = new DateTime($birthDate);
        $interval = $now->diff($date);
        $year     = round($interval->y . '.' . $interval->m, 0);

        return $year;
    }

    /**
     * Calculate age in years, months, and days format
     *
     * @param string $dateInput
     * @return string
     */
    public static function setAge($dateInput)
    {
        $date     = new DateTime($dateInput);
        $now      = new DateTime();
        $interval = $now->diff($date);
        $output   = $interval->y . ' Y ' . $interval->m . ' M ' . $interval->d . ' D';

        return $output;
    }

    /**
     * Get doctor name from doctor data collection
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $code
     * @return string|null
     */
    public static function DoctorName($data, $code)
    {
        $doctor = collect($data)->where('Doctor', $code)->first();
        if ($doctor !== null) {
            mb_internal_encoding('UTF-8');
            $name = mb_substr($doctor->LocalName, 1);
            if (str_contains($name, '\\')) {
                $temp = explode("\\", $name);
                $name = $temp[1] . $temp[0];
            }
        } else {
            $name = null;
        }

        return $name;
    }

    /**
     * Get clinic name from clinic data collection
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $code
     * @return string|null
     */
    public static function ClinicName($data, $code)
    {
        $clinic = collect($data)->where('Code', $code)->first();
        if ($clinic !== null) {
            mb_internal_encoding('UTF-8');
            $name = mb_substr($clinic->LocalName, 1);
        } else {
            $name = null;
        }
        return $name;
    }

    /**
     * Format date according to specified format
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function SetDate($date, $format)
    {
        // https://www.php.net/manual/en/datetime.format.php
        $date = strtotime($date);

        return date($format, $date);
    }

    /**
     * Format date to full Thai or English format
     *
     * @param string $dateInput
     * @return string
     */
    public static function setFullDate($dateInput)
    {
        $nation = 'THA';
        $date   = explode('-', $dateInput);
        $month  = $date[1];
        if ($nation == 'THA') {
            $year   = $date[0] + 543;
            $months = [
                "01" => "มกราคม",
                "02" => "กุมภาพันธ์",
                "03" => "มีนาคม",
                "04" => "เมษายน",
                "05" => "พฤษภาคม",
                "06" => "มิถุนายน",
                "07" => "กรกฎาคม",
                "08" => "สิงหาคม",
                "09" => "กันยายน",
                "10" => "ตุลาคม",
                "11" => "พฤศจิกายน",
                "12" => "ธันวาคม",
            ];
            $fullmonth = $months[$month] ?? '';
        } else {
            $year   = $date[0];
            $months = [
                "01" => "January",
                "02" => "February",
                "03" => "March",
                "04" => "April",
                "05" => "May",
                "06" => "June",
                "07" => "July",
                "08" => "August",
                "09" => "September",
                "10" => "October",
                "11" => "November",
                "12" => "December",
            ];
            $fullmonth = $months[$month] ?? '';
        }
        $date = substr($date[2], 0, 2) . " " . $fullmonth . " " . $year;

        return $date;
    }
}
