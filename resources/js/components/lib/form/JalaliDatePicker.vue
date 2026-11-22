<template>

    <div
        ref="datepickerRef"
        class="datepicker"
        :class="{ 'is-open': open }"
        dir="rtl"
    >

        <!-- Input -->
        <button
            type="button"
            class="input"
            @click="toggle"
        >

            <span
                class="input-value"
                :class="{ placeholder: !displayValue }"
            >
                {{ displayValue || placeholder }}
            </span>

            <span
                v-if="clearable && modelValue"
                class="clear-button"
                @click.stop="clear"
            >
                ×
            </span>

        </button>


        <!-- Popup -->
        <Transition name="datepicker">

            <div
                v-if="open"
                class="calendar"
            >

                <!-- TIME ONLY -->
                <template v-if="effectiveMode === 'time'">

                    <div class="time-only">

                        <div class="time-icon">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />

                                <path
                                    d="M12 7v5l3 2"
                                />
                            </svg>

                        </div>

                        <div class="time-title">
                            انتخاب زمان
                        </div>

                        <div
                            class="time-fields"
                            dir="ltr"
                        >

                            <!-- Hour -->
                            <div class="time-field">

                                <span>
                                    ساعت
                                </span>

                                <select
                                    v-model.number="selectedHour"
                                >

                                    <option
                                        v-for="hour in 24"
                                        :key="hour - 1"
                                        :value="hour - 1"
                                    >
                                        {{
                                            toPersianNumber(
                                                String(hour - 1).padStart(2, '0')
                                            )
                                        }}
                                    </option>

                                </select>

                            </div>


                            <span class="time-separator">
                                :
                            </span>


                            <!-- Minute -->
                            <div class="time-field">

                                <span>
                                    دقیقه
                                </span>

                                <select
                                    v-model.number="selectedMinute"
                                >

                                    <option
                                        v-for="minute in 60"
                                        :key="minute - 1"
                                        :value="minute - 1"
                                    >
                                        {{
                                            toPersianNumber(
                                                String(minute - 1).padStart(2, '0')
                                            )
                                        }}
                                    </option>

                                </select>

                            </div>


                            <!-- Second -->
                            <template v-if="enableSeconds">

                                <span class="time-separator">
                                    :
                                </span>

                                <div class="time-field">

                                    <span>
                                        ثانیه
                                    </span>

                                    <select
                                        v-model.number="selectedSecond"
                                    >

                                        <option
                                            v-for="second in 60"
                                            :key="second - 1"
                                            :value="second - 1"
                                        >
                                            {{
                                                toPersianNumber(
                                                    String(second - 1).padStart(2, '0')
                                                )
                                            }}
                                        </option>

                                    </select>

                                </div>

                            </template>

                        </div>


                        <button
                            type="button"
                            class="confirm-button"
                            @click="confirmTime"
                        >
                            تأیید زمان
                        </button>

                    </div>

                </template>


                <!-- DATE / DATETIME -->
                <template v-else>

                    <!-- Header -->
                    <div class="calendar-header">

                        <button
                            type="button"
                            class="nav-button"
                            :disabled="!canGoPrevious"
                            @click="previousMonth"
                        >
                            ‹
                        </button>


                        <div class="selectors">

                            <!-- Month -->
                            <div
                                class="custom-select month-select"
                                :class="{ open: monthPickerOpen }"
                            >

                                <button
                                    type="button"
                                    class="custom-select-trigger"
                                    @click.stop="toggleMonthPicker"
                                >

                                    <span>
                                        {{ months[currentMonth - 1] }}
                                    </span>

                                    <svg
                                        class="custom-select-arrow"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M7 10l5 5 5-5"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                </button>


                                <Transition name="picker-dropdown">

                                    <div
                                        v-if="monthPickerOpen"
                                        class="custom-select-menu month-menu"
                                        @click.stop
                                    >

                                        <div class="custom-select-title">
                                            انتخاب ماه
                                        </div>


                                        <div class="month-grid">

                                            <button
                                                v-for="(month, index) in months"
                                                :key="index"
                                                type="button"
                                                class="month-option"
                                                :class="{
                                                    selected:
                                                        currentMonth === index + 1
                                                }"
                                                :disabled="
                                                    isMonthDisabled(index + 1)
                                                "
                                                @click="
                                                    selectMonth(index + 1)
                                                "
                                            >
                                                {{ month }}
                                            </button>

                                        </div>

                                    </div>

                                </Transition>

                            </div>


                            <!-- Year -->
                            <select
                                v-model.number="currentYear"
                                class="year-select"
                            >

                                <option
                                    v-for="year in years"
                                    :key="year"
                                    :value="year"
                                    :disabled="isYearDisabled(year)"
                                >
                                    {{ year }}
                                </option>

                            </select>

                        </div>


                        <button
                            type="button"
                            class="nav-button"
                            :disabled="!canGoNext"
                            @click="nextMonth"
                        >
                            ›
                        </button>

                    </div>


                    <!-- Today -->
                    <button
                        type="button"
                        class="today-button"
                        :disabled="isDateDisabled(todayItem)"
                        @click="selectToday"
                    >
                        امروز
                    </button>


                    <!-- Discount legend -->
                    <div
                        v-if="highlight"
                        class="discount-legend"
                    >
                        <span class="discount-legend-dot"></span>

                        روزهای تخفیف‌دار
                    </div>


                    <!-- Weekdays -->
                    <div class="weekdays">

                        <span
                            v-for="day in weekDays"
                            :key="day"
                        >
                            {{ day }}
                        </span>

                    </div>


                    <!-- Days -->
                    <div class="days">

                        <button
                            v-for="item in calendarDays"
                            :key="`${item.year}-${item.month}-${item.day}`"
                            type="button"
                            :disabled="isDateDisabled(item)"
                            :class="{
                                muted: item.type !== 'current',
                                selected: isSelected(item),
                                today: isToday(item),
                                disabled: isDateDisabled(item),
                                discount: isDiscountDay(item),
                                'discount-start': isDiscountEdge(item, 'start'),
                                'discount-end': isDiscountEdge(item, 'end')
                            }"
                            @click="selectDate(item)"
                        >
                            <span class="day-number">
                                {{ toPersianNumber(item.day) }}
                            </span>

                            <small
                                v-if="showDayPercent(item)"
                                class="day-discount"
                                :class="discountSizeClass(item)"
                            >
                                {{ dayPercent(item) }}
                            </small>
                        </button>

                    </div>


                    <!-- DATETIME -->
                    <div
                        v-if="effectiveMode === 'datetime'"
                        class="time-picker"
                    >

                        <div class="time-picker-title">
                            ساعت
                        </div>


                        <div
                            class="time-fields compact"
                            dir="ltr"
                        >

                            <select
                                v-model.number="selectedHour"
                            >

                                <option
                                    v-for="hour in 24"
                                    :key="hour - 1"
                                    :value="hour - 1"
                                >
                                    {{
                                        toPersianNumber(
                                            String(hour - 1).padStart(2, '0')
                                        )
                                    }}
                                </option>

                            </select>


                            <span>
                                :
                            </span>


                            <select
                                v-model.number="selectedMinute"
                            >

                                <option
                                    v-for="minute in 60"
                                    :key="minute - 1"
                                    :value="minute - 1"
                                >
                                    {{
                                        toPersianNumber(
                                            String(minute - 1).padStart(2, '0')
                                        )
                                    }}
                                </option>

                            </select>


                            <template v-if="enableSeconds">

                                <span>
                                    :
                                </span>

                                <select
                                    v-model.number="selectedSecond"
                                >

                                    <option
                                        v-for="second in 60"
                                        :key="second - 1"
                                        :value="second - 1"
                                    >
                                        {{
                                            toPersianNumber(
                                                String(second - 1).padStart(2, '0')
                                            )
                                        }}
                                    </option>

                                </select>

                            </template>

                        </div>


                        <button
                            type="button"
                            class="confirm-button"
                            @click="confirmDateTime"
                        >
                            تأیید تاریخ و زمان
                        </button>

                    </div>

                </template>

            </div>

        </Transition>

    </div>

