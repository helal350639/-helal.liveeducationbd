<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>{{ $title }} | Live Education BD</title>

    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #edf1f7;
            color: #172033;
            font-family: Arial, sans-serif;
        }

        .select-page {
            max-width: 600px;
            min-height: 100vh;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            background: #edf1f7;
        }

        /* HEADER */

        .select-header {
            min-height: 65px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            gap: 12px;
        }

        .select-back {
            color: #10274d;
            text-decoration: none;
            font-size: 20px;
        }

        .select-logo {
            font-size: 15px;
            font-weight: 700;
            color: #103c92;
            text-align: center;
        }

        .select-header-icons {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #152a4e;
            font-size: 17px;
        }

        /* CONTENT */

        .select-content {
            flex: 1;
            padding: 24px 14px;
        }

        .select-title {
            margin: 0;
            text-align: center;
            font-size: 21px;
            font-weight: 700;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .select-subtitle {
            margin: 8px 0 25px;
            text-align: center;
            color: #718096;
            font-size: 13px;
        }

        /* SELECTION BUTTONS */

        .select-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .select-card {
            min-width: 0;
            min-height: 120px;
            padding: 15px 14px;
            background: #ffffff;
            border: 1px solid #e5ebf4;
            border-radius: 17px;
            box-shadow: 0 3px 9px rgba(20,40,70,.04);
            text-decoration: none;
            color: #172033;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
            transition: all .2s ease;
        }

        .select-card:hover {
            border-color: #a8c7f5;
            box-shadow: 0 6px 16px rgba(8,117,225,.10);
            transform: translateY(-2px);
        }

        .select-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .select-icon-box {
            width: 40px;
            height: 40px;
            background: #eaf3ff;
            color: #0875e1;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 19px;
        }

        .select-arrow {
            color: #8fa4c3;
            font-size: 13px;
        }

        .select-card-name {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .select-empty {
            grid-column: 1 / -1;
            text-align: center;
            background: #ffffff;
            border-radius: 14px;
            padding: 25px 12px;
            color: #718096;
            font-size: 14px;
        }

        /* RUNNING COURSES BUTTON */

        .running-section {
            padding: 10px 14px 25px;
        }

        .running-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 11px;
            width: 100%;
            padding: 17px 14px;
            background: #e2efff;
            border: 1px solid #c4ddff;
            border-radius: 15px;
            color: #0752a2;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
            text-align: center;
            text-decoration: none;
        }

        .running-btn:hover {
            background: #d4e7ff;
        }

        @media (max-width: 380px) {
            .select-grid {
                gap: 9px;
            }

            .select-card {
                padding: 12px 10px;
                min-height: 112px;
            }

            .select-card-name {
                font-size: 12px;
            }

            .select-title {
                font-size: 19px;
            }

            .running-btn {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

@php
    $isBatch = $mode === 'batch';

    $backUrl = $isBatch
        ? route('students.dashboard')
        : route('students.exam.course', [
            'courseId' => $course->id
        ]);
@endphp

<div class="select-page">

    <!-- HEADER -->

    <header class="select-header">

        <a href="{{ $backUrl }}"
           class="select-back"
           aria-label="Go Back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="select-logo">
            Live Education BD
        </div>

        <div class="select-header-icons">
            <i class="fa-solid fa-moon"></i>
            <i class="fa-regular fa-bell"></i>
        </div>

    </header>

    <!-- CONTENT -->

    <main class="select-content">

        <h1 class="select-title">
            {{ $title }}
        </h1>

        <div class="select-subtitle">
            {{ $isBatch
                ? 'আপনার ব্যাচ নির্বাচন করুন'
                : 'আপনার ডিপার্টমেন্ট নির্বাচন করুন' }}
        </div>

        <div class="select-grid">

            @forelse($items as $item)

                @if($isBatch)

                    <a class="select-card"
                       href="{{ route('students.exam.batch', [
                           'courseId' => $course->id,
                           'batchId' => $item->id
                       ]) }}">

                        <div class="select-card-top">
                            <div class="select-icon-box">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>

                            <i class="fa-solid fa-arrow-up-right-from-square select-arrow"></i>
                        </div>

                        <span class="select-card-name">
                            {{ $item->name }}
                        </span>

                    </a>

                @else

                    <a class="select-card"
                       href="{{ route('students.exam.department', [
                           'courseId' => $course->id,
                           'batchId' => $batch->id,
                           'departmentId' => $item->id
                       ]) }}">

                        <div class="select-card-top">
                            <div class="select-icon-box">
                                <i class="fa-solid fa-book-open"></i>
                            </div>

                            <i class="fa-solid fa-arrow-up-right-from-square select-arrow"></i>
                        </div>

                        <span class="select-card-name">
                            {{ $item->name }}
                        </span>

                    </a>

                @endif

            @empty

                <div class="select-empty">
                    বর্তমানে কোনো তথ্য পাওয়া যায়নি।
                </div>

            @endforelse

        </div>

    </main>

    <!-- RUNNING COURSES & BATCHES -->

<footer class="running-section">

    <!-- Button 1: Running Batches -->
    <a href="{{ route('homepage.running.batches') }}"
       class="running-btn">

        <i class="fa-solid fa-layer-group"></i>

        <span>
            All premium Batch 
        </span>

        <i class="fa-solid fa-arrow-right"></i>

    </a>

    <!-- Button 2: All Courses -->
    <a href="{{ route('homepage.courses') }}"
       class="running-btn all-courses-btn">

        <i class="fa-solid fa-graduation-cap"></i>

        <span>
            All premium Courses
        </span>

        <i class="fa-solid fa-arrow-right"></i>

    </a>

</footer>

</div>

</body>
</html>