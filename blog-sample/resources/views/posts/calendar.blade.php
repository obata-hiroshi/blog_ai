<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Calendar - {{ $monthNameYear }}</title>
    <style>
        body { font-family: sans-serif; }
        .calendar-container { width: 90%; margin: 20px auto; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-header h1 { font-size: 1.8em; }
        .calendar-nav a { text-decoration: none; padding: 8px 15px; background-color: #007bff; color: white; border-radius: 4px; }
        .calendar-nav a:hover { background-color: #0056b3; }
        table.calendar { border-collapse: collapse; width: 100%; }
        table.calendar th, table.calendar td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
            height: 120px; /* Increased height for better content display */
            width: calc(100% / 7);
        }
        table.calendar th { background-color: #f8f9fa; text-align: center; font-weight: bold; }
        .day-number { font-weight: bold; font-size: 1.1em; margin-bottom: 5px; }
        .post-item { margin-bottom: 5px; }
        .post-title { display: block; font-size: 0.9em; color: #007bff; text-decoration: none; }
        .post-title:hover { text-decoration: underline; }
        .other-month { background-color: #fdfdfd; } /* For empty cells, if needed */
        .today { background-color: #ffc; } /* Optional: for highlighting today */
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="calendar-header">
            <a href="{{ route('posts.calendar', ['year' => $prevMonthLinkData['year'], 'month' => $prevMonthLinkData['month']]) }}" class="calendar-nav">&laquo; Previous Month</a>
            <h1>{{ $monthNameYear }}</h1>
            <a href="{{ route('posts.calendar', ['year' => $nextMonthLinkData['year'], 'month' => $nextMonthLinkData['month']]) }}" class="calendar-nav">Next Month &raquo;</a>
        </div>

        <table class="calendar">
            <thead>
                <tr>
                    <th>日 (Sun)</th>
                    <th>月 (Mon)</th>
                    <th>火 (Tue)</th>
                    <th>水 (Wed)</th>
                    <th>木 (Thu)</th>
                    <th>金 (Fri)</th>
                    <th>土 (Sat)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $dayCount = 1;
                    $currentDay = \Carbon\Carbon::createFromDate($date->year, $date->month, 1); // To check for today
                @endphp
                <tr>
                    @for ($i = 0; $i < $startOfWeek; $i++)
                        <td class="other-month"></td> {{-- Empty cells before the first day --}}
                    @endfor

                    @while ($dayCount <= $daysInMonth)
                        @if (($startOfWeek + $dayCount - 1) % 7 == 0 && $dayCount > 1)
                            </tr><tr> {{-- New row when week ends --}}
                        @endif

                        <td class="{{ $currentDay->day($dayCount)->isToday() ? 'today' : '' }}">
                            <div class="day-number">{{ $dayCount }}</div>
                            @if (isset($postsByDay[$dayCount]))
                                @foreach ($postsByDay[$dayCount] as $post)
                                    <div class="post-item">
                                        <a href="{{ route('posts.show', $post) }}" class="post-title">{{ $post->title }}</a>
                                    </div>
                                @endforeach
                            @endif
                        </td>

                        @php $dayCount++; @endphp
                    @endwhile

                    @while (($startOfWeek + $dayCount -1) % 7 != 0)
                        <td class="other-month"></td> {{-- Empty cells after the last day --}}
                        @php $dayCount++; @endphp
                    @endwhile
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