</template>


<script setup>

import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch
} from 'vue'

import {
    toJalaali,
    toGregorian,
    isValidJalaaliDate
} from 'jalaali-js'


dayjs.extend(utc)
dayjs.extend(timezone)


/*
|--------------------------------------------------------------------------
| Config
|--------------------------------------------------------------------------
*/

const APP_TIMEZONE = 'Asia/Tehran'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    modelValue: {
        type: String,
        default: ''
    },

    mode: {
        type: String,
        default: 'date',

        validator: value =>
            ['date', 'time', 'datetime'].includes(value)
    },

    output: {
        type: String,
        default: 'jalali',

        validator: value =>
            ['jalali', 'gregorian'].includes(value)
    },

    placeholder: {
        type: String,
        default: 'انتخاب تاریخ'
    },

    clearable: {
        type: Boolean,
        default: true
    },

    inputFormat: {
        type: String,
        default: 'jYYYY-jMM-jDD'
    },

    displayFormat: {
        type: String,
        default: ''
    },

    enableTime: {
        type: Boolean,
        default: false
    },

    enableSeconds: {
        type: Boolean,
        default: false
    },

    /*
    |--------------------------------------------------------------------------
    | Reservation limits
    |--------------------------------------------------------------------------
    */

    disablePast: {
        type: Boolean,
        default: false
    },

    minDate: {
        type: String,
        default: ''
    },

    maxDate: {
        type: String,
        default: ''
    }

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'update:modelValue',
    'change'
])


/*
|--------------------------------------------------------------------------
| Effective Mode
|--------------------------------------------------------------------------
*/

const effectiveMode = computed(() =>
{

    if (props.mode !== 'date')
    {
        return props.mode
    }

    if (props.enableTime)
    {
        return 'datetime'
    }

    return 'date'

})


/*
|--------------------------------------------------------------------------
| Calendar
|--------------------------------------------------------------------------
*/

const months = [
    'فروردین',
    'اردیبهشت',
    'خرداد',
    'تیر',
    'مرداد',
    'شهریور',
    'مهر',
    'آبان',
    'آذر',
    'دی',
    'بهمن',
    'اسفند'
]


const weekDays = [
    'ش',
    'ی',
    'د',
    'س',
    'چ',
    'پ',
    'ج'
]


/*
|--------------------------------------------------------------------------
| Persian Number
|--------------------------------------------------------------------------
*/
function discountSizeClass(item)
{
    const percent = Number(findDiscountRange(item)?.percent || 0)

    if (percent >= 50)
    {
        return 'discount-xl'
    }
    if (percent >= 30)
    {
        return 'discount-lg'
    }
    if (percent >= 15)
    {
        return 'discount-md'
    }

    return 'discount-sm'
}

function toPersianNumber(value)
{
    return String(value).replace(
        /\d/g,
        digit => '۰۱۲۳۴۵۶۷۸۹'[digit]
    )
}


/*
|--------------------------------------------------------------------------
| Tehran Now
|--------------------------------------------------------------------------
*/

function getNow()
{
    return dayjs().tz(
        APP_TIMEZONE
    )
}


function getToday()
{
    const now = getNow()

    return toJalaali(
        now.year(),
        now.month() + 1,
        now.date()
    )
}


const today = getToday()


const todayItem = computed(() => ({
    year: today.jy,
    month: today.jm,
    day: today.jd
}))


/*
|--------------------------------------------------------------------------
| Date Compare
|--------------------------------------------------------------------------
*/

function compareJalaliDate(a, b)
{
    if (a.jy !== b.jy)
    {
        return a.jy - b.jy
    }

    if (a.jm !== b.jm)
    {
        return a.jm - b.jm
    }

    return a.jd - b.jd
}


/*
|--------------------------------------------------------------------------
| Parse Limit Date
|--------------------------------------------------------------------------
|
| اگر output = jalali:
| 1405-07-10
|
| اگر output = gregorian:
| 2026-10-02
| 2026-10-02T00:00:00.000Z
|
*/

function parseLimitDate(value)
{
    if (!value)
    {
        return null
    }

    const raw = String(value).trim().toLowerCase()

    // today
    if (raw === 'today')
    {
        const now = getNow()

        const {jy, jm, jd} = toJalaali(
            now.year(),
            now.month() + 1,
            now.date()
        )

        return {
            jy,
            jm,
            jd
        }
    }

    // Gregorian
    if (props.output === 'gregorian')
    {
        const datePart = raw.split('T')[0]
        const parts = datePart.split('-').map(Number)

        if (parts.length !== 3)
        {
            return null
        }

        const [gy, gm, gd] = parts

        const parsed = dayjs.tz(
            `${gy}-${String(gm).padStart(2, '0')}-${String(gd).padStart(2, '0')}`,
            'YYYY-MM-DD',
            APP_TIMEZONE
        )

        if (!parsed.isValid())
        {
            return null
        }

        const {jy, jm, jd} = toJalaali(gy, gm, gd)

        return {
            jy,
            jm,
            jd
        }
    }

    // Jalali
    const datePart = raw.split(' ')[0]
    const parts = datePart.split(/[-/]/).map(Number)

    if (parts.length !== 3)
    {
        return null
    }

    const [jy, jm, jd] = parts

    if (!isValidJalaaliDate(jy, jm, jd))
    {
        return null
    }

    return {
        jy,
        jm,
        jd
    }
}


/*
|--------------------------------------------------------------------------
| Min Date
|--------------------------------------------------------------------------
*/

function getMinDate()
{
    let minDate =
        parseLimitDate(
            props.minDate
        )


    /*
    |--------------------------------------------------------------------------
    | disablePast
    |--------------------------------------------------------------------------
    */

    if (props.disablePast)
    {

        const currentToday = {
            jy: today.jy,
            jm: today.jm,
            jd: today.jd
        }


        if (
            !minDate ||
            compareJalaliDate(
                currentToday,
                minDate
            ) > 0
        )
        {

            minDate =
                currentToday

        }

    }


    return minDate
}


/*
|--------------------------------------------------------------------------
| Max Date
|--------------------------------------------------------------------------
*/

function getMaxDate()
{
    return parseLimitDate(
        props.maxDate
    )
}


/*
|--------------------------------------------------------------------------
| Date Disabled
|--------------------------------------------------------------------------
*/

function isDateDisabled(item)
{
    if (!item)
    {
        return true
    }


    const date = {
        jy: item.year,
        jm: item.month,
        jd: item.day
    }


    const minDate =
        getMinDate()


    const maxDate =
        getMaxDate()


    if (
        minDate &&
        compareJalaliDate(
            date,
            minDate
        ) < 0
    )
    {
        return true
    }


    if (
        maxDate &&
        compareJalaliDate(
            date,
            maxDate
        ) > 0
    )
    {
        return true
    }


    return false
}


/*
|--------------------------------------------------------------------------
| Month Days
|--------------------------------------------------------------------------
*/

function getMonthDays(
    year,
    month
)
{
    if (month <= 6)
    {
        return 31
    }


    if (month <= 11)
    {
        return 30
    }


    return isValidJalaaliDate(
        year,
        12,
        30
    )
        ? 30
        : 29
}


/*
|--------------------------------------------------------------------------
| Month Allowed
|--------------------------------------------------------------------------
*/

function isMonthAllowed(
    year,
    month
)
{
    const firstDay = {
        jy: year,
        jm: month,
        jd: 1
    }


    const lastDay = {
        jy: year,
        jm: month,
        jd: getMonthDays(
            year,
            month
        )
    }


    const minDate =
        getMinDate()


    const maxDate =
        getMaxDate()


    /*
    |--------------------------------------------------------------------------
    | Entire month before minDate
    |--------------------------------------------------------------------------
    */

    if (
        minDate &&
        compareJalaliDate(
            lastDay,
            minDate
        ) < 0
    )
    {
        return false
    }


    /*
    |--------------------------------------------------------------------------
    | Entire month after maxDate
    |--------------------------------------------------------------------------
    */

    if (
        maxDate &&
        compareJalaliDate(
            firstDay,
            maxDate
        ) > 0
    )
    {
        return false
    }


    return true
}


/*
|--------------------------------------------------------------------------
| Month Disabled
|--------------------------------------------------------------------------
*/

function isMonthDisabled(month)
{
    return !isMonthAllowed(
        currentYear.value,
        month
    )
}


/*
|--------------------------------------------------------------------------
| Year Disabled
|--------------------------------------------------------------------------
*/

function isYearDisabled(year)
{
    const minDate =
        getMinDate()


    const maxDate =
        getMaxDate()


    if (
        minDate &&
        year < minDate.jy
    )
    {
        return true
    }


    if (
        maxDate &&
        year > maxDate.jy
    )
    {
        return true
    }


    return false
}


/*
|--------------------------------------------------------------------------
| First Day
|--------------------------------------------------------------------------
*/

function getFirstDayOfMonth(
    year,
    month
)
{
    const {
        gy,
        gm,
        gd
    } =
        toGregorian(
            year,
            month,
            1
        )


    const date =
        new Date(
            gy,
            gm - 1,
            gd
        )


    return (
        date.getDay() + 1
    ) % 7
}


/*
|--------------------------------------------------------------------------
| Calendar Days
|--------------------------------------------------------------------------
*/

const calendarDays = computed(() =>
{

    const firstDay =
        getFirstDayOfMonth(
            currentYear.value,
            currentMonth.value
        )


    const currentMonthDays =
        getMonthDays(
            currentYear.value,
            currentMonth.value
        )


    const previousMonth =
        currentMonth.value === 1
            ? 12
            : currentMonth.value - 1


    const previousYear =
        currentMonth.value === 1
            ? currentYear.value - 1
            : currentYear.value


    const previousMonthDays =
        getMonthDays(
            previousYear,
            previousMonth
        )


    const days = []


    /*
    |--------------------------------------------------------------------------
    | Previous Month
    |--------------------------------------------------------------------------
    */

    for (
        let i = firstDay - 1;
        i >= 0;
        i--
    )
    {

        days.push({
            day: previousMonthDays - i,
            type: 'previous',
            year: previousYear,
            month: previousMonth
        })

    }


    /*
    |--------------------------------------------------------------------------
    | Current Month
    |--------------------------------------------------------------------------
    */

    for (
        let day = 1;
        day <= currentMonthDays;
        day++
    )
    {

        days.push({
            day,
            type: 'current',
            year: currentYear.value,
            month: currentMonth.value
        })

    }


    /*
    |--------------------------------------------------------------------------
    | Next Month
    |--------------------------------------------------------------------------
    */

    const nextMonth =
        currentMonth.value === 12
            ? 1
            : currentMonth.value + 1


    const nextYear =
        currentMonth.value === 12
            ? currentYear.value + 1
            : currentYear.value


    let nextDay = 1


    while (
        days.length < 42
        )
    {

        days.push({
            day: nextDay++,
            type: 'next',
            year: nextYear,
            month: nextMonth
        })

    }


    return days

})


/*
|--------------------------------------------------------------------------
| Open State
|--------------------------------------------------------------------------
*/

const open =
    ref(false)


const monthPickerOpen =
    ref(false)


const datepickerRef =
    ref(null)


/*
|--------------------------------------------------------------------------
| Parse Model
|--------------------------------------------------------------------------
*/

function parseModelValue()
{
    if (!props.modelValue)
    {

        return {
            jy: today.jy,
            jm: today.jm,
            jd: today.jd,
            hour: 0,
            minute: 0,
            second: 0
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Time Only
    |--------------------------------------------------------------------------
    */

    if (
        effectiveMode.value === 'time'
    )
    {

        const parts =
            props.modelValue
                 .split(':')


        return {
            jy: today.jy,
            jm: today.jm,
            jd: today.jd,
            hour: Number(parts[0]) || 0,
            minute: Number(parts[1]) || 0,
            second: Number(parts[2]) || 0
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Gregorian
    |--------------------------------------------------------------------------
    */

    if (
        props.output === 'gregorian'
    )
    {

        let date


        if (
            props.modelValue.includes('T') ||
            props.modelValue.endsWith('Z')
        )
        {

            date =
                dayjs
                .utc(props.modelValue)
                .tz(APP_TIMEZONE)

        }
        else
        {

            date =
                dayjs.tz(
                    props.modelValue,
                    APP_TIMEZONE
                )

        }


        if (!date.isValid())
        {
            return null
        }


        const jalali =
            toJalaali(
                date.year(),
                date.month() + 1,
                date.date()
            )


        return {
            jy: jalali.jy,
            jm: jalali.jm,
            jd: jalali.jd,
            hour: date.hour(),
            minute: date.minute(),
            second: date.second()
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Jalali
    |--------------------------------------------------------------------------
    */

    const [
        datePart,
        timePart = ''
    ] =
        props.modelValue.split(' ')


    const dateParts =
        datePart
        .split(/[-/]/)
        .map(Number)


    const jy =
        dateParts[0]


    const jm =
        dateParts[1]


    const jd =
        dateParts[2]


    if (
        !jy ||
        !jm ||
        !jd ||
        !isValidJalaaliDate(
            jy,
            jm,
            jd
        )
    )
    {

        return null

    }


    const timeParts =
        timePart
        .split(':')
        .map(Number)


    return {
        jy,
        jm,
        jd,
        hour: timeParts[0] || 0,
        minute: timeParts[1] || 0,
        second: timeParts[2] || 0
    }
}


/*
|--------------------------------------------------------------------------
| Initial Date
|--------------------------------------------------------------------------
*/

function getInitialDate()
{
    const parsed =
        parseModelValue()


    if (parsed)
    {
        return parsed
    }


    /*
    |--------------------------------------------------------------------------
    | If today is before minDate
    |--------------------------------------------------------------------------
    */

    const minDate =
        getMinDate()


    const maxDate =
        getMaxDate()


    const currentToday = {
        jy: today.jy,
        jm: today.jm,
        jd: today.jd,
        hour: 0,
        minute: 0,
        second: 0
    }


    if (
        minDate &&
        compareJalaliDate(
            currentToday,
            minDate
        ) < 0
    )
    {

        return {
            ...minDate,
            hour: 0,
            minute: 0,
            second: 0
        }

    }


    if (
        maxDate &&
        compareJalaliDate(
            currentToday,
            maxDate
        ) > 0
    )
    {

        return {
            ...maxDate,
            hour: 0,
            minute: 0,
            second: 0
        }

    }


    return currentToday
}


const initialDate =
    getInitialDate()


/*
|--------------------------------------------------------------------------
| Calendar State
|--------------------------------------------------------------------------
*/

const currentYear =
    ref(initialDate.jy)


const currentMonth =
    ref(initialDate.jm)


const selectedDay =
    ref(initialDate.jd)


/*
|--------------------------------------------------------------------------
| Time State
|--------------------------------------------------------------------------
*/

const selectedHour =
    ref(initialDate.hour)


const selectedMinute =
    ref(initialDate.minute)


const selectedSecond =
    ref(initialDate.second)


/*
|--------------------------------------------------------------------------
| Years
|--------------------------------------------------------------------------
*/

const years = computed(() =>
{

    const result = []


    let start =
        today.jy - 100


    let end =
        today.jy + 20


    const minDate =
        getMinDate()


    const maxDate =
        getMaxDate()


    if (minDate)
    {

        start =
            Math.min(
                start,
                minDate.jy
            )

    }


    if (maxDate)
    {

        end =
            Math.max(
                end,
                maxDate.jy
            )

    }


    for (
        let year = start;
        year <= end;
        year++
    )
    {

        result.push(year)

    }


    return result

})


/*
|--------------------------------------------------------------------------
| Display Value
|--------------------------------------------------------------------------
*/

const displayValue = computed(() =>
{

    if (!props.modelValue)
    {
        return ''
    }


    /*
    |--------------------------------------------------------------------------
    | Time
    |--------------------------------------------------------------------------
    */

    if (
        effectiveMode.value === 'time'
    )
    {

        const parsed =
            parseModelValue()


        if (!parsed)
        {
            return ''
        }


        let value =
            `${String(parsed.hour).padStart(2, '0')}:${String(parsed.minute).padStart(2, '0')}`


        if (props.enableSeconds)
        {

            value +=
                `:${String(parsed.second).padStart(2, '0')}`

        }


        return toPersianNumber(
            value
        )

    }


    /*
    |--------------------------------------------------------------------------
    | Date / Datetime
    |--------------------------------------------------------------------------
    */

    const parsed =
        parseModelValue()


    if (!parsed)
    {
        return ''
    }


    let value =
        `${parsed.jy}-${String(parsed.jm).padStart(2, '0')}-${String(parsed.jd).padStart(2, '0')}`


    if (
        effectiveMode.value === 'datetime'
    )
    {

        value +=
            ` ${String(parsed.hour).padStart(2, '0')}:${String(parsed.minute).padStart(2, '0')}`


        if (props.enableSeconds)
        {

            value +=
                `:${String(parsed.second).padStart(2, '0')}`

        }

    }


    return toPersianNumber(
        value
    )

})


/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

const canGoPrevious = computed(() =>
{

    let year =
        currentYear.value


    let month =
        currentMonth.value - 1


    if (month === 0)
    {

        month = 12
        year--

    }


    return isMonthAllowed(
        year,
        month
    )

})


const canGoNext = computed(() =>
{

    let year =
        currentYear.value


    let month =
        currentMonth.value + 1


    if (month === 13)
    {

        month = 1
        year++

    }


    return isMonthAllowed(
        year,
        month
    )

})


function previousMonth()
{
    if (!canGoPrevious.value)
    {
        return
    }


    if (
        currentMonth.value === 1
    )
    {

        currentMonth.value = 12
        currentYear.value--

    }
    else
    {

        currentMonth.value--

    }

}


function nextMonth()
{
    if (!canGoNext.value)
    {
        return
    }


    if (
        currentMonth.value === 12
    )
    {

        currentMonth.value = 1
        currentYear.value++

    }
    else
    {

        currentMonth.value++

    }

}


/*
|--------------------------------------------------------------------------
| Month Picker
|--------------------------------------------------------------------------
*/

function toggleMonthPicker()
{
    if (!open.value)
    {
        return
    }


    monthPickerOpen.value =
        !monthPickerOpen.value
}


function selectMonth(month)
{
    if (
        isMonthDisabled(month)
    )
    {
        return
    }


    currentMonth.value =
        month


    /*
    |--------------------------------------------------------------------------
    | Ensure selected day exists
    |--------------------------------------------------------------------------
    */

    const maxDay =
        getMonthDays(
            currentYear.value,
            currentMonth.value
        )


    if (
        selectedDay.value > maxDay
    )
    {

        selectedDay.value =
            maxDay

    }


    monthPickerOpen.value =
        false
}


/*
|--------------------------------------------------------------------------
| Build Time
|--------------------------------------------------------------------------
*/

function buildTimeValue()
{
    const hour =
        String(selectedHour.value)
        .padStart(2, '0')


    const minute =
        String(selectedMinute.value)
        .padStart(2, '0')


    const second =
        String(selectedSecond.value)
        .padStart(2, '0')


    if (props.enableSeconds)
    {

        return `${hour}:${minute}:${second}`

    }


    return `${hour}:${minute}`
}


/*
|--------------------------------------------------------------------------
| Build Date Value
|--------------------------------------------------------------------------
*/

function buildDateValue(
    year,
    month,
    day
)
{

    const {
        gy,
        gm,
        gd
    } =
        toGregorian(
            year,
            month,
            day
        )


    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */

    if (
        effectiveMode.value === 'date'
    )
    {

        if (
            props.output === 'jalali'
        )
        {

            return [
                year,
                String(month).padStart(2, '0'),
                String(day).padStart(2, '0')
            ].join('-')

        }


        return [
            gy,
            String(gm).padStart(2, '0'),
            String(gd).padStart(2, '0')
        ].join('-')

    }


    /*
    |--------------------------------------------------------------------------
    | Datetime
    |--------------------------------------------------------------------------
    */

    const localDateTime =
        dayjs.tz(
            `${gy}-${String(gm).padStart(2, '0')}-${String(gd).padStart(2, '0')} ${String(selectedHour.value).padStart(2, '0')}:${String(selectedMinute.value).padStart(2, '0')}:${String(selectedSecond.value).padStart(2, '0')}`,
            APP_TIMEZONE
        )


    /*
    |--------------------------------------------------------------------------
    | Jalali output
    |--------------------------------------------------------------------------
    */

    if (
        props.output === 'jalali'
    )
    {

        let value =
            `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')} ${String(selectedHour.value).padStart(2, '0')}:${String(selectedMinute.value).padStart(2, '0')}`


        if (props.enableSeconds)
        {

            value +=
                `:${String(selectedSecond.value).padStart(2, '0')}`

        }


        return value

    }


    /*
    |--------------------------------------------------------------------------
    | Gregorian output
    |--------------------------------------------------------------------------
    */

    return localDateTime
    .utc()
    .format(
        'YYYY-MM-DDTHH:mm:ss.SSS[Z]'
    )
}


/*
|--------------------------------------------------------------------------
| Emit
|--------------------------------------------------------------------------
*/

function emitValue(value)
{
    emit(
        'update:modelValue',
        value
    )


    emit(
        'change',
        value
    )
}


/*
|--------------------------------------------------------------------------
| Select Date
|--------------------------------------------------------------------------
*/

function selectDate(item)
{
    if (!item)
    {
        return
    }


    if (
        isDateDisabled(item)
    )
    {
        return
    }


    currentYear.value =
        item.year


    currentMonth.value =
        item.month


    selectedDay.value =
        item.day


    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */

    if (
        effectiveMode.value === 'date'
    )
    {

        const value =
            buildDateValue(
                item.year,
                item.month,
                item.day
            )


        emitValue(value)


        open.value = false

    }
}


/*
|--------------------------------------------------------------------------
| Today
|--------------------------------------------------------------------------
*/

function selectToday()
{
    if (
        isDateDisabled(
            todayItem.value
        )
    )
    {
        return
    }


    currentYear.value =
        today.jy


    currentMonth.value =
        today.jm


    selectedDay.value =
        today.jd


    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */

    if (
        effectiveMode.value === 'date'
    )
    {

        const value =
            buildDateValue(
                today.jy,
                today.jm,
                today.jd
            )


        emitValue(value)


        open.value = false

    }

}


/*
|--------------------------------------------------------------------------
| Confirm Time
|--------------------------------------------------------------------------
*/

function confirmTime()
{
    if (
        effectiveMode.value !== 'time'
    )
    {
        return
    }


    emitValue(
        buildTimeValue()
    )


    open.value = false
}


/*
|--------------------------------------------------------------------------
| Confirm Datetime
|--------------------------------------------------------------------------
*/

function confirmDateTime()
{
    if (
        effectiveMode.value !== 'datetime'
    )
    {
        return
    }


    const item = {
        year: currentYear.value,
        month: currentMonth.value,
        day: selectedDay.value
    }


    if (
        isDateDisabled(item)
    )
    {
        return
    }


    const value =
        buildDateValue(
            currentYear.value,
            currentMonth.value,
            selectedDay.value
        )


    emitValue(value)


    open.value = false
}


/*
|--------------------------------------------------------------------------
| Selected
|--------------------------------------------------------------------------
*/

function isSelected(item)
{

    /*
    |--------------------------------------------------------------------------
    | Datetime
    |--------------------------------------------------------------------------
    */

    if (
        effectiveMode.value === 'datetime' &&
        open.value
    )
    {

        return (
            item.year === currentYear.value &&
            item.month === currentMonth.value &&
            item.day === selectedDay.value
        )

    }


    /*
    |--------------------------------------------------------------------------
    | Time
    |--------------------------------------------------------------------------
    */

    if (
        effectiveMode.value === 'time'
    )
    {
        return false
    }


    /*
    |--------------------------------------------------------------------------
    | No Value
    |--------------------------------------------------------------------------
    */

    if (!props.modelValue)
    {
        return false
    }


    const parsed =
        parseModelValue()


    if (!parsed)
    {
        return false
    }


    return (
        parsed.jy === item.year &&
        parsed.jm === item.month &&
        parsed.jd === item.day
    )
}


/*
|--------------------------------------------------------------------------
| Today
|--------------------------------------------------------------------------
*/

function isToday(item)
{
    return (
        item.year === today.jy &&
        item.month === today.jm &&
        item.day === today.jd
    )
}


/*
|--------------------------------------------------------------------------
| Clear
|--------------------------------------------------------------------------
*/

function clear()
{
    emitValue('')

    open.value = false

    monthPickerOpen.value = false
}


/*
|--------------------------------------------------------------------------
| Discount Highlight
|--------------------------------------------------------------------------
*/

const highlight = ref(null)   // { ranges: [{ start, end, percent }] }


// هم میلادی (2026-11-20)، هم جلالی (1405-08-29)، هم timestamp با Z را می‌فهمد
function parseAnyDate(value)
{
    if (!value)
    {
        return null
    }

    const raw = String(value).trim()

    // timestamp با Z یا offset → به وقت تهران تبدیل شود (جلوگیری از یک روز جابه‌جایی)
    if (/T.*(Z|[+-]\d{2}:?\d{2})$/i.test(raw))
    {
        const d = dayjs.utc(raw).tz(APP_TIMEZONE)

        if (!d.isValid())
        {
            return null
        }

        const {jy, jm, jd} = toJalaali(d.year(), d.month() + 1, d.date())
        return {jy, jm, jd}
    }

    const datePart = raw.split(/[T ]/)[0]
    const [y, m, d] = datePart.split(/[-/]/).map(Number)

    if (!y || !m || !d)
    {
        return null
    }

    if (y >= 1700)
    {
        const {jy, jm, jd} = toJalaali(y, m, d)
        return {jy, jm, jd}
    }

    return isValidJalaaliDate(y, m, d)
        ? {jy: y, jm: m, jd: d}
        : null
}


// بازه‌ای که این روز داخلش است (اگر چند بازه هم‌پوشان بود، بیشترین درصد)
function findDiscountRange(item)
{
    const ranges = highlight.value?.ranges

    if (!ranges?.length)
    {
        return null
    }

    const date = {jy: item.year, jm: item.month, jd: item.day}

    let found = null

    for (const r of ranges)
    {
        if (r.start && compareJalaliDate(date, r.start) < 0)
        {
            continue
        }
        if (r.end && compareJalaliDate(date, r.end) > 0)
        {
            continue
        }

        if (!found || Number(r.percent || 0) > Number(found.percent || 0))
        {
            found = r
        }
    }

    return found
}


function isDiscountDay(item)
{
    return !!findDiscountRange(item)
}


function isDiscountEdge(item, edge)
{
    const ranges = highlight.value?.ranges

    if (!ranges?.length)
    {
        return false
    }

    return ranges.some(r =>
    {
        const point = r[edge]

        return !!point
            && point.jy === item.year
            && point.jm === item.month
            && point.jd === item.day
    })
}


function dayPercent(item)
{
    const percent = findDiscountRange(item)?.percent

    return percent
        ? toPersianNumber(Number(percent)) + '٪'
        : ''
}


// درصد فقط روی روزهای تخفیف‌دارِ ماه جاری که قابل انتخاب هستند نشان داده می‌شود
function showDayPercent(item)
{
    return item.type === 'current'
        && !isDateDisabled(item)
        && !!dayPercent(item)
}


/*
 * ورودی:
 *   { ranges: [{ starts_at, ends_at, percent }, ...] }
 *   یا (سازگاری قبلی) { startsAt, endsAt, percent }
 */
function openWithHighlight({ranges = null, startsAt = null, endsAt = null, percent = null} = {})
{
    const source = ranges?.length
        ? ranges
        : ((startsAt || endsAt) ? [{starts_at: startsAt, ends_at: endsAt, percent}] : [])

    const parsed = source
    .map(r => ({
        start: parseAnyDate(r.starts_at ?? r.startsAt),
        end: parseAnyDate(r.ends_at ?? r.endsAt),
        percent: r.percent ?? null,
    }))
    .filter(r => r.start || r.end)

    highlight.value = parsed.length ? {ranges: parsed} : null

    // اولین بازه‌ی جاری یا آینده؛ اگر شروعش در آینده است به ماه شروعش برو
    const upcoming = [...parsed]
    .sort((a, b) => compareJalaliDate(a.start || a.end, b.start || b.end))
    .find(r => !r.end || compareJalaliDate(r.end, today) >= 0)

    let target = (upcoming?.start && compareJalaliDate(upcoming.start, today) > 0)
        ? upcoming.start
        : {jy: today.jy, jm: today.jm, jd: today.jd}

    if (!isMonthAllowed(target.jy, target.jm))
    {
        const min = getMinDate()
        if (min)
        {
            target = min
        }
    }

    currentYear.value = target.jy
    currentMonth.value = target.jm
    monthPickerOpen.value = false
    open.value = true
}


function closePicker()
{
    open.value = false
    monthPickerOpen.value = false
}


// وقتی بسته شد، هایلایت پاک شود تا دفعه‌ی بعد که معمولی باز می‌کنند نماند
watch(open, value =>
{
    if (!value)
    {
        highlight.value = null
    }
})


defineExpose({
    openWithHighlight,
    close: closePicker,
})


/*
|--------------------------------------------------------------------------
| Toggle
|--------------------------------------------------------------------------
*/

function toggle()
{
    open.value =
        !open.value


    if (open.value)
    {

        syncFromModel()

    }
}


/*
|--------------------------------------------------------------------------
| Sync Model
|--------------------------------------------------------------------------
*/

function syncFromModel()
{
    const date =
        getInitialDate()


    currentYear.value =
        date.jy


    currentMonth.value =
        date.jm


    selectedDay.value =
        date.jd


    selectedHour.value =
        date.hour


    selectedMinute.value =
        date.minute


    selectedSecond.value =
        date.second


    /*
    |--------------------------------------------------------------------------
    | Make sure selected month is allowed
    |--------------------------------------------------------------------------
    */

    if (
        !isMonthAllowed(
            currentYear.value,
            currentMonth.value
        )
    )
    {

        const minDate =
            getMinDate()


        const maxDate =
            getMaxDate()


        if (minDate)
        {

            currentYear.value =
                minDate.jy


            currentMonth.value =
                minDate.jm


            selectedDay.value =
                minDate.jd

        }
        else if (maxDate)
        {

            currentYear.value =
                maxDate.jy


            currentMonth.value =
                maxDate.jm


            selectedDay.value =
                maxDate.jd

        }

    }
}


/*
|--------------------------------------------------------------------------
| Watch Model
|--------------------------------------------------------------------------
*/

watch(
    () => props.modelValue,
    () =>
    {

        if (!open.value)
        {
            syncFromModel()
        }

    }
)


/*
|--------------------------------------------------------------------------
| Watch Limits
|--------------------------------------------------------------------------
*/

watch(
    [
        () => props.minDate,
        () => props.maxDate,
        () => props.disablePast,
        () => props.output
    ],
    () =>
    {

        if (!open.value)
        {
            syncFromModel()
        }

    }
)


/*
|--------------------------------------------------------------------------
| Click Outside
|--------------------------------------------------------------------------
*/

function handleClickOutside(event)
{
    if (
        datepickerRef.value &&
        !datepickerRef.value.contains(
            event.target
        )
    )
    {

        open.value = false

        monthPickerOpen.value = false

    }
}


/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() =>
{

    document.addEventListener(
        'mousedown',
        handleClickOutside
    )

})


/*
|--------------------------------------------------------------------------
| Before Unmount
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() =>
{

    document.removeEventListener(
        'mousedown',
        handleClickOutside
    )

})

</script>

<style scoped>

/* ==============================
   Discount Days
============================== */

.days button.discount:not(.selected):not(.muted) {
    background: #FFFFFF;
    color: #1F2937;
    border: 1.5px solid #E11D48;
    font-weight: 800;
}

/* ابتدا و انتهای بازه تخفیف */
.days button.discount-start:not(.selected),
.days button.discount-end:not(.selected) {
    background: #FFFFFF !important;
    color: #1F2937;
    border: 2px solid #BE123C;
}

/* روزهای تخفیف خارج از ماه جاری */
.days button.discount.muted {
    background: #FFFFFF;
    color: #9CA3AF;
    border: 1px solid #FDA4AF;
}

/* ساختار روز تخفیف */
.days button.discount {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1px;
    line-height: 1.15;
}

/* عدد روز */
.days button.discount .day-number {
    color: #1F2937;
    font-weight: 800;
}

/* ==============================
   Discount Percentage
============================== */

.days button.discount .day-discount {
    display: block;
    font-weight: 900;
    line-height: 1.2;
    transform-origin: center;
    animation: discountPulse 1.5s ease-in-out infinite;
}

/* کمتر از 15 درصد */
.day-discount.discount-sm {
    font-size: 9px;
    color: #BE123C;
    animation-duration: 2.5s;
}

/* 15 تا 29 درصد */
.day-discount.discount-md {
    font-size: 11px;
    color: #9F1239;
    animation-duration: 2s;
}

/* 30 تا 49 درصد */
.day-discount.discount-lg {
    font-size: 13px;
    color: #881337;
    animation-duration: 5s;
    text-shadow: 0 0 3px #FB7185;
}

/* 50 درصد به بالا */
.day-discount.discount-xl {
    font-size: 15px;
    color: #4C0519;
    animation-duration: 5s;
    text-shadow:
        0 0 3px #FB7185,
        0 0 7px #E11D48;
}

/* ==============================
   Animation
============================== */

@keyframes discountPulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
        filter: brightness(1);
    }

    50% {
        opacity: .75;
        transform: scale(1.15);
        filter: brightness(1.2);
    }
}

/* ==============================
   Discount Legend
============================== */

.discount-legend {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 6px 0 4px;
    color: #BE123C;
    font-size: 12px;
    font-weight: 700;
}

.discount-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 3px;
    background: #FFFFFF;
    border: 1.5px solid #E11D48;
}

/* ==============================
   Selected Day
============================== */

.days button.discount.selected {
    border: 2px solid #E11D48;
}

/* ==============================
   Accessibility
============================== */

@media (prefers-reduced-motion: reduce) {
    .days button.discount .day-discount {
        animation: none !important;
    }
}

</style>
